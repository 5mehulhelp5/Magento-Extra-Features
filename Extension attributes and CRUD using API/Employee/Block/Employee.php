<?php

namespace Task\Employee\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Task\Employee\Api\EmployeeRepositoryInterface;
use Task\Employee\Model\ResourceModel\Employee\CollectionFactory;

class Employee extends Template
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var EmployeeRepositoryInterface
     */
    private EmployeeRepositoryInterface $employeeRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * Employee constructor.
     *
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param EmployeeRepositoryInterface $employeeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        EmployeeRepositoryInterface $employeeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory=$collectionFactory;
        $this->employeeRepository=$employeeRepository;
        $this->searchCriteriaBuilder=$searchCriteriaBuilder;
    }

    /**
     * Return the Edit Url
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('employee/index/edit');
    }

    /**
     * Return the Save Url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('employee/index/save');
    }
    /**
     * Return the Delete Url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('employee/index/delete');
    }
    /**
     * Get Data by Id
     *
     * @param int $Id
     * @return \Task\Employee\Api\Data\EmployeeInterface
     */
    public function getUpdate()
    {
        $id=$this->getRequest()->getParam('id');
        $collection=$this->employeeRepository->getById($id);
        return $collection;
    }

    /**
     * Return the All Employee Details
     *
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getAllEmployees()
    {
        $collection= $this->employeeRepository->getList($this->searchCriteriaBuilder->create());
        return $collection->getItems();
    }
}
