<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Reads module settings from store configuration.
 */
class Config
{
    public const DEFAULT_POLL_INTERVAL = 5000;
    public const MIN_POLL_INTERVAL = 1000;

    private const XML_PATH_ENABLED = 'featured_product_stock/general/enabled';
    private const XML_PATH_PRODUCT_SKU = 'featured_product_stock/general/product_sku';
    private const XML_PATH_POLL_INTERVAL = 'featured_product_stock/general/poll_interval';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Checks whether the homepage widget is enabled.
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabled(int|string|null $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns the configured product SKU.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getProductSku(int|string|null $storeId = null): string
    {
        $sku = (string) $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_SKU,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return trim($sku);
    }

    /**
     * Returns the stock polling interval in milliseconds.
     *
     * @param int|string|null $storeId
     * @return int
     */
    public function getPollInterval(int|string|null $storeId = null): int
    {
        $interval = (int) $this->scopeConfig->getValue(
            self::XML_PATH_POLL_INTERVAL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return max(self::MIN_POLL_INTERVAL, $interval ?: self::DEFAULT_POLL_INTERVAL);
    }
}
