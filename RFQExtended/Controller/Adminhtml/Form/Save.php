<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Controller\Adminhtml\Form;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Codilar\RFQExtended\Model\RFQExtendedFormFactory;
use Codilar\RFQExtended\Model\RFQExtendedForm;
use Codilar\RFQExtended\Model\RFQExtendedItemsFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Codilar\RFQExtended\Helper\Data as RFQExtendedHelper;
use Codilar\RFQExtended\Block\Adminhtml\Form\View as FormViewBlock;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    const FORM_UPDATE_REJECT_MESSAGE = "The Form has been Rejected.";

    const FORM_UPDATE_SUCCESS_MESSAGE = "The Form has been Updated.";

    const FORM_UPDATE_INVALID_MESSAGE = "Invalid Form Id!";

    /**
     * @var RFQExtendedFormFactory
     */
    protected RFQExtendedFormFactory $rfqExtendedFormFactory;

    /**
     * @var RFQExtendedItemsFactory
     */
    protected RFQExtendedItemsFactory $rfqExtendedItemsFactory;

    /**
     * @var RFQExtendedHelper
     */
    protected RFQExtendedHelper $rfqExtendedHelper;

    /**
     * @param Context $context
     * @param RFQExtendedFormFactory $rfqExtendedFormFactory
     * @param RFQExtendedItemsFactory $rfqExtendedItemsFactory
     * @param RFQExtendedHelper $rfqExtendedHelper
     */
    public function __construct(
        Context $context,
        RFQExtendedFormFactory $rfqExtendedFormFactory,
        RFQExtendedItemsFactory $rfqExtendedItemsFactory,
        RFQExtendedHelper $rfqExtendedHelper
    )
    {
        $this->rfqExtendedFormFactory = $rfqExtendedFormFactory;
        $this->rfqExtendedItemsFactory = $rfqExtendedItemsFactory;
        $this->rfqExtendedHelper = $rfqExtendedHelper;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Codilar_RFQExtended::codilar");
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws CouldNotSaveException | LocalizedException | NoSuchEntityException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if($params['action'] == FormViewBlock::REJECT_REQUEST_BUTTON_TITTLE) {
            $rfqExtendedForm = $this->rfqExtendedFormFactory->create()->load($params['entity_id']);
            if ($rfqExtendedForm->getEntityId()) {
                $rfqExtendedForm->setState(RFQExtendedForm::FORM_STATE_REJECTED_VALUE);
                $rfqExtendedForm->save();
                $this->messageManager->addSuccess(__(self::FORM_UPDATE_REJECT_MESSAGE));
            } else {
                $this->messageManager->addError(__(self::FORM_UPDATE_INVALID_MESSAGE));
            }
        } else {
            $rfqExtendedItems = $this->rfqExtendedItemsFactory->create();
            for ($i = 0; $i < $params['count']; $i++) {
                $rfqExtendedItems = $rfqExtendedItems->load($params['item_id'][$i]);
                if ($rfqExtendedItems->getEntityId()) {
                    $rfqExtendedItems->setProductNameDesc($params['product_name_desc'][$i]);
                    $rfqExtendedItems->setQty($params['qty'][$i]);
                    $rfqExtendedItems->setSku($params['sku'][$i]);
                    $rfqExtendedItems->save();
                    $rfqExtendedItems->unsetData();
                }
            }

            if ($params['action'] == FormViewBlock::CONVERT_TO_QUOTE_BUTTON_TITTLE) {
                $this->rfqExtendedHelper->quoteConvertToRFQ($params['entity_id']);
            }
            $this->messageManager->addSuccess(__(self::FORM_UPDATE_SUCCESS_MESSAGE));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
