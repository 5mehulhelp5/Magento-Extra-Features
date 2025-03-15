<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication\Filter;

use Casio\LotterySale\Model\Export\LotteryApplication\FilterProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class DateTimeFilter
 * Casio\LotterySale\Model\Export\LotteryApplication\Filter
 */
class DateTimeFilter extends AbstractFilter implements FilterProcessorInterface
{
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * DateTimeFilter constructor.
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezone
    ) {
        parent::__construct($resourceConnection);
        $this->timezone = $timezone;
    }

    /**
     * @param string $type
     * @param string $whereCondition
     * @param string $prefixTbl
     * @param string $columnName
     * @param mixed $value
     * @param boolean $useTimezone
     * @return string
     * @throws \Exception
     */
    public function process($type, $whereCondition, $prefixTbl, $columnName, $value, $useTimezone)
    {
        $condition = '';
        $field = $prefixTbl.".".$columnName;
        $connection = $this->getConnection();
        $dateTimeValueFrom = reset($value);
        $dateTimeValueTo = end($value);
        $timezone = 'UTC';
        if ($useTimezone !== false) {
            $timezone = $this->timezone->getConfigTimezone('website', $useTimezone);
        }
        if ($dateTimeValueFrom != "") {
            if ($whereCondition != '') {
                $condition .= " AND ";
            }
            $dateTimeValueFrom = $this->getFormatDate($dateTimeValueFrom);
            $dateTimeValueFrom = new \DateTime($dateTimeValueFrom, new \DateTimeZone($timezone));
            if ($timezone != 'UTC') {
                $dateTimeValueFrom->setTimezone(new \DateTimeZone('UTC'));
            }
            $condition .= $this->_getConditionSql(
                $connection,
                $connection->quoteIdentifier($field),
                ['gteq' => $dateTimeValueFrom->format('Y-m-d H:i:s')]
            );
        }
        if ($dateTimeValueTo != "") {
            $dateTimeValueTo = $this->getFormatDate($dateTimeValueTo);
            $dateTimeValueTo = new \DateTime($dateTimeValueTo, new \DateTimeZone($timezone));
            $dateTimeValueTo->setTime(23, 59, 59);
            if ($timezone != 'UTC') {
                $dateTimeValueTo->setTimezone(new \DateTimeZone('UTC'));
            }
            if ($condition != "" || $whereCondition != '') {
                $condition .= " AND ";
            }
            $condition .= $this->_getConditionSql(
                $connection,
                $connection->quoteIdentifier($field),
                ['lteq' => $dateTimeValueTo->format('Y-m-d H:i:s')]
            );
        }
        return $condition;
    }
}
