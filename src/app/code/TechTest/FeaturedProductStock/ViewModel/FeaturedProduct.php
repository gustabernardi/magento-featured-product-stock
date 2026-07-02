<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\ViewModel;

use TechTest\FeaturedProductStock\Model\Config;
use TechTest\FeaturedProductStock\Model\Stock\QuantityFormatter;
use TechTest\FeaturedProductStock\Model\Stock\SalableQuantity;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides product and stock data to the homepage featured product block.
 */
class FeaturedProduct implements ArgumentInterface
{
    /**
     * @var bool
     */
    private bool $productLoaded = false;

    /**
     * @var Product|null
     */
    private ?Product $product = null;

    /**
     * @var float|null
     */
    private ?float $salableQty = null;

    /**
     * @param Config $config
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ImageHelper $imageHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param SalableQuantity $salableQuantity
     * @param QuantityFormatter $quantityFormatter
     */
    public function __construct(
        private readonly Config $config,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly ImageHelper $imageHelper,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly SalableQuantity $salableQuantity,
        private readonly QuantityFormatter $quantityFormatter
    ) {
    }

    /**
     * Checks whether the feature is enabled for the current store.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled($this->getStoreId());
    }

    /**
     * Returns the configured product when it is visible on the storefront.
     *
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        if ($this->productLoaded) {
            return $this->product;
        }

        $this->productLoaded = true;
        $sku = $this->config->getProductSku($this->getStoreId());

        if ($sku === '') {
            return null;
        }

        try {
            /** @var Product $product */
            $product = $this->productRepository->get($sku, false, $this->getStoreId());
        } catch (NoSuchEntityException) {
            return null;
        }

        if ((int) $product->getStatus() !== Status::STATUS_ENABLED || !$product->isVisibleInSiteVisibility()) {
            return null;
        }

        $this->product = $product;

        return $this->product;
    }

    /**
     * Returns the product name.
     *
     * @return string
     */
    public function getProductName(): string
    {
        return (string) ($this->getProduct()?->getName() ?? '');
    }

    /**
     * Returns the product URL.
     *
     * @return string
     */
    public function getProductUrl(): string
    {
        return (string) ($this->getProduct()?->getProductUrl() ?? '#');
    }

    /**
     * Returns the base image URL for the configured product.
     *
     * @return string
     */
    public function getImageUrl(): string
    {
        $product = $this->getProduct();

        if (!$product) {
            return '';
        }

        return (string) $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }

    /**
     * Returns the product final price formatted for the current store.
     *
     * @return string
     */
    public function getFormattedPrice(): string
    {
        $product = $this->getProduct();

        if (!$product) {
            return '';
        }

        return $this->priceCurrency->format(
            (float) $product->getFinalPrice(),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }

    /**
     * Returns the current salable quantity.
     *
     * @return float|null
     */
    public function getSalableQty(): ?float
    {
        if ($this->salableQty !== null) {
            return $this->salableQty;
        }

        $product = $this->getProduct();

        if (!$product) {
            return null;
        }

        try {
            $this->salableQty = $this->salableQuantity->getBySku((string) $product->getSku());
        } catch (LocalizedException) {
            return null;
        }

        return $this->salableQty;
    }

    /**
     * Returns the formatted salable quantity.
     *
     * @return string
     */
    public function getFormattedSalableQty(): string
    {
        $qty = $this->getSalableQty();

        if ($qty === null) {
            return (string) __('Unavailable');
        }

        return $this->quantityFormatter->format($qty);
    }

    /**
     * Returns the configured polling interval in milliseconds.
     *
     * @return int
     */
    public function getPollInterval(): int
    {
        return $this->config->getPollInterval($this->getStoreId());
    }

    /**
     * Returns the current store ID.
     *
     * @return int
     */
    private function getStoreId(): int
    {
        return (int) $this->getStore()->getId();
    }

    /**
     * Returns the current store.
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    private function getStore(): \Magento\Store\Api\Data\StoreInterface
    {
        return $this->storeManager->getStore();
    }
}
