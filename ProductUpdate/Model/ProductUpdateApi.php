<?php

namespace KalyanUs\ProductUpdate\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use KalyanUs\ProductUpdate\Logger\Logger;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;

class ProductUpdateApi
{
    const PRODUCT_FETCH_ENABLED_PATH = 'kalyan_product_update/general/enable';
    const DISABLE_INVENTORY_PATH = 'kalyan_product_update/general/disable_product';

    const API_URL = 'kalyan_product_update/general/api_url';
    const STORE_CODE = 'kalyan_us_store';
    const METAL_VALUE_CODE = 'metal_value';
    const WEIGHT_CODE = 'weight';
    const MAKING_CHARGES_CODE = 'making_charges';
    const BARCODE_ATTRIBUTE = 'kj_parent_barcode';
    const HIERARCHY_NAME_ATTRIBUTE = 'kj_hierarchy_name';
    const GEMSTONE_WEIGHT_ATTRIBUTE = 'kj_gemstone_weight';
    const GOLD_WEIGHT_ATTRIBUTE = 'gold_in_grams';
    const DIAMOND_WEIGHT = 'kj_diamond_weight';
    const COLOR_STONE_WEIGHT = 'kj_color_stone_wt';
    const GEMSTONE_PRICE_ATTRIBUTE = 'kj_gemstone_price';
    const STONE_RATE = 'stone_rate';
    const COLOR_STONE_PRICE = 'kj_color_stone_price';
    const METAL_PURITY = 'kj_gold_purity';
    const METAL_RATE = 'kj_metalrate';
    const MAKING_CHARGE_UNIT = 'kj_mc_unit';
    const MAKING_CHARGE_RATE = 'kj_mc_rate';
    const MAKING_CHARGE_PURITY = 'kj_mccalcpurity';

    const PRODUCT_STATUS = 'status';

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    private AttributeRepositoryInterface $attributeRepository;
    private AttributeOptionManagementInterface $optionManagement;

    private array $purityOptions = [];
    private array $mcUnitOptions = [];

    private array $mcPurityOptions = [];


    /**
     * @param Curl $curl
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionManagementInterface $optionManagement
     */
    public function __construct(
        Curl $curl,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionManagementInterface $optionManagement
    ) {
        $this->curl = $curl;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->attributeRepository = $attributeRepository;
        $this->optionManagement = $optionManagement;
    }

