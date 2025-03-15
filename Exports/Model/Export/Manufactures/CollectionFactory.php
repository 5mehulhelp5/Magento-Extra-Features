<?php

namespace Codilar\Exports\Model\Export\Manufactures;

use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;

class CollectionFactory
{
    const MANUFACTURER_ATTRIBUTE_CODE = 'brand_name';
    const EAV_TABLE = 'eav_attribute';

    const EAV_ATTRIbUTE_OPTION_TABLE = 'eav_attribute_option';

    const EAV_ATTRIbUTE_OPTION_VALUE_TABLE = 'eav_attribute_option_value';

    const ATTRIBUTE_ENTITY_TYPE_TABLE = 'catalog_product_entity_varchar';

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $_resourceConnection;

    /**
     * @var EavConfig
     */
    protected EavConfig $_eavConfig;

    /**
     * CollectionFactory constructor.
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * @return array|null
     */
    public function create()
    {
        try {
            $connection = $this->_resourceConnection->getConnection();
            $select = $connection->select()
                ->from(
                    ['ea' => $connection->getTableName(self::EAV_TABLE)],
                    []
                )
                ->join(
                    ['eao' => $connection->getTableName(self::EAV_ATTRIbUTE_OPTION_TABLE)],
                    'ea.attribute_id = eao.attribute_id',
                    ['option_id']
                )
                ->join(
                    ['eaov' => $connection->getTableName(self::EAV_ATTRIbUTE_OPTION_VALUE_TABLE)],
                    'eao.option_id = eaov.option_id',
                    ['brand_name' => 'value']
                )
                ->joinLeft(
                    ['cpei' => $connection->getTableName(self::ATTRIBUTE_ENTITY_TYPE_TABLE)],
                    'eao.option_id = cpei.value AND cpei.attribute_id = ea.attribute_id',
                    ['product_count' => 'COUNT(cpei.value_id)']
                )
                ->where('ea.attribute_code = ?', self::MANUFACTURER_ATTRIBUTE_CODE)
                ->where('eaov.store_id = ?', 0)
                ->group(['option_id', self::MANUFACTURER_ATTRIBUTE_CODE]);

            $collection = $connection->fetchAll($select);
            return $collection;
        } catch (\Exception $exception) {
            return null;
        }
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
}
