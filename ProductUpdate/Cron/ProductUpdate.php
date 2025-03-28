<?php

namespace KalyanUs\ProductUpdate\Cron;

use KalyanUs\ProductUpdate\Logger\Logger;
use KalyanUs\ProductUpdate\Model\ProductUpdateApi;
class ProductUpdate
{
    /**
     * @var ProductUpdateApi
     */
    private ProductUpdateApi $productUpdateApi;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param ProductUpdateApi $productUpdateApi
     * @param Logger $logger
     */
    public function __construct(
        ProductUpdateApi $productUpdateApi,
        Logger $logger
    ) {
        $this->productUpdateApi = $productUpdateApi;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $this->productUpdateApi->updateProducts();
        } catch (\Exception $e) {
            $this->logger->error('Unexpected Error in Product Update Cron: ' . $e->getMessage());
        }
    }
}
