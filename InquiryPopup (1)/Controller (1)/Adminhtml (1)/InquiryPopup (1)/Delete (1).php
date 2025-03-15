<?php

namespace Codilar\InquiryPopup\Controller\Adminhtml\InquiryPopup;
use Magento\Framework\App\Action\Action;
use Codilar\InquiryPopup\Model\InquiryPopupFactory as ModelFactory;
use Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup as ResourceModel;
use Magento\Framework\App\Action\Context;

class Delete extends Action
{
    /**
     * @var ModelFactory
     */
    protected $modelFactory;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    public function __construct(
        Context $context,
        ModelFactory $modelFactory,
        ResourceModel $resourceModel
    )
    {
        parent::__construct($context);
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
    }

    public function execute()
    {
        $model = $this->modelFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        $model->load($id);
        $model->delete();
        $this->messageManager->addSuccessMessage(__("successfully deleted"));
        return $this->resultRedirectFactory->create()->setPath('inquirydetails/inquirypopup/index');
    }
}