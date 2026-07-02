<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Block;

use TechTest\FeaturedProductStock\Model\Config;
use TechTest\FeaturedProductStock\ViewModel\FeaturedProduct as FeaturedProductViewModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

/**
 * Homepage block responsible for preparing the Knockout jsLayout payload.
 */
class FeaturedProduct extends Template
{
    /**
     * @param Template\Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private readonly Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the configured ViewModel.
     *
     * @return FeaturedProductViewModel|null
     */
    public function getViewModel(): ?FeaturedProductViewModel
    {
        $viewModel = $this->getData('view_model');

        return $viewModel instanceof FeaturedProductViewModel ? $viewModel : null;
    }

    /**
     * Serializes the jsLayout and injects runtime values required by the component.
     *
     * @return string
     */
    public function getJsLayoutJson(): string
    {
        $jsLayout = $this->getData('jsLayout');
        $jsLayout = is_array($jsLayout) ? $jsLayout : [];
        $viewModel = $this->getViewModel();
        $product = $viewModel?->getProduct();
        $initialQty = $viewModel?->getSalableQty();

        $componentConfig = [
            'stockUrl' => $this->getUrl('featuredstock/stock/index'),
            'sku' => $product ? (string) $product->getSku() : '',
            'interval' => $viewModel?->getPollInterval() ?? Config::DEFAULT_POLL_INTERVAL,
            'defaultInterval' => Config::DEFAULT_POLL_INTERVAL,
            'minInterval' => Config::MIN_POLL_INTERVAL,
            'initialQty' => $initialQty,
            'initialFormattedQty' => $viewModel?->getFormattedSalableQty() ?? '',
        ];

        $existingConfig = $jsLayout['components']['featuredProductStock']['config'] ?? [];
        $jsLayout['components']['featuredProductStock']['config'] = array_replace(
            is_array($existingConfig) ? $existingConfig : [],
            $componentConfig
        );

        return $this->json->serialize($jsLayout);
    }
}
