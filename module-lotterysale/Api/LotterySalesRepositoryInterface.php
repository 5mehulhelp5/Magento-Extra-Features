<?php
declare(strict_types=1);

namespace Casio\LotterySale\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LotterySalesRepositoryInterface
{
    /**
     * Save LotterySales
     * @param Data\LotterySalesInterface $lotterySales
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Casio\LotterySale\Api\Data\LotterySalesInterface $lotterySales
    );

    /**
     * Retrieve LotterySales
     * @param $lotterySalesId
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($lotterySalesId);

    /**
     * @param $userId
     * @param $productId
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface|mixed|string|null
     */
    public function getByProductId($productId);

    /**
     * Retrieve LotterySales matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Casio\LotterySale\Api\Data\LotterySalesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete LotterySales
     * @param Data\LotterySalesInterface $lotterySales
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Casio\LotterySale\Api\Data\LotterySalesInterface $lotterySales
    );
}
