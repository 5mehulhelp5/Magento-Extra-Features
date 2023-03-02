<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Controller\Quote;

use Magento\Framework\App\ActionInterface;
use Codilar\RFQExtended\Model\RFQExtendedFormFactory;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedForm as RFQExtendedFormResourceModel;
use Codilar\RFQExtended\Model\RFQExtendedItemsFactory;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedItems as RFQExtendedItemsResourceModel;
use Magento\Framework\Exception\AlreadyExistsException;
use Codilar\RFQExtended\Model\RFQExtendedForm;
use Codilar\RFQExtended\Model\RfqQuotesStatus;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save implements ActionInterface
{
    const SUCCESS_MESSAGE = 'The Request has been Saved.';

    const FAILURE_MESSAGE = 'Unable to Save, Try Again!';

    protected RFQExtendedFormFactory $rfqExtendedFormFactory;

    protected RFQExtendedFormResourceModel $rfqExtendedFormResourceModel;

    protected RFQExtendedItemsFactory $rfqExtendedItemsFactory;

    protected RFQExtendedItemsResourceModel $rfqExtendedItemsResourceModel;

    protected ResultFactory $resultFactory;

    protected RedirectInterface $redirect;

    protected MessageManagerInterface $messageManager;

    protected RequestInterface $request;

    protected UploaderFactory $_fileUploaderFactory;

    protected Filesystem $_filesystem;

    public function __construct(
        RFQExtendedFormFactory $rfqExtendedFormFactory,
        RFQExtendedFormResourceModel $rfqExtendedFormResourceModel,
        RFQExtendedItemsFactory $rfqExtendedItemsFactory,
        RFQExtendedItemsResourceModel $rfqExtendedItemsResourceModel,
        ResultFactory $resultFactory,
        RedirectInterface $redirect,
        MessageManagerInterface $messageManager,
        RequestInterface $request,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory
    )
    {
        $this->rfqExtendedFormFactory = $rfqExtendedFormFactory;
        $this->rfqExtendedFormResourceModel = $rfqExtendedFormResourceModel;
        $this->rfqExtendedItemsFactory = $rfqExtendedItemsFactory;
        $this->rfqExtendedItemsResourceModel = $rfqExtendedItemsResourceModel;
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $filesystem;
    }

    public function execute()
    {
        $params = $this->request->getParams();

//        print_r($params);die;

        try {
            if(isset($params['customer_id'])) {
                $rfqExtendedFormModel = $this->rfqExtendedFormFactory->create();
                $rfqExtendedFormModel->setData([
                    'customer_id' => $params['customer_id'],
                    'quote_name' => $params['quote_name'],
                    'state' => RfqQuotesStatus::QUOTE_STATUS_OPEN_VALUE
                ]);
                $this->rfqExtendedFormResourceModel->save($rfqExtendedFormModel);
                if ($rfqExtendedFormModel->getEntityId()) {
                    $rfqExtendedItemsModel = $this->rfqExtendedItemsFactory->create();
                    for($i = 1; $i <= $params['count']; $i++) {
                        $imageName = '';
                        if(isset($_FILES['attachment']['name'][$i]) && !empty($_FILES['attachment']['name'][$i])) {
                            $field_name = "attachment[".$i."]";
                            $uploader = $this->_fileUploaderFactory->create(['fileId' => $field_name]);
                            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(false);
                            $path = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('rfq/');
                            $uploader->save($path);
                            $imageName = $uploader->getUploadedFileName();
                        }

                        $knownManufacturer = (isset($params['known_manufacturer'][$i])) ? 1 : 0;
                        $rfqExtendedItemsModel->setData([
                            'parent_id' => $rfqExtendedFormModel->getEntityId(),
                            'known_manufacturer' => $knownManufacturer,
                            'manufacture_name' => $params['manufacture_name'][$i],
                            'mfg_part' => $params['mfg_part'][$i],
                            'product_name' => $params['product_name'][$i],
                            'description' => $params['description'][$i],
                            'annual_usage' => $params['annual_usage'][$i],
                            'requested_qty' => $params['requested_qty'][$i],
                            'attachment' => $imageName
                        ]);
                        $this->rfqExtendedItemsResourceModel->save($rfqExtendedItemsModel);
                        $rfqExtendedItemsModel->unsetData();
                    }
                    $this->messageManager->addSuccessMessage( __(self::SUCCESS_MESSAGE) );
                }
            }
        } catch (AlreadyExistsException | \Exception $exception) {
            $this->messageManager->addErrorMessage( __(self::FAILURE_MESSAGE) );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }

}
