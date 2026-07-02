define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/translate'
], function (Component, ko, $, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'TechTest_FeaturedProductStock/stock',
            stockUrl: '',
            sku: '',
            interval: 5000,
            defaultInterval: 5000,
            minInterval: 1000,
            initialQty: null,
            initialFormattedQty: '',
            outOfStockLabel: $t('Out of stock'),
            unavailableLabel: $t('Unavailable'),
            qtyLabel: $t('Available quantity')
        },

        /**
         * Initializes observable state and starts polling stock updates.
         *
         * @return {Object}
         */
        initialize: function () {
            this._super();

            this.qty = ko.observable(this.initialQty);
            this.formattedQty = ko.observable(this.initialFormattedQty || this.unavailableLabel);
            this.isAvailable = ko.observable(Number(this.initialQty) > 0);
            this.isLoading = ko.observable(false);
            this.hasError = ko.observable(false);
            this.stockLabel = ko.pureComputed(function () {
                if (this.hasError() || this.qty() === null || typeof this.qty() === 'undefined') {
                    return this.unavailableLabel;
                }

                return this.isAvailable() ? this.qtyLabel : this.outOfStockLabel;
            }, this);
            this.shouldShowQty = ko.pureComputed(function () {
                return this.isAvailable() && !this.hasError();
            }, this);
            this.pollingTimer = null;
            this.currentRequest = null;
            this.visibilityHandler = this.refreshWhenVisible.bind(this);

            this.startPolling();

            return this;
        },

        /**
         * Starts periodic stock refreshes.
         *
         * @return {void}
         */
        startPolling: function () {
            var interval = Math.max(
                Number(this.interval) || Number(this.defaultInterval) || 5000,
                Number(this.minInterval) || 1000
            );

            if (!this.stockUrl) {
                return;
            }

            if (document.addEventListener) {
                document.addEventListener('visibilitychange', this.visibilityHandler);
            }

            this.refreshStock();
            this.pollingTimer = window.setInterval(this.refreshStock.bind(this), interval);
        },

        /**
         * Calls the stock JSON endpoint and updates the visible quantity.
         *
         * @return {void}
         */
        refreshStock: function () {
            var component = this;

            if (document.hidden || component.currentRequest) {
                return;
            }

            component.isLoading(true);

            component.currentRequest = $.ajax({
                url: component.stockUrl,
                type: 'GET',
                dataType: 'json',
                cache: false,
                data: {
                    sku: component.sku
                }
            }).done(function (response) {
                if (!response || response.success !== true) {
                    component.hasError(true);
                    return;
                }

                component.qty(response.qty);
                component.formattedQty(response.formattedQty || component.unavailableLabel);
                component.isAvailable(response.isAvailable === true);
                component.hasError(false);
            }).fail(function () {
                component.hasError(true);
            }).always(function () {
                component.isLoading(false);
                component.currentRequest = null;
            });
        },

        /**
         * Refreshes immediately when the tab becomes visible again.
         *
         * @return {void}
         */
        refreshWhenVisible: function () {
            if (!document.hidden) {
                this.refreshStock();
            }
        },

        /**
         * Stops the polling timer when the component is disposed.
         *
         * @return {void}
         */
        destroy: function () {
            if (this.pollingTimer) {
                window.clearInterval(this.pollingTimer);
            }

            if (document.removeEventListener && this.visibilityHandler) {
                document.removeEventListener('visibilitychange', this.visibilityHandler);
            }

            if (this.currentRequest) {
                this.currentRequest.abort();
            }

            this._super();
        }
    });
});