    public function getProductData()
    {
        try {
            $response = [];
            if ($this->isProductUpdateEnabled()) {
                $baseApiUrl = $this->getApiUrl();
                $headers = ['Content-Type' => 'application/json'];
                $this->curl->setHeaders($headers);
                $this->curl->get($baseApiUrl);

                $responseBody = $this->curl->getBody();
                $responseBody = trim($responseBody);
                $responseBody = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $responseBody);
                $response = json_decode($responseBody, true);

                if ($response === null) {
                    throw new \Exception("JSON Decode Error: " . json_last_error_msg());
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
        return $response;
    }


    public function updateProducts(OutputInterface $output = null)
    {
        $storeId = $this->getStoreIdByCode(self::STORE_CODE);
        $this->logger->info('Fetching the data from Api');
        $output?->writeln("<info>Fetching the data from Api</info>");
        $data = $this->getProductData();
        if (empty($data)) {
            $this->logger->info('No data received for product update.');
            $output?->writeln("<error>No data received for product update.</error>");
            return;
        }

        // Extract all barcodes
        $barcodes = array_column($data, 'barcode');
        $productDataByBarcode = array_combine($barcodes, $data);

        // Load products in a single query using collection
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter($storeId)
            ->addAttributeToSelect([
                self::BARCODE_ATTRIBUTE,
                self::METAL_VALUE_CODE,
                self::WEIGHT_CODE,
                self::MAKING_CHARGES_CODE,
                self::HIERARCHY_NAME_ATTRIBUTE,
                self::GEMSTONE_WEIGHT_ATTRIBUTE,
                self::GOLD_WEIGHT_ATTRIBUTE,
                self::DIAMOND_WEIGHT,
                self::COLOR_STONE_WEIGHT,
                self::GEMSTONE_PRICE_ATTRIBUTE,
                self::STONE_RATE,
                self::COLOR_STONE_PRICE,
                self::PRODUCT_STATUS
            ])
            ->addFieldToFilter(self::BARCODE_ATTRIBUTE, ['in' => $barcodes]);
        $response = [];
        $this->logger->info('Update Starts');
        $output?->writeln("<info>Update Starts</info>");
        $productCount = 0;
        $this->purityOptions = $this->getAttributeOptionIds(self::METAL_PURITY);
        $this->mcUnitOptions = $this->getAttributeOptionIds(self::MAKING_CHARGE_UNIT);
        $this->mcPurityOptions = $this->getAttributeOptionIds(self::MAKING_CHARGE_PURITY);
        foreach ($collection as $product) {
            try {
                $barcode = $product->getData(self::BARCODE_ATTRIBUTE) ?? null;
                if (!$barcode || !isset($productDataByBarcode[$barcode])) {
                    $this->logger->info('Product not found for barcode: '. $barcode);
                    $output?->writeln("<error>Product not found for barcode:   {$barcode}</error>");
                    continue;
                }

                $item = $productDataByBarcode[$barcode];
                $product->setStoreId($storeId);

                $product->setStatus(Status::STATUS_ENABLED);
                $product->getResource()->saveAttribute($product, self::PRODUCT_STATUS);

                if (isset($item['metalvalue'])) {
                    $product->setData(self::METAL_VALUE_CODE, $item['metalvalue']);
                    $this->saveAttribute($product, self::METAL_VALUE_CODE);
                }

                if (isset($item['grossweight'])) {
                    $product->setData(self::WEIGHT_CODE, $item['grossweight']);
                    $this->saveAttribute($product, self::WEIGHT_CODE);
                }

                if (isset($item['mcvalue'])) {
                    $product->setData(self::MAKING_CHARGES_CODE, $item['mcvalue']);
                    $this->saveAttribute($product, self::MAKING_CHARGES_CODE);
                }

                if (isset($item['hierarchyname'])) {
                    $product->setData(self::HIERARCHY_NAME_ATTRIBUTE, $item['hierarchyname']);
                    $this->saveAttribute($product, self::HIERARCHY_NAME_ATTRIBUTE);
                }

                if (isset($item['stoneweight'])) {
                    $product->setData(self::GEMSTONE_WEIGHT_ATTRIBUTE, $item['stoneweight']);
                    $this->saveAttribute($product, self::GEMSTONE_WEIGHT_ATTRIBUTE);
                }

                if (isset($item['netweight'])) {
                    $product->setData(self::GOLD_WEIGHT_ATTRIBUTE, $item['netweight']);
                    $this->saveAttribute($product, self::GOLD_WEIGHT_ATTRIBUTE);
                }

                if (isset($item['totalvalue'])) {
                    $product->setPrice($item['totalvalue']);
                    $this->saveAttribute($product, 'price');
                }

                if (isset($item['diamondcarat'])) {
                    $product->setData(self::DIAMOND_WEIGHT, $item['diamondcarat']);
                    $this->saveAttribute($product, self::DIAMOND_WEIGHT);
                }

                if (isset($item['colorstonecarat'])) {
                    $product->setData(self::COLOR_STONE_WEIGHT, $item['colorstonecarat']);
                    $this->saveAttribute($product, self::COLOR_STONE_WEIGHT);
                }

                if (isset($item['stonevalue'])) {
                    $product->setData(self::GEMSTONE_PRICE_ATTRIBUTE, $item['stonevalue']);
                    $this->saveAttribute($product, self::GEMSTONE_PRICE_ATTRIBUTE);
                }

                if (isset($item['diamondvalue'])) {
                    $product->setData(self::STONE_RATE, $item['diamondvalue']);
                    $this->saveAttribute($product, self::STONE_RATE);
                }

                if (isset($item['colorstonevalue'])) {
                    $product->setData(self::COLOR_STONE_PRICE, $item['colorstonevalue']);
                    $this->saveAttribute($product, self::COLOR_STONE_PRICE);
                }

                $purityValue = isset($item['metalcalcpurity']) ? $this->getMetalPurityInCarat($item['metalcalcpurity']) : null;
                if ($purityValue) {
                    $product->setData(self::METAL_PURITY, $purityValue);
                    $this->saveAttribute($product, self::METAL_PURITY);
                }

                if (isset($item['metalrate'])) {
                    $product->setData(self::METAL_RATE, $item['metalrate']);
                    $this->saveAttribute($product, self::METAL_RATE);
                }

                $makingChargeUnit = isset($item['mc_unit']) ? $this->getOptionIdByLabel($this->mcUnitOptions, $item['mc_unit']) : null;
                if ($makingChargeUnit) {
                    $product->setData(self::MAKING_CHARGE_UNIT, $makingChargeUnit);
                    $this->saveAttribute($product, self::MAKING_CHARGE_UNIT);
                }

                if (isset($item['mc_rate'])) {
                    $product->setData(self::MAKING_CHARGE_RATE, $item['mc_rate']);
                    $this->saveAttribute($product, self::MAKING_CHARGE_RATE);
                }

                $mcPurityValue = isset($item['mccalcpurity']) ? $this->getMakingchargesPurityInCarat($item['mccalcpurity']) : null;
                if ($mcPurityValue) {
                    $product->setData(self::MAKING_CHARGE_PURITY, $mcPurityValue);
                    $this->saveAttribute($product, self::MAKING_CHARGE_PURITY);
                }

                $productCount++;
                $this->logger->info('updated product with barcode: '. $barcode . 'Sku: '. $product->getSku());
                $output?->writeln("<info>updated product with barcode:   {$barcode} : Sku : {$product->getSku()}</info>");
                $response[] = $product->getSku();
            } catch (\Exception $e) {
                $output?->writeln("<error>Error updating product with barcode:   {$barcode} : {$e->getMessage()}</error>");
                $this->logger->error("Error updating product with barcode " . $barcode . ": " . $e->getMessage());
                $response[] = $e->getMessage();
            }
        }

        $output?->writeln("<info>--------------------------------------------------</info>");
        $output?->writeln("{$productCount} products have been updated successfully.");
        $this->logger->info($productCount.' products have been updated successfully.');

        // Disabling the rest of products
        if ($this->disableOtherInventory()) {
            $this->disableProducts($storeId, $barcodes,$output);
        }
        return $response;
    }

    /**
     * @param $product
     * @param $attributeCode
     * @return void
     */
    public function saveAttribute($product, $attributeCode)
    {
        $product->getResource()->saveAttribute($product,$attributeCode);
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
     * @param $barcodes
     * @param OutputInterface|null $output
     * @return void
     */
    public function disableProducts($storeId, $barcodes, OutputInterface $output = null)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter($storeId)
            ->addAttributeToSelect(self::PRODUCT_STATUS)
            ->addFieldToFilter(self::BARCODE_ATTRIBUTE, ['nin' => $barcodes]);

        $disabledProductCount = 0;
        foreach ($collection as $product) {
            try {
                $product->setStatus(Status::STATUS_DISABLED);
                $product->getResource()->saveAttribute($product, self::PRODUCT_STATUS);
                $disabledProductCount++;
                $this->logger->info('Disabled product with Sku: '. $product->getSku());
                $output?->writeln("<info>Disabled product with Sku : {$product->getSku()}</info>");
            } catch (\Exception $e) {
                $output?->writeln("<error>Error disabling product SKU: {$product->getSku()} : {$e->getMessage()}</error>");
                $this->logger->error('Error disabling product SKU: ' . $product->getSku() . ' - ' . $e->getMessage());
            }
        }
        $output?->writeln("<info>------------------------------------</info>");
        $output?->writeln("{$disabledProductCount} products have been disabled successfully.");
        $this->logger->info($disabledProductCount.' products have been disabled successfully.');
    }

    /**
     * @return bool
     */
    public function isProductUpdateEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::PRODUCT_FETCH_ENABLED_PATH);
    }

