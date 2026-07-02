<?php

declare(strict_types=1);

namespace TechTest\FeaturedProductStock\Controller\Stock;

use TechTest\FeaturedProductStock\Model\Config;
use TechTest\FeaturedProductStock\Model\Stock\QuantityFormatter;
use TechTest\FeaturedProductStock\Model\Stock\SalableQuantity;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * JSON endpoint used by the storefront component to refresh product stock.
 */
class Index implements HttpGetActionInterface
{
    /**
     * @param Config $config
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param SalableQuantity $salableQuantity
     * @param QuantityFormatter $quantityFormatter
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly JsonFactory $jsonFactory,
        private readonly RequestInterface $request,
        private readonly SalableQuantity $salableQuantity,
        private readonly QuantityFormatter $quantityFormatter,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Returns the current salable quantity for the configured product.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->jsonFactory->create();
        $this->disableClientCache($result);

        $storeId = (int) $this->storeManager->getStore()->getId();
        $sku = $this->config->getProductSku($storeId);
        $requestedSku = trim((string) $this->request->getParam('sku'));

        if (!$this->config->isEnabled($storeId) || $sku === '') {
            $result->setHttpResponseCode(404);

            return $result->setData([
                'success' => false,
                'message' => __('Featured product stock is not configured.'),
            ]);
        }

        if ($requestedSku !== '' && $requestedSku !== $sku) {
            $result->setHttpResponseCode(409);

            return $result->setData([
                'success' => false,
                'message' => __('Featured product stock configuration changed.'),
            ]);
        }

        try {
            $qty = $this->salableQuantity->getBySku($sku);

            return $result->setData([
                'success' => true,
                'qty' => $qty,
                'formattedQty' => $this->quantityFormatter->format($qty),
                'isAvailable' => $qty > 0,
            ]);
        } catch (LocalizedException $exception) {
            $this->logger->warning($exception->getMessage(), ['exception' => $exception]);
            $result->setHttpResponseCode(404);

            return $result->setData([
                'success' => false,
                'message' => __('Unable to load featured product stock.'),
            ]);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            $result->setHttpResponseCode(500);

            return $result->setData([
                'success' => false,
                'message' => __('Unable to refresh stock right now.'),
            ]);
        }
    }

    /**
     * Prevents browsers and intermediary caches from reusing stock responses.
     *
     * @param Json $result
     * @return void
     */
    private function disableClientCache(Json $result): void
    {
        $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $result->setHeader('Pragma', 'no-cache', true);
        $result->setHeader('Expires', '0', true);
    }
}
