<?php

namespace Codilar\Exports\Model\Export\ContractedProducts;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Codilar\Exports\Model\Export\ContractedProducts\CollectionFactory as ContractedProductsCollectionFactory;
use Magento\Company\Model\CompanyRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Codilar\Exports\Model\Export\ContractedProducts\AttributeCollectionProvider;
use Magento\Store\Model\StoreManagerInterface;

class ContractedProducts extends AbstractEntity
{
    const ENTITY_TYPE_CODE = 'contracted_products';
    const COL_DIVISION_NAME = 'division_name';
    const COL_COMPANY_ID = 'company_id';
    const COL_SKU = 'sku';
    const COL_PRICE = 'price';

    /**
     * @var AttributeCollectionProvider
     */
    private AttributeCollectionProvider $attributeCollectionProvider;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $contractedProductsCollectionFactory;

    /**
     * @var CompanyRepository
     */
    private CompanyRepository $companyRepository;

    /**
     * @param CollectionFactory $contractedProductsCollectionFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CompanyRepository $companyRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     */
    public function __construct(
        ContractedProductsCollectionFactory $contractedProductsCollectionFactory,
        AttributeCollectionProvider         $attributeCollectionProvider,
        CompanyRepository                   $companyRepository,
        ScopeConfigInterface                $scopeConfig,
        StoreManagerInterface               $storeManager,
        Factory                             $collectionFactory,
        CollectionByPagesIteratorFactory    $resourceColFactory,
        array                               $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->contractedProductsCollectionFactory = $contractedProductsCollectionFactory;
        $this->companyRepository = $companyRepository;
    }

    /**
     * @var string[]
     */
    protected array $templateExportData = [
        self::COL_SKU,
        self::COL_COMPANY_ID,
        self::COL_DIVISION_NAME,
        self::COL_PRICE,
        ];


    /**
     * @return Collection
     * @throws Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function exportItem($item)
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $collection = $this->contractedProductsCollectionFactory->create($this->getAttributeCollection(),
            $this->_parameters
        );
        foreach ($collection as $item) {
            $data[self::COL_SKU] = $item[self::COL_SKU];
            $data[self::COL_COMPANY_ID] = $this->getCompanyName($item[self::COL_COMPANY_ID]);
            $data[self::COL_DIVISION_NAME] = $item[self::COL_DIVISION_NAME];
            $data[self::COL_PRICE] = $item[self::COL_PRICE];
            $writer->writeRow($data);
        }
        return $writer->getContents();
    }


    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return self::ENTITY_TYPE_CODE;
    }


    /**
     * @return string[]
     * @throws LocalizedException
     */
    protected function _getHeaderColumns()
    {
        $columns = $this->templateExportData;
        $filters = $this->_parameters;
        if (!isset($filters[Export::FILTER_ELEMENT_SKIP])) {
            return $columns;
        }

        if (count($filters[Export::FILTER_ELEMENT_SKIP]) === count($columns)) {
            throw new LocalizedException(__('There is no data for the export.'));
        }
        // remove the skipped from columns
        $skippedAttributes = array_flip($filters[Export::FILTER_ELEMENT_SKIP]);
        foreach ($columns as $key => $value) {
            if (array_key_exists($value, $skippedAttributes) === true) {
                unset($columns[$key]);
            }
        }
        return $columns;
    }

    /**
     * @param $companyId
     * @return mixed|string|null
     * @throws NoSuchEntityException
     */
    protected function getCompanyName($companyId)
    {
        return $this->companyRepository->get($companyId)->getCompanyName();
    }
}
