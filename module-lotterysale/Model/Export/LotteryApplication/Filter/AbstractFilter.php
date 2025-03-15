<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication\Filter;

use Magento\Framework\App\ResourceConnection;

abstract class AbstractFilter implements \Casio\LotterySale\Model\Export\LotteryApplication\FilterProcessorInterface
{
    /** @var ResourceConnection  */
    protected $resourceConnection;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface  */
    protected $connection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
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
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param $dateTime
     * @return string
     */
    protected function getFormatDate($dateTime): string
    {
        return date('Y-m-d', strtotime($dateTime));
    }
}
