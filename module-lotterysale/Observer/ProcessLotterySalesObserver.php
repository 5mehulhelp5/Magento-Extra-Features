<?php

declare(strict_types=1);

namespace Casio\LotterySale\Observer;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Api\LotterySalesRepositoryInterface;
use Casio\LotterySale\Api\Data\LotterySalesInterfaceFactory;
use Casio\LotterySale\Model\LotterySalesRepository;
use Casio\LotterySale\Model\ResourceModel\LotterySales\DeleteMultiple;
use Casio\LotterySale\Model\ResourceModel\LotterySales\SaveMultiple;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Save source product relations during product persistence via controller
 *
 * This needs to be handled in dedicated observer, because there is no pre-defined way of making several API calls for
 * Form submission handling
 */
class ProcessLotterySalesObserver implements ObserverInterface
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
     * @var LotterySalesInterfaceFactory
     */
    private LotterySalesInterfaceFactory $lotterySalesFactory;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var SaveMultiple
     */
    private SaveMultiple $saveMultiple;
    /**
     * @var DeleteMultiple
     */
    private DeleteMultiple $deleteMultiple;
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param LotterySalesRepository $lotterySalesRepository
     * @param SaveMultiple $saveMultiple
     * @param DeleteMultiple $deleteMultiple
     * @param LotterySalesInterfaceFactory $lotterySalesFactory
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param TimezoneInterface $timezone
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        LotterySalesRepository $lotterySalesRepository,
        SaveMultiple $saveMultiple,
        DeleteMultiple $deleteMultiple,
        LotterySalesInterfaceFactory $lotterySalesFactory,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager
    )
    {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->lotterySalesRepository = $lotterySalesRepository;
        $this->saveMultiple = $saveMultiple;
        $this->deleteMultiple = $deleteMultiple;
        $this->lotterySalesFactory = $lotterySalesFactory;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
    }

    /**
     * Process Lottery Sales during product saving via controller.
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        /** @var ProductInterface $product */
        $product = $observer->getEvent()->getProduct();
        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();
        $productData = $controller->getRequest()->getParam('product', []);
        $casioLotterySalesData = $product['casio_lottery_sales'] ?? [];
        $defaultSourceData = false;
        if (!empty($casioLotterySalesData)
        ) {
            $lotteryDateFrom = $casioLotterySalesData[LotterySalesInterface::APPLICATION_DATE_FROM] ?: null;
            $lotteryDateTo = $casioLotterySalesData[LotterySalesInterface::APPLICATION_DATE_TO] ?: null;
            $lotteryDescription = $casioLotterySalesData[LotterySalesInterface::DESCRIPTION] ?: null;
            if (($lotteryDateFrom != null || $lotteryDateTo != null) && ($lotteryDescription == null)) {
                throw new \Magento\Framework\Exception\LocalizedException(__("Description field of Lottery Sales section is required."));
            }
            if ($casioLotterySalesData[LotterySalesInterface::TITLE] ||
                $casioLotterySalesData[LotterySalesInterface::DESCRIPTION] ||
                $casioLotterySalesData[LotterySalesInterface::LOTTERY_DATE] ||
                $casioLotterySalesData[LotterySalesInterface::APPLICATION_DATE_FROM] ||
                $casioLotterySalesData[LotterySalesInterface::APPLICATION_DATE_TO] ||
                $casioLotterySalesData[LotterySalesInterface::PURCHASE_DEADLINE]
            ) {
                $defaultSourceData = [
                    LotterySalesInterface::PRODUCT_ID => $product->getId(),
                    LotterySalesInterface::SKU => $productData['sku'],
                    LotterySalesInterface::TITLE => $casioLotterySalesData[LotterySalesInterface::TITLE],
                    LotterySalesInterface::DESCRIPTION => $casioLotterySalesData[LotterySalesInterface::DESCRIPTION],
                    LotterySalesInterface::LOTTERY_DATE => $casioLotterySalesData[LotterySalesInterface::LOTTERY_DATE],
                    LotterySalesInterface::APPLICATION_DATE_FROM => $this->convertLotteryDateTime(
                        $casioLotterySalesData[LotterySalesInterface::APPLICATION_DATE_FROM] ?: null
                    ),
                    LotterySalesInterface::APPLICATION_DATE_TO => $this->convertLotteryDateTime(
                        $casioLotterySalesData[LotterySalesInterface::APPLICATION_DATE_TO] ?: null
                    ),
                    LotterySalesInterface::PURCHASE_DEADLINE => $this->convertLotteryDateTime(
                        $casioLotterySalesData[LotterySalesInterface::PURCHASE_DEADLINE] ?: null
                    ),
                ];
            }
        }
        $lotterySalesItem = $this->getLotterySalesItems($product->getId(), $defaultSourceData);
        if (isset($lotterySalesItem['update'])) {
            $this->saveMultiple->execute($lotterySalesItem['update']);
        }
        if (isset($lotterySalesItem['delete'])) {
            $this->deleteMultiple->execute($lotterySalesItem['delete']);
        }
    }

    /**
     * @param $value
     * @return string
     * @throws \Exception
     */
    private function convertLotteryDateTime($value)
    {
        if ($value) {
            $dateTime = new \DateTime($value);
            $websiteId = $this->storeManager->getWebsite()->getId();
            $timezone = $this->timezone->getConfigTimezone('website', $websiteId);
            $currentDateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'), new \DateTimeZone($timezone));
            $value = $currentDateTime->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        }
        return $value;
    }

    /**
     * Get Lottery Sales by product id
     *
     * @param $productId
     * @param $defaultSourceData
     * @param $websiteIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getLotterySalesItems($productId, $defaultSourceData): array
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(LotterySalesInterface::PRODUCT_ID, $productId)
            ->create();
        $lotterySalesItems = $this->lotterySalesRepository->getList($searchCriteria)->getItems();

        $lotterySalesItemData = [];

        if ($defaultSourceData) {
            $lotterySalesItemData['update'][] = $this->handleLotterySalesData($defaultSourceData);
        } else {
            $currentWebsiteId = $this->storeManager->getWebsite()->getId();
            foreach ($lotterySalesItems as $lotterySales) {
                if ($currentWebsiteId == $lotterySales->getWebsiteId()) {
                    $lotterySalesItemData['delete'][] = $lotterySales;
                    break;
                }
            }
        }
        return $lotterySalesItemData;
    }

    /**
     * @param $defaultSourceData
     * @param null $websiteId
     * @return LotterySalesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function handleLotterySalesData($defaultSourceData, $websiteId = null)
    {
        $lotterySalesDataObject = $this->lotterySalesFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $lotterySalesDataObject,
            $defaultSourceData,
            LotterySalesInterface::class
        );
        if (!$websiteId) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }
        $lotterySalesDataObject->setWebsiteId($websiteId);

        return $lotterySalesDataObject;
    }
}
