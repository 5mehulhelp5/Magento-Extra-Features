<?php

namespace Task\Employee\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Task\Employee\Api\Data\EmployeeInterface;
use Task\Employee\Api\EmployeeRepositoryInterface;
use Task\Employee\Api\EmployeeAddressRepositoryInterface;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $pageFactory;
    /**
     * @var EmployeeRepositoryInterface
     */
    protected EmployeeRepositoryInterface $employeeRepository;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var EmployeeAddressRepositoryInterface
     */
    private EmployeeAddressRepositoryInterface $employeeAddressRepository;

    /**
     * View constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EmployeeRepositoryInterface $employeeRepository
     * @param PageFactory $pageFactory
     * @param EmployeeAddressRepositoryInterface $employeeAddressRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmployeeRepositoryInterface $employeeRepository,
        PageFactory $pageFactory,
        EmployeeAddressRepositoryInterface $employeeAddressRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory=$resultJsonFactory;
        $this->employeeRepository=$employeeRepository;
        $this->pageFactory = $pageFactory;
        $this->employeeAddressRepository=$employeeAddressRepository;
    }

    /**
     * Return the Json Data
     *
     * @return ResponseInterface|Json|ResultInterface|EmployeeInterface
     */
    public function execute()
    {
        $resultJson=$this->resultJsonFactory->create();
        try {
            $collection=$this->employeeRepository->getById(1)->getData();
            return $resultJson->setData($collection);
        } catch (NoSuchEntityException $e) {
             return $resultJson->setData($e->getMessage());
        }
    }
}
