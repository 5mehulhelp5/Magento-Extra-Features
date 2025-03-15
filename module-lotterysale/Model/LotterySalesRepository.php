<?php

namespace Casio\LotterySale\Model;

use Casio\LotterySale\Api\Data\LotterySalesInterfaceFactory;
use Casio\LotterySale\Api\Data\LotterySalesSearchResultsInterfaceFactory;
use Casio\LotterySale\Api\LotterySalesRepositoryInterface;
use Casio\LotterySale\Model\ResourceModel\LotterySales as ResourceLotterySales;
use Casio\LotterySale\Model\ResourceModel\LotterySales\CollectionFactory as LotterySalesCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class LotterySalesRepository implements LotterySalesRepositoryInterface
{
    /**
     * @var ResourceLotterySales
     */
    protected $resource;

    /**
     * @var LotterySalesFactory
     */
    protected $lotterySalesFactory;

    /**
     * @var LotterySalesCollectionFactory
     */
    protected $lotterySalesCollectionFactory;

    /**
     * @var LotterySalesSearchResultsInterfaceFactory
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
     * @var LotterySalesInterfaceFactory
     */
    protected $dataLotterySalesFactory;

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
     * @param ResourceLotterySales $resource
     * @param LotterySalesFactory $lotterySalesFactory
     * @param LotterySalesInterfaceFactory $dataLotterySalesFactory
     * @param LotterySalesCollectionFactory $lotterySalesCollectionFactory
     * @param LotterySalesSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceLotterySales $resource,
        LotterySalesFactory $lotterySalesFactory,
        LotterySalesInterfaceFactory $dataLotterySalesFactory,
        LotterySalesCollectionFactory $lotterySalesCollectionFactory,
        LotterySalesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->lotterySalesFactory = $lotterySalesFactory;
        $this->lotterySalesCollectionFactory = $lotterySalesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataLotterySalesFactory = $dataLotterySalesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param \Casio\LotterySale\Api\Data\LotterySalesInterface $lotterySales
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface
     * @throws CouldNotSaveException
     */
    public function save(
        \Casio\LotterySale\Api\Data\LotterySalesInterface $lotterySales
    ) {
        $lotterySalesData = $this->extensibleDataObjectConverter->toNestedArray(
            $lotterySales,
            [],
            \Casio\LotterySale\Api\Data\LotterySalesInterface::class
        );

        $lotterySalesModel = $this->lotterySalesFactory->create()->setData($lotterySalesData);

        try {
            $this->resource->save($lotterySalesModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the lotterySales: %1',
                $exception->getMessage()
            ));
        }
        return $lotterySalesModel->getDataModel();
    }

    /**
     * @param string $lotterySalesId
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface
     * @throws NoSuchEntityException
     */
    public function get($lotterySalesId)
    {
        $lotterySales = $this->lotterySalesFactory->create();
        $this->resource->load($lotterySales, $lotterySalesId);
        if (!$lotterySales->getId()) {
            throw new NoSuchEntityException(__('LotterySales with id "%1" does not exist.', $lotterySalesId));
        }
        return $lotterySales->getDataModel();
    }

    /**
     * @param $productId
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface|mixed|string|null
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProductId($productId)
    {
        $id = $this->resource->getIdByProductId($productId);
        if ($id) {
            return $this->get($id);
        }
        return $id;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Casio\LotterySale\Api\Data\LotterySalesSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->lotterySalesCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Casio\LotterySale\Api\Data\LotterySalesInterface::class
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
     * @param \Casio\LotterySale\Api\Data\LotterySalesInterface $lotterySales
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(
        \Casio\LotterySale\Api\Data\LotterySalesInterface $lotterySales
    ) {
        try {
            $lotterySalesModel = $this->lotterySalesFactory->create();
            $this->resource->load($lotterySalesModel, $lotterySales->getId());
            $this->resource->delete($lotterySalesModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the LotterySales: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
}
