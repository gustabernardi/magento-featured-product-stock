# TechTest_FeaturedProductStock

Magento 2 module that adds a full-width featured product box as the first element of the default homepage content area. The box displays the configured product name, final price, base image and salable quantity. Only the stock quantity is refreshed periodically through AJAX, without reloading the page.

## Compatibility

- Magento Open Source 2.4.6+.
- Developed against Magento Open Source 2.4.7-p10.
- Designed for the Luma/Blank frontend inheritance model.
- Uses MSI `Magento_InventorySalesApi` to read salable quantity.

## Installation

Copy the module to:

```text
app/code/TechTest/FeaturedProductStock
```

Then run:

```bash
bin/magento module:enable TechTest_FeaturedProductStock
bin/magento setup:upgrade
bin/magento cache:flush
```

In production mode, also run the normal static content deployment flow for the store locale.

## Configuration

Go to:

```text
Stores > Configuration > Catalog > Featured Product Stock
```

Available settings:

- `Enabled`: shows or hides the homepage box.
- `Featured Product SKU`: product SKU displayed on the homepage.
- `Stock Refresh Interval (ms)`: polling interval used by the Knockout component. Values lower than `1000` are normalized to `1000`.

All settings support default, website and store view scopes. This allows each store view to enable the feature, select a product and define a polling interval independently.

The default SKU is `24-MB01`, which exists in Magento sample data. If the product is disabled or not visible, the block renders nothing.

The admin fields are validated before saving:

- `Featured Product SKU` is required and must exist in the catalog.
- `Stock Refresh Interval (ms)` is required, must be numeric and must be greater than or equal to `1000`.

## Implementation Notes

- `view/frontend/layout/cms_index_index.xml` injects the block into the homepage through `referenceContainer name="content"` with `before="-"`.
- Block arguments are used for the ViewModel and the `jsLayout`.
- `TechTest\FeaturedProductStock\ViewModel\FeaturedProduct` prepares storefront product data.
- `TechTest\FeaturedProductStock\Model\Stock\SalableQuantity` reads the current website MSI stock through `GetProductSalableQtyInterface`.
- `featuredstock/stock/index` returns a small JSON response with the current salable quantity.
- `view/frontend/web/js/view/stock.js` is a Knockout UI component that polls the JSON endpoint and updates only the stock area.
- `view/frontend/web/css/source/_module.less` contains the module styles using LESS variables and selector nesting.
- All frontend layout, template, JavaScript, translation and LESS files live inside the module. No theme files are edited.

## Product Selection Strategy

The module uses an admin-configured SKU instead of a catalog product attribute. This keeps the feature explicit for a single homepage product, supports store view specific configuration, and avoids ambiguity when more than one product could be marked as featured.

A product attribute would also be valid for a broader merchandising workflow, but it would require additional rules for priority, store scope and conflict resolution when multiple products are selected.

## Frontend Behavior

The homepage HTML contains the static product data and the initial salable quantity. The Knockout component receives the runtime values through `jsLayout`, including the stock endpoint URL, configured SKU, polling interval and minimum interval.

Only the stock quantity area is refreshed after page load. The component avoids overlapping AJAX requests, pauses polling while the browser tab is hidden and refreshes once the tab becomes visible again.

When the salable quantity reaches zero, the stock badge switches to an out-of-stock state and displays `Out of stock`. If the AJAX endpoint cannot be reached, the component keeps the card visible and shows a temporary stock unavailable message.

## Endpoint Response

```json
{
  "success": true,
  "qty": 12,
  "formattedQty": "12",
  "isAvailable": true
}
```

## Cache Behavior

The homepage block can remain cacheable because the volatile value is refreshed by the frontend component after page load and at the configured interval. Product data changes should follow Magento's normal cache invalidation/flush process.

The stock endpoint sends no-cache headers to avoid reusing stale AJAX responses.
