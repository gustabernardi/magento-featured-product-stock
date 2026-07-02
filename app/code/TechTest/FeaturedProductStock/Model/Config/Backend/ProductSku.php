<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Model\Config\Backend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Validates the configured featured product SKU before saving system config.
 */
class ProductSku extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly ProductRepositoryInterface $productRepository,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Ensures that the configured SKU exists in the catalog.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave(): self
    {
        $sku = trim((string) $this->getValue());

        if ($sku === '') {
            throw new LocalizedException(__('Please enter a featured product SKU.'));
        }

        try {
            $this->productRepository->get($sku);
        } catch (NoSuchEntityException) {
            throw new LocalizedException(
                __('The featured product SKU "%1" does not exist.', $sku)
            );
        }

        $this->setValue($sku);

        return parent::beforeSave();
    }
}
