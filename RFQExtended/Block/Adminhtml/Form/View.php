<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Block\Adminhtml\Form;

use Codilar\RFQExtended\Helper\Data;
use Codilar\RFQExtended\Model\RFQExtendedForm;
use Codilar\RFQExtended\Model\RfqQuotesStatus;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Codilar\RFQExtended\Model\RFQExtendedFormFactory;
use Codilar\RFQExtended\Model\RFQExtendedItemsFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

class View extends Template
{
    const ADMIN_FORM_SAVE_URL = "rfqextended/form/save";

    const UPDATE_BUTTON_TITLE = "Update Form";

    const CONVERT_TO_QUOTE_BUTTON_TITTLE = "Convert to Quote";

    const REJECT_REQUEST_BUTTON_TITTLE = "Reject Request";

    /**
     * @var RFQExtendedFormFactory
     */
    protected RFQExtendedFormFactory $rfqExtendedFormFactory;

    /**
     * @var RFQExtendedItemsFactory
     */
    protected RFQExtendedItemsFactory $rfqExtendedItemsFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var RfqQuotesStatus
     */
    protected RfqQuotesStatus $rfqQuotesStatus;

    /**
     * @param Context $context
     * @param RFQExtendedFormFactory $rfqExtendedFormFactory
     * @param RFQExtendedItemsFactory $rfqExtendedItemsFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $helperData
     * @param RfqQuotesStatus $rfqQuotesStatus
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context $context,
        RFQExtendedFormFactory $rfqExtendedFormFactory,
        RFQExtendedItemsFactory $rfqExtendedItemsFactory,
        CustomerRepositoryInterface $customerRepository,
        Data $helperData,
        RfqQuotesStatus $rfqQuotesStatus,
        array $data = [],
        JsonHelper $jsonHelper = null,
        DirectoryHelper $directoryHelper = null
    )
    {
        $this->rfqExtendedFormFactory = $rfqExtendedFormFactory;
        $this->rfqExtendedItemsFactory = $rfqExtendedItemsFactory;
        $this->customerRepository = $customerRepository;
        $this->helperData = $helperData;
        $this->rfqQuotesStatus = $rfqQuotesStatus;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * @return string
     */
    public function getFormActionURl()
    {
        return $this->getUrl(self::ADMIN_FORM_SAVE_URL, ['_secure' => true]);
    }

    /**
     * @return RFQExtendedForm|false
     */
    public function getRFQExtendedFormData()
    {
        $formId = $this->_request->getParam('id');

        if(isset($formId) && $formId) {
            return $this->rfqExtendedFormFactory->create()->load($formId);
        }
        return false;
    }


    /**
     * @return false|\Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     */
    public function getRFQExtendedItemsData()
    {
        $formId = $this->_request->getParam('id');

        if(isset($formId) && $formId) {
            $rfqItemsData = $this->rfqExtendedItemsFactory->create()->getCollection();
            $rfqItemsData->addFieldToFilter('parent_id', $formId);

            return $rfqItemsData;
        }
        return false;
    }

    /**
     * @throws NoSuchEntityException | LocalizedException
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $customerName = $customer->getFirstname() . " " . $customer->getLastname();
    }

    /**
     * @return string
     */
    public function getUpdateButtonTitle()
    {
        return self::UPDATE_BUTTON_TITLE;
    }

    /**
     * @return string
     */
    public function getConvertQuoteButtonTitle()
    {
        return self::CONVERT_TO_QUOTE_BUTTON_TITTLE;
    }

    /**
     * @return string
     */
    public function getRejectButtonTitle()
    {
        return self::REJECT_REQUEST_BUTTON_TITTLE;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getManufactureOptions(): array
    {
        return $this->helperData->getManufactureOptionsArray();
    }

    public function getRfqStatus($value)
    {
        return $this->helperData->getRfqStatus($value);
    }
}
