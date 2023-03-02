<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Helper;

use Codilar\RFQExtended\Model\RFQExtendedFormFactory;
use Codilar\RFQExtended\Model\RFQExtendedForm;
use Codilar\RFQExtended\Model\RFQExtendedItemsFactory;
use Codilar\RFQExtended\Model\RfqQuotesStatus;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\NegotiableQuote\Controller\FileProcessor;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class Data extends AbstractHelper
{
    const INVALID_FORM_MESSAGE = "Invalid Form Id!";

    const QUOTE_CONVERT_SUCCESS_MESSAGE = "The Form has been Converted to Quote.";

    const SKU_NOT_FOUND_MESSAGE = "SKU not found.";

    /**
     * @var RFQExtendedFormFactory
     */
    protected RFQExtendedFormFactory $rfqExtendedFormFactory;

    /**
     * @var RFQExtendedItemsFactory
     */
    protected RFQExtendedItemsFactory $rfqExtendedItemsFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;

    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var FileProcessor
     */
    protected FileProcessor $fileProcessor;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    protected NegotiableQuoteManagementInterface $negotiableQuoteManagement;

    /**
     * @var CartManagementInterface
     */
    protected CartManagementInterface $quoteManagement;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var Attribute
     */
    protected Attribute $attributes;

    /**
     * @var RfqQuotesStatus
     */
    protected RfqQuotesStatus $rfqQuotesStatus;

    /**
     * @param Context $context
     * @param RFQExtendedFormFactory $rfqExtendedFormFactory
     * @param RFQExtendedItemsFactory $rfqExtendedItemsFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepository $productRepository
     * @param FileProcessor $fileProcessor
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param CartManagementInterface $quoteManagement
     * @param ManagerInterface $messageManager
     * @param Attribute $attributes
     * @param RfqQuotesStatus $rfqQuotesStatus
     */
    public function __construct(
        Context $context,
        RFQExtendedFormFactory $rfqExtendedFormFactory,
        RFQExtendedItemsFactory $rfqExtendedItemsFactory,
        CartRepositoryInterface $cartRepository,
        ProductRepository $productRepository,
        FileProcessor $fileProcessor,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        CartManagementInterface $quoteManagement,
        ManagerInterface $messageManager,
        Attribute $attributes,
        RfqQuotesStatus $rfqQuotesStatus
    )
    {
        $this->rfqExtendedFormFactory = $rfqExtendedFormFactory;
        $this->rfqExtendedItemsFactory = $rfqExtendedItemsFactory;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->fileProcessor = $fileProcessor;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->quoteManagement = $quoteManagement;
        $this->messageManager = $messageManager;
        $this->attributes = $attributes;
        $this->rfqQuotesStatus = $rfqQuotesStatus;
        parent::__construct($context);
    }

    /**
     * @throws NoSuchEntityException | CouldNotSaveException | LocalizedException|\Exception
     */
    public function quoteConvertToRFQ($formId)
    {
        try {
            $rfqExtendedForm = $this->rfqExtendedFormFactory->create()->load($formId);
            if ($rfqExtendedForm->getEntityId()) {
                $rfqExtendedItems = $this->rfqExtendedItemsFactory->create()->getCollection();
                $rfqExtendedItems->addFieldToFilter('parent_id', $formId);

                $quoteId = $this->quoteManagement->createEmptyCartForCustomer($rfqExtendedForm->getCustomerId());
                $quote = $this->cartRepository->get($quoteId);

                foreach ($rfqExtendedItems as $item) {
                    if ($item->getSku() !== null) {
                        $product = $this->productRepository->get($item->getSku());
                        if ($product->getEntityId()) {
                            $quote->addProduct($product, $item->getQty());
                            $this->cartRepository->save($quote);
                            $quote->collectTotals();
                        } else {
                            $this->messageManager->addError(__(self::SKU_NOT_FOUND_MESSAGE));
                        }
                    }
                }

                $files = $this->fileProcessor->getFiles();
                $this->removeAddresses($quote);
                $this->negotiableQuoteManagement->create($quote->getId(), $rfqExtendedForm->getQuoteName(), $rfqExtendedForm->getQuoteName(), $files);

                $rfqExtendedForm->setState(RFQExtendedForm::FORM_STATE_CONVERTED_VALUE);
                $rfqExtendedForm->save();
                $this->messageManager->addSuccess(__(self::QUOTE_CONVERT_SUCCESS_MESSAGE));
            } else {
                $this->messageManager->addError(__(self::INVALID_FORM_MESSAGE));
            }
        } catch (NoSuchEntityException | CouldNotSaveException | LocalizedException $exception) {
            $this->messageManager->addError(__($exception->getMessage()));
        }
    }

    /**
     * Remove address from quote.
     *
     * @param CartInterface $quote
     * @return $this
     */
    private function removeAddresses(CartInterface $quote)
    {
        if ($quote->getBillingAddress()) {
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->getBillingAddress();
        }
        if ($quote->getShippingAddress()) {
            $quote->removeAddress($quote->getShippingAddress()->getId());
            $quote->getShippingAddress();
        }
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getShippingAssignments()) {
            $quote->getExtensionAttributes()->setShippingAssignments(null);
        }
        return $this;
    }

    /**
     * To get Manufacture options.
     *
     * @return array
     * @throws LocalizedException
     */
    public function getManufactureOptionsArray()
    {
        $options = [];
        $attributesList = $this->attributes->loadByCode('catalog_product', 'brand_name');

        foreach ($attributesList->getSource()->getAllOptions() as $optionInfo) {
            if($optionInfo['value'] != '')
                $options[] = ['value'=> $optionInfo['value'], 'label' => $optionInfo['label']];
        }

        return $options;
    }

    /**
     * @param $value
     * @return string
     * @throws LocalizedException
     */
    public function getManufactureOption($value)
    {
        $manufacturerOptions = $this->getManufactureOptionsArray();
        foreach ($manufacturerOptions as $manufacturerOption) {
            if ($value == $manufacturerOption['value'])
            {
                return $manufacturerOption['label'];
            }
        }
        return '';
    }

    /**
     * To get RFQ status from option
     *
     * @param $value
     * @return mixed|void
     */
    public function getRfqStatus($value)
    {
        $allStatus = $this->rfqQuotesStatus->getAllQuoteStatus();
        foreach ($allStatus as $status) {
            if ($status['value'] == $value)
            {
                return $status['label'];
            }
        }
    }
}
