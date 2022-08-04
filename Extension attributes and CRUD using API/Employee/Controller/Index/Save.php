<?php


namespace Task\Employee\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Task\Employee\Api\EmployeeRepositoryInterface;
use Magento\Framework\App\Action\Context;

class Save extends Action
{
    /**
     * @var EmployeeRepositoryInterface
     */
    private EmployeeRepositoryInterface $employeeRepository;

    /**
     * Save constructor.
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
     * Save the Employeee Details
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $model=$this->employeeRepository->create();
        $data=$this->getRequest()->getParams();
        if (!empty($data['id'])) {
            $model=$this->employeeRepository->load($data['id']);
        }
        $model->setIsActive($data['is_active']);
        $model->setFirstName($data['first_name']);
        $model->setLastName($data['last_name']);
        $model->setDob($data['dob']);
        $model->setPrice($data['price']);
        $model->setWeight($data['weight']);
        try {
            $this->employeeRepository->save($model);
            $this->messageManager->addSuccessMessage(__('%1 Saved Successfully', $model->getFirstName()));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Error saving Brand"));
        }
        return $this->resultRedirectFactory->create()->setPath('employee/index/view');
    }
}
