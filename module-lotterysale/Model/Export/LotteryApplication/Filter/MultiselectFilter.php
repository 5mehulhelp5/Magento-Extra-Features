<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication\Filter;

/**
 * Class MultiselectFilter
 * Casio\LotterySale\Model\Export\LotteryApplication\Filter
 */
class MultiselectFilter extends AbstractFilter implements \Casio\LotterySale\Model\Export\LotteryApplication\FilterProcessorInterface
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
            ['in' => $value]
        );
        return $condition;
    }
}
