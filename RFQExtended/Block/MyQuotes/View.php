<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Block\MyQuotes;

use Codilar\RFQExtended\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedForm\CollectionFactory as MyQuotesCollectionFactory;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedItems\CollectionFactory as QuotesItemCollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Codilar\RFQExtended\Model\RfqQuotesStatus;
use Magento\Authorization\Model\UserContextInterface;

class View extends Template
{
    /**
     * @var MyQuotesCollectionFactory
     */
    private MyQuotesCollectionFactory $myQuotesCollectionFactory;
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;
    /**
     * @var QuotesItemCollectionFactory
     */
    private QuotesItemCollectionFactory $quotesItemCollectionFactory;
    /**
     * @var RfqQuotesStatus
     */
    private RfqQuotesStatus $myQuotesStatus;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * View constructor.
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param MyQuotesCollectionFactory $myQuotesCollectionFactory
     * @param QuotesItemCollectionFactory $quotesItemCollectionFactory
     * @param RfqQuotesStatus $myQuotesStatus
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        UserContextInterface $userContext,
        MyQuotesCollectionFactory $myQuotesCollectionFactory,
        QuotesItemCollectionFactory $quotesItemCollectionFactory,
        RfqQuotesStatus $myQuotesStatus,
        Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->myQuotesCollectionFactory = $myQuotesCollectionFactory;
        $this->userContext = $userContext;
        $this->quotesItemCollectionFactory = $quotesItemCollectionFactory;
        $this->myQuotesStatus = $myQuotesStatus;
        $this->helperData = $helperData;
    }

    /**
     * Checks whether customer is logged in or not
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        if($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the Customer Id if customer logged in
     *
     * @return int|null
     */
    public function getCustomerId() {
        if($this->isCustomerLoggedIn()) {
            return $this->userContext->getUserId();
        } else {
            return null;
        }
    }

    /**
     * Return the MyQuotes Data
     *
     * @param $state
     * @return DataObject[]
     */
    public function getMyQuotes($state)
    {
        $customerId = $this->getCustomerId();
        if($state=='all') {
            return $this->myQuotesCollectionFactory->create()->addFieldToFilter('customer_id',$customerId)->getItems();
        }
        return  $this->myQuotesCollectionFactory->create()->addFieldToFilter('customer_id',$customerId)->addFieldToFilter('state',$state)->getItems();
    }

    /**
     * Return the Quote Items Data by quote Id
     *
     * @param $quoteId
     * @return DataObject[]
     */
    public function getQuoteItems($quoteId)
    {
        return $this->quotesItemCollectionFactory->create()->addFieldToFilter('parent_id',$quoteId)->getItems();
    }

    /**
     * Return all the Quote States
     *
     * @return string[]
     */
    public function getAllQuoteState()
    {
        $allQuoteStatus = $this->myQuotesStatus->getAllQuoteStatus();
        $defaultStatus = ['label'=>'All','value'=>'all'];
        array_unshift($allQuoteStatus, $defaultStatus);
        return $allQuoteStatus;
    }

    /**
     * return the QuoteItem Page Url
     *
     * @return string
     */
    public function getQuoteItemUrl()
    {
        return 'quick-quote/quote/items/';
    }

    public function getRfqStatus($value)
    {
        return $this->helperData->getRfqStatus($value);
    }
}
