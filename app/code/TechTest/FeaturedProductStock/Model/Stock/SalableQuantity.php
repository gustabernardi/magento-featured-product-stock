<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Model\Stock;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolves the current MSI salable quantity for a product SKU.
 */
class SalableQuantity
{
    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly StockResolverInterface $stockResolver,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Returns the salable quantity for the current website stock.
     *
     * @param string $sku
     * @return float
     * @throws LocalizedException
     */
    public function getBySku(string $sku): float
    {
        $websiteCode = (string) $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(
            SalesChannelInterface::TYPE_WEBSITE,
            $websiteCode
        );

        $qty = (float) $this->getProductSalableQty->execute($sku, (int) $stock->getStockId());

        return max(0.0, $qty);
    }
}
