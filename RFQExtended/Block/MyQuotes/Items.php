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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Codilar\RFQExtended\Model\RFQExtendedFormFactory as RfqQuoteModelFactory;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedForm as RfqQuoteResourceModel;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedItems\CollectionFactory as QuotesItemCollectionFactory;
use Magento\Framework\View\Element\Template\Context;

class Items extends Template
{
    /**
     * @var QuotesItemCollectionFactory
     */
    private QuotesItemCollectionFactory $quoteItemCollectionFactory;
    /**
     * @var RfqQuoteModelFactory
     */
    private RfqQuoteModelFactory $rfqQuoteModelFactory;
    /**
     * @var RfqQuoteResourceModel
     */
    private RfqQuoteResourceModel $rfqQuoteResourceModel;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * Items constructor.
     * @param Context $context
     * @param RfqQuoteModelFactory $rfqQuoteModelFactory
     * @param RfqQuoteResourceModel $rfqQuoteResourceModel
     * @param QuotesItemCollectionFactory $quoteItemCollectionFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        RfqQuoteModelFactory $rfqQuoteModelFactory,
        RfqQuoteResourceModel $rfqQuoteResourceModel,
        QuotesItemCollectionFactory $quoteItemCollectionFactory,
        Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->rfqQuoteModelFactory = $rfqQuoteModelFactory;
        $this->rfqQuoteResourceModel = $rfqQuoteResourceModel;
        $this->helperData = $helperData;
    }


    /**
     * Return the quote item by quote id
     *
     * @param $quoteId
     * @return DataObject[]
     */
    public function getQuoteItems($quoteId)
    {
        return $this->quoteItemCollectionFactory->create()->addFieldToFilter('parent_id',$quoteId)->getItems();
    }

    /**
     * Return the Current Quote Data
     *
     * @param $quoteId
     * @return \Codilar\RFQExtended\Model\RFQExtendedForm
     */
    public function getQuoteData($quoteId)
    {
        $quote = $this->rfqQuoteModelFactory->create();
        $this->rfqQuoteResourceModel->load($quote,$quoteId);
        return $quote;
    }

    /**
     * For getting rfq status based on value
     */
    public function getRfqStatus($value)
    {
        return $this->helperData->getRfqStatus($value);
    }

    /**
     * @param $value
     * @return string
     * @throws LocalizedException
     */
    public function getManufacturerLabel($value): string
    {
        return $this->helperData->getManufactureOption($value);
    }
}