    /**
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->scopeConfig->getValue(self::API_URL);
    }

    /**
     * @return bool
     */
    public function disableOtherInventory()
    {
        return (bool) $this->scopeConfig->getValue(self::DISABLE_INVENTORY_PATH);
    }

    /**
     * @param $value
     * @return mixed|null
     */
    public function getMetalPurityInCarat($value)
    {
        $data = [
            '750'   => $this->getOptionIdByLabel($this->purityOptions,'18K'),
            '920'   => $this->getOptionIdByLabel($this->purityOptions,'22K'),
            '999.9' => $this->getOptionIdByLabel($this->purityOptions,'24K')
        ];

        return $data[$value] ?? null;
    }

    /**
     * @param $attributeCode
     * @return array
     */
    private function getAttributeOptionIds($attributeCode)
    {
        $data = [];
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            $options = $this->optionManagement->getItems('catalog_product', $attribute->getAttributeCode());

            foreach ($options as $option) {
                $data[$option->getLabel()] = (int)$option->getValue();
            }
        } catch (\Exception $e) {
            return [];
        }
        return $data;
    }

    /**
     * @param $array
     * @param $label
     * @return mixed|null
     */
    public function getOptionIdByLabel($array , $label)
    {
        return $array[$label] ?? null;
    }

    /**
     * @param $value
     * @return mixed|null
     */
    public function getMakingchargesPurityInCarat($value)
    {
        $data = [
            '750'   => $this->getOptionIdByLabel($this->mcPurityOptions,'18K'),
            '920'   => $this->getOptionIdByLabel($this->mcPurityOptions,'22K'),
            '999.9' => $this->getOptionIdByLabel($this->mcPurityOptions,'24K')
        ];

        return $data[$value] ?? null;
    }
}
