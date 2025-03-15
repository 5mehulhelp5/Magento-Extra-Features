<?php

namespace Codilar\InquiryPopup\Controller\Adminhtml\InquiryPopup;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;


class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    protected $enableData;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
        
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        
    }


   
    public function execute()
    {
       
    return $this->resultRedirectFactory->create()->setPath('inquirydetails/inquirypopup/view');
        

    }
}