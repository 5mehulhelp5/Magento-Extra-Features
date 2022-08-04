<?php

namespace Task\Employee\Plugin;

use Task\Employee\Api\Data\EmployeeInterface;
use Task\Employee\Api\EmployeeRepositoryInterface;
use Task\Employee\Model\ResourceModel\Employee\CollectionFactory;
use Task\Employee\Api\EmployeeAddressRepositoryInterface;
use Task\Employee\Api\Data\EmployeeExtensionFactory;
use Task\Employee\Api\EmployeeRepositoryExtension;
use Magento\Framework\Api\SearchCriteriaBuilder;

class EmployeeRepository
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var EmployeeAddressRepositoryInterface
     */
    private EmployeeAddressRepositoryInterface $addressRepository;
    /**
     * @var EmployeeRepositoryExtension
     */
    private EmployeeRepositoryExtension $employeeRepositoryExtension;
    /**
     * @var EmployeeExtensionFactory
     */
    private EmployeeExtensionFactory $employeeExtensionFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * EmployeeRepositoryInterface constructor.
     * @param CollectionFactory $collectionFactory
     * @param EmployeeAddressRepositoryInterface $addressRepository
     * @param EmployeeExtensionFactory $employeeExtensionFactory
     * @param EmployeeRepositoryExtension $employeeRepositoryExtension
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        EmployeeAddressRepositoryInterface $addressRepository,
        EmployeeExtensionFactory $employeeExtensionFactory,
        EmployeeRepositoryExtension $employeeRepositoryExtension,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->addressRepository = $addressRepository;
        $this->employeeExtensionFactory=$employeeExtensionFactory;
        $this->employeeRepositoryExtension = $employeeRepositoryExtension;
        $this->searchCriteriaBuilder=$searchCriteriaBuilder;
    }

    /**
     * Adding extension attribute Address Items to getById
     *
     * @param EmployeeRepositoryInterface $subject
     * @param EmployeeInterface $employee
     * @return EmployeeInterface
     */
    public function afterGetById(
        EmployeeRepositoryInterface $subject,
        EmployeeInterface $employee
    ) {
        $filter=$this->searchCriteriaBuilder->addFilter('address_id', $employee->getId());
        $extensionAttributes=$employee->getExtensionAttributes();
        $employeeExtension = $extensionAttributes ? : $this->employeeExtensionFactory->create();
        $AddressData= $this->addressRepository->getList($filter->create())->getItems();
        $employeeExtension->setAddressItems($AddressData);
        $employee->setExtensionAttributes($employeeExtension);
        return $employee;
    }
}
