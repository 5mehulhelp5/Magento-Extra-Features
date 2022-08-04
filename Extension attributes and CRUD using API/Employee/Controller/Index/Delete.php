<?php

namespace Task\Employee\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Task\Employee\Api\EmployeeRepositoryInterface;

class Delete extends Action
{
    /**
     * @var EmployeeRepositoryInterface
     */
    private EmployeeRepositoryInterface $employeeRepository;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param EmployeeRepositoryInterface $employeeRepository
     */
    public function __construct(
        Context $context,
        EmployeeRepositoryInterface $employeeRepository
    ) {
        parent::__construct($context);
        $this->employeeRepository=$employeeRepository;
    }

    /**
     * Delete the Employee by id
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model=$this->employeeRepository->load($id);
            $model->delete();
            $this->messageManager->addSuccessMessage(__("Successfully deleted"));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Error In Deleteing Employee"));
        }
        return $this->resultRedirectFactory->create()->setPath('employee/index/view');
    }
}
