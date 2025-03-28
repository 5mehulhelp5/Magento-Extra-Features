<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Enrollment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \KalyanUs\Scheme\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \KalyanUs\Scheme\Model\EnrollmentProcess
     */
    protected $enrollmentProcess;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry=$coreRegistry;
        $this->customerSession = $customerSession;
        $this->enrollmentProcess=$enrollmentProcess;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id= $this->getRequest()->getParam('id');
        if ($this->customerSession->isLoggedIn()) {
            if ($id) {
                $enrollment=$this->enrollmentProcess->geCustomertEnrollmentInfoById(
                    $id,
                    $this->customerSession->getCustomerId()
                );
                if (count($enrollment)>0) {
                    $this->coreRegistry->register('current_enrollment_view', $enrollment);
                } else {
                    $this->_redirect('scheme/enrollment/planbook');
                    return;
                }
            } else {
                $this->_redirect('scheme/enrollment/planbook');
                return;
            }
            return $this->resultPageFactory->create();
        } else {
            $this->_redirect('customer/account/');
            return;
        }
    }
}
