<?php

namespace Casio\LotterySale\Observer;

use Casio\LotterySale\Api\LotteryApplicationRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class OrderSavedAfterObserver implements ObserverInterface
{
    /**
     * @var LotteryApplicationRepositoryInterface
     */
    private LotteryApplicationRepositoryInterface $lotteryApplicationRepository;
    private LoggerInterface $logger;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * OrderSavedAfterObserver constructor.
     * @param LotteryApplicationRepositoryInterface $lotteryApplicationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        LotteryApplicationRepositoryInterface $lotteryApplicationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->lotteryApplicationRepository = $lotteryApplicationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $productIds = [];
            foreach ($order->getItems() as $item) {
                $productIds[] = $item->getProductId();
            }
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('product_id', $productIds, 'in')
                ->addFilter('user_id', $customerId)
                ->addFilter('purchase_deadline', $now->format('Y-m-d H:i:s'), 'gteq')
                ->create();
            $lotteryApplication = $this->lotteryApplicationRepository->getList($searchCriteria);
            $lotteryApplicationIds = [];
            foreach ($lotteryApplication->getItems() as $item) {
                $lotteryApplicationIds[] = $item->getId();
            }
            $this->lotteryApplicationRepository->updateOrderedValue($lotteryApplicationIds);
        }
    }
}
