<?php
declare(strict_types=1);

namespace Casio\LotterySale\Api\Data;

interface LotteryApplicationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get LotteryApplication list.
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
