<?php

namespace KalyanUs\GoldValueUpdate\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use KalyanUs\GoldValueUpdate\Logger\Logger;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class UpdateGoldValue extends Command
{
    const STORE_CODE = 'kalyan_us_store';
    const OPTION_SKUS = 'skus';
    const METAL_VALUE_CODE = 'metal_value';
    const PURITY_CODE = 'purity';
    const GOLD_IN_GRAMS = 'gold_in_grams';
    const GOLD_RATE_CONFIG_PATH = 'jewellery/gold_config/gold_purities';

    /**
     * @var State
     */
    private State $state;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     */
    public function __construct(
        State $state,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ) {
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('kalyanus:goldvalueupdate:update-products-for-store')
            ->setDescription('Update Products Gold Value for a specific store based on config values.')
            ->addOption(self::OPTION_SKUS, null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of SKUs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            $this->logger->error("Unable to set the Area Code!", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $output->writeln("<error>Unable to set the Area Code!: {$e->getMessage()} </error>");
            return Cli::RETURN_FAILURE;
        }


        $storeCode = self::STORE_CODE;
        $storeId = $this->getStoreIdByCode($storeCode);
        if (!$storeId) {
            $output->writeln("<error>Store not found for code: $storeCode</error>");
            return Cli::RETURN_FAILURE;
        }

        $goldRateData = $this->getGoldRate($storeId);
        if (!count($goldRateData)) {
            $output->writeln("<error>Invalid Gold Rate: $storeCode</error>");
            return Cli::RETURN_FAILURE;
        }

        $skus = $input->getOption(self::OPTION_SKUS);
        $output->writeln("<info>Processing store ID: $storeId</info>");

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($storeId);

        if ($skus) {
            $skuArray = explode(',', $skus);
            $productCollection->addFieldToFilter('sku', ['in' => $skuArray]);
        }
        $productCollection->addAttributeToSelect(['id','sku',self::PURITY_CODE,self::GOLD_IN_GRAMS]);

        foreach ($productCollection as $product) {
            try {
                $metalValue = $this->calculateMetalValue($product, $goldRateData);
                if($metalValue) {
                    $product->setStoreId($storeId);
                    $product->setData(self::METAL_VALUE_CODE, $metalValue);
                    $product->getResource()->saveAttribute($product, self::METAL_VALUE_CODE);
                    $output->writeln("<info>Updated product SKU: {$product->getSku()}</info>");
                } else {
                    $this->logger->error('Skipped product SKu: '. $product->getSku(). ' Because invalid Metal value');
                    $output->writeln("<error>Skipped product SKU: {$product->getSku()} Because invalid Metal value</error>");
                }
            } catch (\Exception $e) {
                $this->logger->error('Error updating product SKU: '.$product->getSku() . 'Error: ' . $e->getMessage());
                $output->writeln("<error>Error updating product SKU: {$product->getSku()}. Error: {$e->getMessage()}</error>");
                continue;
            }
        }

        $output->writeln('<info>All products have been processed successfully.</info>');
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param $product
     * @param $goldRateData
     * @return float|int|null
     */
    private function calculateMetalValue($product, $goldRateData)
    {
        $purityValue = $product->getData('purity') ? strtolower($product->getAttributeText('purity')) : null;
        $goldInGram = $product->getGoldInGrams();
        if (!$purityValue || !$goldInGram || !isset($goldRateData[$purityValue])) {
            return null;
        }
        return $goldRateData[$purityValue] * $goldInGram;
    }

    /**
     * @param $storeCode
     * @return int|null
     */
    private function getStoreIdByCode($storeCode): ?int
    {
        try {
            $store = $this->storeManager->getStore($storeCode);
            return $store->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }


    /**
     * @param $storeId
     * @return array
     */
    private function getGoldRate($storeId)
    {
        $rateData = [];
        try {
            $tableRates = $this->scopeConfig->getValue(self::GOLD_RATE_CONFIG_PATH, ScopeInterface::SCOPE_STORE, $storeId);
            $parsedRates = json_decode($tableRates, true);
            if (!is_array($parsedRates)) {
                throw new \InvalidArgumentException("Invalid gold rate configuration.");
            }
            foreach ($parsedRates as $tableRate) {
                $rateData[$tableRate['code']] = $tableRate['rate'];
            }
            return $rateData;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }
}
