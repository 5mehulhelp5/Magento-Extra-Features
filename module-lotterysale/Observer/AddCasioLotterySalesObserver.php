<?php

namespace Casio\LotterySale\Observer;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Api\LotterySalesRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add Casio lottery sales to product collection.
 */
class AddCasioLotterySalesObserver implements ObserverInterface
{
    /**
     * @var LotterySalesRepositoryInterface
     */
    private LotterySalesRepositoryInterface $lotterySalesRepository;
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * AddStockItemsObserver constructor.
     *
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param StoreManagerInterface $storeManager
     * @param LotterySalesRepositoryInterface $lotterySalesRepository
     */
    public function __construct(
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        StoreManagerInterface $storeManager,
        LotterySalesRepositoryInterface $lotterySalesRepository
    ) {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->storeManager = $storeManager;
        $this->lotterySalesRepository = $lotterySalesRepository;
    }

    /**
     * Add stock items to products in collection.
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Collection $productCollection */
        $productCollection = $observer->getData('collection');
        $productIds = array_keys($productCollection->getItems());
        $websiteId = $this->storeManager->getWebsite()->getId();
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(LotterySalesInterface::PRODUCT_ID, $productIds, 'in')
            ->addFilter(LotterySalesInterface::WEBSITE_ID, $websiteId)
            ->create();
        $lotterySalesCollection = $this->lotterySalesRepository->getList($searchCriteria);
        foreach ($lotterySalesCollection->getItems() as $item) {
            /** @var Product $product */
            $product = $productCollection->getItemById($item->getProductId());
            $productExtension = $product->getExtensionAttributes();
            $productExtension->setCasioLotterySales($item);
            $product->setExtensionAttributes($productExtension);
        }
    }
}
