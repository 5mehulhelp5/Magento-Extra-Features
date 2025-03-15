<?php
namespace Codilar\InquiryPopup\Controller\Adminhtml\InquiryPopup;

use Codilar\InquiryPopup\Model\InquiryPopup;
use Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup as InquiryPopupResource;
use Magento\Framework\App\Action\Context;


class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var InquiryPopup
     */
    private $product;
    /**
     * @var InquiryPopupResource
     */
    private $productResource;


    public function __construct(Context $context, InquiryPopup $product, InquiryPopupResource $productResource,
                                array $data = []
                                 )
    {
        $this->product = $product;
        $this-> productResource= $productResource;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
    }


    public function execute()
    {
        /* Get the post data */
        $data = $this->getRequest()->getParams();
        
        
        // $file = $this->getRequest()->getFiles();
        
        $temp=$this->product->setData($data);

                   /* Use the resource model to save the model in the DB */
            $this->productResource->save($temp);
            $this->messageManager->addSuccessMessage("Product saved successfully!");
            $post = $this->getRequest()->getParams();
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('inquirydetails/inquirypopup/index');
            return $redirect;
    }
}























