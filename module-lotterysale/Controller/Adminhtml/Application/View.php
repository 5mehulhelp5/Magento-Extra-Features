<?php

namespace Casio\LotterySale\Controller\Adminhtml\Application;

use Casio\LotterySale\Model\LotteryApplicationFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class View extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var LotteryApplicationFactory
     */
    private LotteryApplicationFactory $lotteryApplicationFactory;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LotteryApplicationFactory $lotteryApplicationFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LotteryApplicationFactory $lotteryApplicationFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->lotteryApplicationFactory = $lotteryApplicationFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Casio_LotterySale::listing');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $id = $this->_request->getParam('id');
        $model = $this->lotteryApplicationFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Lottery application no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $resultPage->getConfig()->getTitle()->prepend(__("Casio"));
        $resultPage->getConfig()->getTitle()->prepend(__("Lottery Application Management"));
        $resultPage->getConfig()->getTitle()->prepend(__($model->getLotterySalesCode()));

        return $resultPage;
    }
}
