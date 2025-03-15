<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication\Filter;

use Casio\LotterySale\Model\Export\LotteryApplication\FilterProcessorInterface;

/**
 * Class VarcharFilter
 * Casio\LotterySale\Model\Export\LotteryApplication\Filter
 */
class VarcharFilter extends AbstractFilter implements FilterProcessorInterface
{

    /**
     * @param string $type
     * @param string $whereCondition
     * @param string $prefixTbl
     * @param string $columnName
     * @param mixed $value
     * @param boolean $useTimezone
     * @return string
     */
    public function process($type, $whereCondition, $prefixTbl, $columnName, $value, $useTimezone)
    {
        $condition = '';
        if ($whereCondition != '') {
            $condition .= " AND ";
        }
        $field = $prefixTbl.".".$columnName;
        $connection = $this->getConnection();
        $condition .= $this->_getConditionSql(
            $connection,
            $connection->quoteIdentifier($field),
            ['like' => "%$value%"]
        );
        return $condition;
    }
}
