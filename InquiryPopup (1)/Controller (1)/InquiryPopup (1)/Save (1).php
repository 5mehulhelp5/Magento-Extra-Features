<?php
namespace Codilar\InquiryPopup\Controller\InquiryPopup;

use Codilar\InquiryPopup\Model\InquiryPopup;
use Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup as InquiryPopupResource;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;





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

   protected $resultJsonFactory;



    const XML_PATH_EMAIL_RECIPIENT_NAME = 'meghashree';
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'meghashetty512@gmail.com';

    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $storeManager;

    public function __construct(Context $context, InquiryPopup $product, InquiryPopupResource $productResource,
                                JsonFactory $resultJsonFactory,
                                \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
                                \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Psr\Log\LoggerInterface $loggerInterface,
                                StoreManagerInterface $storeManager,
                                array $data = []
                                 )
    {
        $this->product = $product;
        $this-> productResource= $productResource;
        $this->resultJsonFactory=$resultJsonFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }


    public function execute()
    {
        

        /* Get the post data */
        $data = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();
        $temp=$this->product->setData($data);

                   /* Use the resource model to save the model in the DB */
        $this->productResource->save($temp);

        $this->messageManager->addSuccessMessage("Product saved successfully!");
        $post = $this->getRequest()->getParams();
            



                // Send Mail
                $this->_inlineTranslation->suspend();


                $sentToEmail = $this->_scopeConfig ->getValue('trans_email/ident_general/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $sentToName = $this->_scopeConfig ->getValue('trans_email/ident_general/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);


                $transport = $this->_transportBuilder
                ->setTemplateIdentifier('customemail_email_template')
                ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                    )
                    ->setTemplateVars($post)
                    ->setFromByScope("general")
                    ->addTo($sentToEmail,$sentToName)
                    //->addTo('owner@example.com','owner')
                    ->getTransport();

                    $transport->sendMessage();

                    $this->_inlineTranslation->resume();

                    
        $this->messageManager->addSuccess('Email sent successfully');
        return $resultJson->setData([ 'true']);
        $redirect = $this->resultRedirectFactory->create();
        //$this->messageManager->addSuccessMessage("Product saved successfully!");
        $redirect->setPath('inquirydetails/inquirypopup/save');
        return $redirect;
    }
}























