<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication;

interface FilterProcessorInterface
{
    /**
     * @param string $type
     * @param string $condition
     * @param string $prefixTbl
     * @param string $columnName
     * @param mixed $value
     * @param boolean $useTimezone
     * @return mixed
     */
    public function process($type, $condition, $prefixTbl, $columnName, $value, $useTimezone);
}
