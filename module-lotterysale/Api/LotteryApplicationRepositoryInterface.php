<?php
declare(strict_types=1);

namespace Casio\LotterySale\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LotteryApplicationRepositoryInterface
{
    /**
     * Save LotteryApplication
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
    );

    /**
     * @param array $lotteryApplicationIds
     * @return mixed
     */
    public function updateOrderedValue(array $lotteryApplicationIds);
    /**
     * Retrieve LotteryApplication
     * @param string $lotteryApplicationId
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($lotteryApplicationId);

    /**
     * @param $userId
     * @param $productId
     * @return mixed
     */
    public function getByUserIdAndProductId($userId, $productId);

    /**
     * Retrieve LotteryApplication matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete LotteryApplication
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
    );

    /**
     * Delete LotteryApplication by ID
     * @param string $lotteryApplicationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($lotteryApplicationId);
}
