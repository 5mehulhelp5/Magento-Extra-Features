<?php

namespace Casio\LotterySale\Model\SalesType\Validator;

use Casio\LotterySale\Api\Data\LotteryApplicationInterface;
use Casio\LotterySale\Api\LotteryApplicationRepositoryInterface;
use Casio\LotterySale\Model\LotteryApplication;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Casio\LotterySale\Model\SalesType\Lottery as SalesTypeLottery;
use Magento\Framework\Exception\NoSuchEntityException;

class Lottery
{
    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var LotteryApplicationRepositoryInterface
     */
    private LotteryApplicationRepositoryInterface $lotteryApplicationRepository;

    /**
     * @var SalesTypeLottery
     */
    private SalesTypeLottery $salesTypeLottery;

    /**
     * Lottery constructor.
     * @param Session $customerSession
     * @param LotteryApplicationRepositoryInterface $lotteryApplicationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SalesTypeLottery $salesTypeLottery
     */
    public function __construct(
        Session $customerSession,
        LotteryApplicationRepositoryInterface $lotteryApplicationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SalesTypeLottery $salesTypeLottery
    ) {
        $this->customerSession = $customerSession;
        $this->lotteryApplicationRepository = $lotteryApplicationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->salesTypeLottery = $salesTypeLottery;
    }

    /**
     * Validation is lottery sales product
     */
    public function validate($product): int
    {
        try {
            if (!$product) {
                throw new NoSuchEntityException(__('Product is not loaded'));
            }

            $lotteryPeriod = $this->salesTypeLottery->match($product);
            if ($lotteryPeriod == SalesTypeLottery::AFTER) {
                return LotteryApplication::STATUS_CLOSE;
            } elseif ($lotteryPeriod != SalesTypeLottery::REGULAR) {
                return LotteryApplication::STATUS_NOT_LOTTERY;
            }

            if (!$this->validateApply($product)) {
                return LotteryApplication::STATUS_APPLIED;
            }
        } catch (\Exception $e) {
            return LotteryApplication::STATUS_NOT_LOTTERY;
        }

        return LotteryApplication::STATUS_SATISFY;
    }

    /**
     * Determine if the user can purchase the item
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateApply(Product $product): bool
    {
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }

        $customerId = $this->customerSession->getCustomerId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(LotteryApplicationInterface::USER_ID, $customerId)
            ->addFilter(LotteryApplicationInterface::LOTTERY_SALES_ID, $product->getExtensionAttributes()->getCasioLotterySales()->getId())
            ->create();

        $lotterySales = $this->lotteryApplicationRepository->getList($searchCriteria);

        if ($lotterySales->getItems()) {
            return false;
        }

        return true;
    }
}
