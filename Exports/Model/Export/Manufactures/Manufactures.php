<?php

namespace Codilar\Exports\Model\Export\Manufactures;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Codilar\Exports\Model\Export\Manufactures\AttributeCollectionProvider;
use Codilar\Exports\Model\Export\Manufactures\CollectionFactory as ManufacturersCollectionFactory;

class Manufactures extends AbstractEntity
{
    const ENTITY_TYPE_CODE = 'master_manufactures';
    const COL_BRAND_NAME = 'brand_name';
    const COL_OPTION_ID = 'option_id';
    const COL_PRODUCT_COUNT = 'product_count';

    /**
     * @var AttributeCollectionProvider
     */
    private AttributeCollectionProvider $attributeCollectionProvider;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $manufacturersCollectionFactory;

    /**
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionFactory $manufacturersCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     */
    public function __construct(
        AttributeCollectionProvider                     $attributeCollectionProvider,
        ManufacturersCollectionFactory                  $manufacturersCollectionFactory,
        ScopeConfigInterface                            $scopeConfig,
        StoreManagerInterface                           $storeManager,
        Factory                                         $collectionFactory,
        CollectionByPagesIteratorFactory                $resourceColFactory,
        array                                           $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->manufacturersCollectionFactory = $manufacturersCollectionFactory;
    }

    /**
     * @var string[]
     */
    protected array $templateExportData = [
        self::COL_BRAND_NAME,
        self::COL_OPTION_ID,
        self::COL_PRODUCT_COUNT
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
        $collection = $this->manufacturersCollectionFactory->create();
        foreach ($collection as $item) {
            $data[self::COL_BRAND_NAME] = $item[self::COL_BRAND_NAME];
            $data[self::COL_OPTION_ID] = $item[self::COL_OPTION_ID];
            $data[self::COL_PRODUCT_COUNT] = $item[self::COL_PRODUCT_COUNT];
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
}
