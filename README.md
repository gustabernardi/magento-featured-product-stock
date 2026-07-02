# Magento Featured Product Stock

Technical assessment module for Magento Open Source. It adds a full-width featured product box as the first element of the Luma homepage content area and refreshes only the salable quantity through AJAX.

## Module

The module is available at:

```text
app/code/TechTest/FeaturedProductStock
```

## Features

- Configurable featured product by SKU.
- Live salable stock refresh without full page reload.
- Admin configuration with default, website and store view scopes.
- Admin validation for SKU and polling interval.
- Homepage insertion through layout XML using `referenceContainer`.
- Block arguments and `jsLayout` usage.
- Knockout UI component for the stock area.
- LESS styles contained inside the module.
- No direct theme edits.

## Installation

```bash
bin/magento module:enable TechTest_FeaturedProductStock
bin/magento setup:upgrade
bin/magento cache:flush
```

In production mode, run the normal static content deployment flow for the store locale.

## Configuration

Go to:

```text
Stores > Configuration > Catalog > Featured Product Stock
```

Available settings:

- `Enabled`
- `Featured Product SKU`
- `Stock Refresh Interval (ms)`

The default SKU is `24-MB01`, which exists in Magento sample data.

## Documentation

See the full module documentation:

```text
app/code/TechTest/FeaturedProductStock/README.md
```
