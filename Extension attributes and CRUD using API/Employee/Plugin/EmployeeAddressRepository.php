<?php

namespace Task\Employee\Plugin;

use Task\Employee\Api\Data\EmployeeAddressInterface;
use Task\Employee\Api\EmployeeAddressRepositoryInterface;
use Task\Employee\Api\EmployeeRepositoryInterface;
use Task\Employee\Api\Data\EmployeeAddressExtensionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

class EmployeeAddressRepository
{
    /**
     * @var EmployeeRepositoryInterface
     */
    private EmployeeRepositoryInterface $employeeRepository;
    /**
     * @var EmployeeAddressExtensionFactory
     */
    private EmployeeAddressExtensionFactory $employeeAddressExtensionFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * EmployeeAddressRepositoryInterface constructor.
     * @param EmployeeRepositoryInterface $employeeRepository
     * @param EmployeeAddressExtensionFactory $employeeAddressExtensionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        EmployeeAddressExtensionFactory $employeeAddressExtensionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->employeeRepository=$employeeRepository;
        $this->employeeAddressExtensionFactory=$employeeAddressExtensionFactory;
        $this->searchCriteriaBuilder=$searchCriteriaBuilder;
    }

    /**
     * Adding extension attribute Employee Details to getById
     *
     * @param EmployeeAddressRepositoryInterface $subject
     * @param EmployeeAddressInterface $employeeAddress
     * @return EmployeeAddressInterface
     */
    public function afterGetById(
        EmployeeAddressRepositoryInterface $subject,
        EmployeeAddressInterface $employeeAddress
    ) {
        $extensionAttributes=$employeeAddress->getExtensionAttributes();
        $employeeExtension = $extensionAttributes ?:$this->employeeAddressExtensionFactory->create();
        $EmployeeData=$this->employeeRepository->getDataById($employeeAddress->getAddressId());
        $employeeExtension->setEmployeeDetails($EmployeeData);
        $employeeAddress->setExtensionAttributes($employeeExtension);
        return $employeeAddress;
    }
}
