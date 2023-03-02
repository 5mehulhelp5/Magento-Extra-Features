<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\Postloginhome\Block\Index;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteRepository;
use Magento\NegotiableQuote\Model\NegotiableQuoteFactory;

/**
 * Class to provide Negotiable Custom quote list
 *
 */
class MyRfq extends Template
{
    /**
     * @var UserContextInterface
     */
    protected UserContextInterface $userContext;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var NegotiableQuoteRepository
     */
    protected NegotiableQuoteRepository $negotiableQuoteRepository;
     
    /**
     * @var NegotiableQuoteFactory
     */
    protected NegotiableQuoteFactory $negotiableQuoteFactory;

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param StoreManagerInterface $storeManager
     * @param NegotiableQuoteRepository $negotiableQuoteRepository
     * @param NegotiableQuoteFactory $negotiableQuoteFactory
     * @param array $data [optional]
     */

    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        StoreManagerInterface $storeManager,
        NegotiableQuoteRepository $negotiableQuoteRepository,
        NegotiableQuoteFactory $negotiableQuoteFactory,
        array $data = [])
    {
        $this->userContext = $userContext;
        $this->storeManager = $storeManager;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get baseurl
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        if($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        if($this->isCustomerLoggedIn()) {
            return $this->userContext->getUserId();
        } else {
            return null;
        }
    }

    /**
     * Get Rfq data collection
     *
     * @return array
     */
    public function getRfqCollection()
    {
        $customerId = $this->getCustomerId();
        $searchResult = $this->negotiableQuoteRepository->getListByCustomerId($customerId);
        $negQuotedata = [];
        if(count($searchResult)>0){
            foreach ($searchResult as $item) {
                $negdata = [
                    "entity_id" => $item->getId(),
                    "store_id" => $item->getStoreId(),
                    "created_at"  => $item->getCreatedAt(),
                    "items_qty" => $item->getItemsQty(),
                    "customer_id" => $item->getCustomerId(),
                    "grand_total" => $item->getGrandTotal(),
                    "rfqinfo" => $this->getCustomRfqInfo($item->getId()),
                ];
                $negQuotedata[] = $negdata;
            }
        }
        return $negQuotedata;
    }


    /**
     * Get Rfq custom Data
     *
     * @return array
     */
    public function getCustomRfqInfo($rfqId)
    {
        $rfqLoadData = $this->negotiableQuoteFactory->create()->load($rfqId);
        $rfqQuoteData = [
            "quote_id" => $rfqLoadData->getQuoteId(),
            "quote_name" => $rfqLoadData->getQuoteName(),
            "status" => $rfqLoadData->getStatus(),
            "expiration_period" => $rfqLoadData->getExpirationPeriod(),
        ];
        return $rfqQuoteData;
    }

    /**
     * @return array
     */
    public function getStatusLabels()
    {
        // return [
        //     NegotiableQuoteInterface::STATUS_CREATED => __('Submitted'),
        //     NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER => __('Open'),
        //     NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN => __('Pending'),
        //     NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER => __('Submitted'),
        //     NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN => __('Updated'),
        //     NegotiableQuoteInterface::STATUS_ORDERED => __('Ordered'),
        //     NegotiableQuoteInterface::STATUS_EXPIRED => __('Expired'),
        //     NegotiableQuoteInterface::STATUS_DECLINED => __('Declined'),
        //     NegotiableQuoteInterface::STATUS_CLOSED => __('Closed'),
        // ];

        $statusData = [
            "created" => 'Open',
            "processing_by_customer" => 'Open',
            "processing_by_admin"  => 'Open',
            "submitted_by_customer" => 'Open',
            "submitted_by_admin" => 'Quote Received',
            "ordered" => 'Quote Received',
            "expired" => 'Closed',
            "declined" => 'Closed',
            "closed" => 'Closed',
        ];
        return $statusData;
    } 
}
