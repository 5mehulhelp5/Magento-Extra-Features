<?php

namespace Codilar\InquiryPopup\Controller\Adminhtml\InquiryPopup;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Codilar\InquiryPopup\Model\InquiryPopupFactory as ModelFactory;
use Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup as ResourceModel;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

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
        PageFactory $pageFactory,
        ModelFactory $modelFactory,
        ResourceModel $resourceModel
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
    }
     public function execute()
    {

         $page=$this->pageFactory->create();
         $data=$this->getRequest()->getParams();
         $inquirypopup = $this->modelFactory->create();
         $inquirypopup->load($data['entity_id']);
         $page->getConfig()->getTitle()->set('InquiryPopup '.$inquirypopup->getProductName().' details');
          return $page;
    }
}
