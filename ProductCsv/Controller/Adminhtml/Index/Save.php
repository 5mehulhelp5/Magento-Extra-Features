<?php

namespace Codilar\ProductCsv\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Codilar\ProductCsv\Model\Uploader;
use Codilar\ProductCsv\Model\ProductCsvFactory as ModelFactory;
use Codilar\ProductCsv\Model\ResourceModel\ProductCsv as ResourceModel;


class Save extends \Magento\Backend\App\Action
{
    public $Uploader;
    protected $modelFactory;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    public function __construct(
        Context $context,
        Uploader $Uploader,
        ModelFactory $modelFactory,
        ResourceModel $resourceModel
    )
    {
        parent::__construct($context);
        $this->Uploader = $Uploader;
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
    }

    public function execute()
    {
        try {
            
            $result = $this->Uploader->saveFileToTmpDir('productcsv');
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        // $data = $this->getRequest()->getParams();
        $ProductCsv = $this->modelFactory->create();
         $ProductCsv->setProductcsv($this->_getSession()->getName().$this->_getSession()->getCookiePath().$this->_getSession()->getCookieDomain());
        try {
            
            $this->resourceModel->save($ProductCsv);
            $this->messageManager->addSuccessMessage(__('ProductCsv %1 saved successfully', $ProductCsv->getName()));
        } 
        catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Error saving ProductCsv"));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        
    }
}