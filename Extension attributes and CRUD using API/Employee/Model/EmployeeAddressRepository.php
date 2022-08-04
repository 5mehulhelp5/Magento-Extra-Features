<?php

namespace Task\Employee\Model;

use Task\Employee\Api\Data\EmployeeAddressInterface;
use Task\Employee\Api\Data\EmployeeSearchResultInterface;
use Task\Employee\Api\EmployeeAddressRepositoryInterface;
use Task\Employee\Model\EmployeeAddressFactory as ModelFactory;
use Task\Employee\Model\ResourceModel\EmployeeAddress as ResourceModel;
use Task\Employee\Model\ResourceModel\EmployeeAddress\Collection;
use Task\Employee\Model\ResourceModel\EmployeeAddress\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Task\Employee\Api\Data\EmployeeSearchResultInterfaceFactory;

class EmployeeAddressRepository implements EmployeeAddressRepositoryInterface
{
    /**
     * @var EmployeeAddressFactory
     */
    private EmployeeAddressFactory $modelFactory;
    /**
     * @var Collection
     */
    private Collection $collection;
    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var SearchCriteriaInterface
     */
    private SearchCriteriaInterface $searchCriteria;
    /**
     * @var EmployeeSearchResultInterfaceFactory
     */
    private EmployeeSearchResultInterfaceFactory $employeeSearchResultInterfaceFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * EmployeeAddressRepository constructor.
     * @param CollectionFactory $collectionFactory
     * @param Collection $collection
     * @param EmployeeAddressFactory $modelFactory
     * @param ResourceModel $resourceModel
     * @param SearchCriteriaInterface $searchCriteria
     * @param CollectionProcessorInterface $collectionProcessor
     * @param EmployeeSearchResultInterfaceFactory $employeeSearchResultInterfaceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Collection $collection,
        ModelFactory $modelFactory,
        ResourceModel $resourceModel,
        SearchCriteriaInterface $searchCriteria,
        CollectionProcessorInterface $collectionProcessor,
        EmployeeSearchResultInterfaceFactory $employeeSearchResultInterfaceFactory
    ) {
        $this->modelFactory=$modelFactory;
        $this->collection=$collection;
        $this->resourceModel=$resourceModel;
        $this->collectionFactory=$collectionFactory;
        $this->searchCriteria=$searchCriteria;
        $this->collectionProcessor=$collectionProcessor;
        $this->employeeSearchResultInterfaceFactory=$employeeSearchResultInterfaceFactory;
    }

    /**
     * Get Student Data by Id
     *
     * @param int $Id
     * @return EmployeeAddress
     */
    public function getById($Id)
    {
        $model=$this->modelFactory->create();
        $this->resourceModel->load($model, $Id);
        return $model;
    }

    /**
     * Return the Address Data by ID
     *
     * @param int $addressId
     * @return EmployeeAddressInterface|array
     */
    public function getByAddressId($addressId)
    {
        $object=$this->modelFactory->create();
        $collection=$object->getCollection();
        $collection->addFieldToFilter('address_id', $addressId);
        return $collection->getData();
    }
    /**
     * Return Collection
     *
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }
    /**
     * Return the List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EmployeeSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection =$this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, ($collection));
        $searchResult=$this->employeeSearchResultInterfaceFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }
}
