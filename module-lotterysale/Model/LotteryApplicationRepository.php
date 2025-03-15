<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model;

use Casio\LotterySale\Api\Data\LotteryApplicationInterfaceFactory;
use Casio\LotterySale\Api\Data\LotteryApplicationSearchResultsInterfaceFactory;
use Casio\LotterySale\Api\LotteryApplicationRepositoryInterface;
use Casio\LotterySale\Model\ResourceModel\LotteryApplication as ResourceLotteryApplication;
use Casio\LotterySale\Model\ResourceModel\LotteryApplication\CollectionFactory as LotteryApplicationCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Casio\LotterySale\Model\ResourceModel\LotterySales;

class LotteryApplicationRepository implements LotteryApplicationRepositoryInterface
{
    /**
     * @var ResourceLotteryApplication
     */
    protected $resource;

    /**
     * @var LotteryApplicationFactory
     */
    protected $lotteryApplicationFactory;

    /**
     * @var LotteryApplicationCollectionFactory
     */
    protected $lotteryApplicationCollectionFactory;

    /**
     * @var LotteryApplicationSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var LotteryApplicationInterfaceFactory
     */
    protected $dataLotteryApplicationFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceLotteryApplication $resource
     * @param LotteryApplicationFactory $lotteryApplicationFactory
     * @param LotteryApplicationInterfaceFactory $dataLotteryApplicationFactory
     * @param LotteryApplicationCollectionFactory $lotteryApplicationCollectionFactory
     * @param LotteryApplicationSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceLotteryApplication $resource,
        LotteryApplicationFactory $lotteryApplicationFactory,
        LotteryApplicationInterfaceFactory $dataLotteryApplicationFactory,
        LotteryApplicationCollectionFactory $lotteryApplicationCollectionFactory,
        LotteryApplicationSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->lotteryApplicationFactory = $lotteryApplicationFactory;
        $this->lotteryApplicationCollectionFactory = $lotteryApplicationCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataLotteryApplicationFactory = $dataLotteryApplicationFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface
     * @throws CouldNotSaveException
     */
    public function save(
        \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
    ) {
        /* if (empty($lotteryApplication->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $lotteryApplication->setStoreId($storeId);
        } */

        $lotteryApplicationData = $this->extensibleDataObjectConverter->toNestedArray(
            $lotteryApplication,
            [],
            \Casio\LotterySale\Api\Data\LotteryApplicationInterface::class
        );

        $lotteryApplicationModel = $this->lotteryApplicationFactory->create()->setData($lotteryApplicationData);

        try {
            $this->resource->save($lotteryApplicationModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the lotteryApplication: %1',
                $exception->getMessage()
            ));
        }
        return $lotteryApplicationModel->getDataModel();
    }

    /**
     * @param array $lotteryApplicationIds
     */
    public function updateOrderedValue(array $lotteryApplicationIds)
    {
        $this->resource->updateOrderedValue($lotteryApplicationIds);
    }

    /**
     * @param string $lotteryApplicationId
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface
     * @throws NoSuchEntityException
     */
    public function get($lotteryApplicationId)
    {
        $lotteryApplication = $this->lotteryApplicationFactory->create();
        $this->resource->load($lotteryApplication, $lotteryApplicationId);
        if (!$lotteryApplication->getId()) {
            throw new NoSuchEntityException(__('LotteryApplication with id "%1" does not exist.', $lotteryApplicationId));
        }
        return $lotteryApplication->getDataModel();
    }

    /**
     * @param $userId
     * @param $productId
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface|string|null
     * @throws NoSuchEntityException
     */
    public function getByUserIdAndProductId($userId, $productId)
    {
        $id = $this->resource->getIdByUserIdAndProductId($userId, $productId);
        if ($id) {
            return $this->get($id);
        }
        return $id;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->lotteryApplicationCollectionFactory->create();
        $collection->join(
            ['cls' => $collection->getConnection()->getTableName(LotterySales::MAIN_TABLE)],
            'main_table.lottery_sales_id = cls.id',
            [
                'product_id',
                'sku',
                'lottery_date',
                'application_date_from',
                'application_date_to',
                'purchase_deadline',
                'website_id',
                'created_at',
                'updated_at',
                'title',
                'description'
            ]
        );
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Casio\LotterySale\Api\Data\LotteryApplicationInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(
        \Casio\LotterySale\Api\Data\LotteryApplicationInterface $lotteryApplication
    ) {
        try {
            $lotteryApplicationModel = $this->lotteryApplicationFactory->create();
            $this->resource->load($lotteryApplicationModel, $lotteryApplication->getId());
            $this->resource->delete($lotteryApplicationModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the LotteryApplication: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param string $lotteryApplicationId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($lotteryApplicationId)
    {
        return $this->delete($this->get($lotteryApplicationId));
    }
}
