<?php
declare(strict_types=1);

namespace Casio\LotterySale\Api\Data;

interface LotterySalesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get LotterySales list.
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Casio\LotterySale\Api\Data\LotterySalesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
