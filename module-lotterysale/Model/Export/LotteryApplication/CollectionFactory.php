<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication;

use Casio\LotterySale\Model\ResourceModel\LotteryApplication\Collection as LotteryApplicationCollection;
use Casio\LotterySale\Model\ResourceModel\LotteryApplication\CollectionFactory as LotteryApplicationCollectionFactory;
use Casio\Core\Model\Source\WebsitesPermission;
use Magento\Framework\Data\Collection as AttributeCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;

/**
 * Class CollectionFactory
 * Casio\LotterySale\Model\Export\LotteryApplication
 */
class CollectionFactory
{
    /** @var LotteryApplicationCollectionFactory  */
    protected $collectionFactory;

    /** @var ResourceConnection  */
    protected $_resourceConnection;

    /** @var EavConfig  */
    protected $_eavConfig;

    /** @var WebsitesPermission  */
    protected $websitesPermission;

    /** @var FilterProcessorAggregator  */
    protected $filerProcessor;

    /**
     * CollectionFactory constructor.
     * @param LotteryApplicationCollectionFactory $collectionFactory
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param WebsitesPermission $websitesPermission
     * @param FilterProcessorAggregator $filerProcessor
     */
    public function __construct(
        LotteryApplicationCollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        WebsitesPermission $websitesPermission,
        FilterProcessorAggregator $filerProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_eavConfig = $eavConfig;
        $this->websitesPermission = $websitesPermission;
        $this->filerProcessor = $filerProcessor;
    }

    public function create(AttributeCollection $attributeCollection, array $filters)
    {
        /** @var LotteryApplicationCollection $collection */
        $collection = $this->collectionFactory->create();
        $connection = $this->_resourceConnection->getConnection();
        $lotterySalesTbl = $this->_resourceConnection->getTableName('casio_lottery_sales');
        $prefixTbl = 'cls';
        $select = $collection->getSelect()->joinLeft(
            [$prefixTbl => $lotterySalesTbl],
            'main_table.lottery_sales_id = cls.id',
            [
                'lottery_id' => 'main_table.id',
                'lottery_number' => 'main_table.lottery_sales_code',
                'sku' => 'cls.sku',
                'cls_created_at' => 'cls.created_at',
                'cls_updated_at' => 'cls.updated_at',
                'website_id' => 'cls.website_id',
                'lottery_sales_id' => 'cls.id',
                'lottery_date' => 'cls.lottery_date',
                'application_date_from' => 'cls.application_date_from',
                'application_date_to' => 'cls.application_date_to',
                'purchase_deadline' => 'cls.purchase_deadline',
            ]
        )->group(
            'main_table.id'
        );
        $whereCondition = '';
        $useTimezone = false;
        foreach ($this->retrieveFilterData($filters) as $columnName => $value) {
            if ($columnName == 'website_id' && count($value) == 1) {
                $useTimezone = $value[0];
            }
            $attributeDefinition = $attributeCollection->getItemById($columnName);
            if (!$attributeDefinition) {
                throw new LocalizedException(__(
                    'Given column name "%columnName" is not present in collection.',
                    ['columnName' => $columnName]
                ));
            }

            $type = $attributeDefinition->getData('backend_type');
            if (!$type) {
                throw new LocalizedException(__(
                    'There is no backend type specified for column "%columnName".',
                    ['columnName' => $columnName]
                ));
            }
            if ($columnName == 'created_at') {
                $prefixTbl = 'main_table';
            } else {
                $prefixTbl = 'cls';
            }
            $condition = $this->filerProcessor->process($type, $whereCondition, $prefixTbl, $columnName, $value, $useTimezone);

            $whereCondition .= $condition;
        }
        if ($whereCondition != '') {
            $whereCondition = trim($whereCondition, " AND ");
            $select->where(
                $whereCondition
            );
        }
        return $connection->fetchAll($select);
    }

    /**
     * @param $connection
     * @param $fieldName
     * @param $condition
     * @return mixed
     */
    protected function _getConditionSql($connection, $fieldName, $condition)
    {
        return $connection->prepareSqlCondition($fieldName, $condition);
    }

    /**
     * @param array $filters
     * @return array
     */
    private function retrieveFilterData(array $filters)
    {
        return array_filter(
            $filters[Export::FILTER_ELEMENT_GROUP] ?? [],
            function ($value) {
                return $value !== '';
            }
        );
    }
}
