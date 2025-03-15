<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\Export\LotteryApplication;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class FilterProcessorAggregator
 * Casio\LotterySale\Model\Export\LotteryApplication
 */
class FilterProcessorAggregator
{
    /**
     * @var FilterProcessorInterface[]
     */
    private $handler;

    /**
     * @param FilterProcessorInterface[] $handler
     * @throws LocalizedException
     */
    public function __construct(array $handler = [])
    {
        foreach ($handler as $filterProcessor) {
            if (!($filterProcessor instanceof FilterProcessorInterface)) {
                throw new LocalizedException(__(
                    'Filter handler must be instance of "%interface"',
                    ['interface' => FilterProcessorInterface::class]
                ));
            }
        }

        $this->handler = $handler;
    }

    /**
     * @param $type
     * @param $condition
     * @param $prefixTbl
     * @param $columnName
     * @param $value
     * @param $useTimezone
     * @return mixed
     * @throws LocalizedException
     */
    public function process($type, $condition, $prefixTbl, $columnName, $value, $useTimezone)
    {
        if (!isset($this->handler[$type])) {
            throw new LocalizedException(__(
                'No filter processor for "%type" given.',
                ['type' => $type]
            ));
        }
        return $this->handler[$type]->process($type, $condition, $prefixTbl, $columnName, $value, $useTimezone);
    }
}
