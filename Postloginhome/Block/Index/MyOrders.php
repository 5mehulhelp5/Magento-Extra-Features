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
use Magento\Framework\Pricing\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MyOrders extends Template
{
    /**
     * @var UserContextInterface
     */
    protected UserContextInterface $userContext;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Data
     */
    private Data $priceHelper;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param StoreManagerInterface $storeManager
     * @param Data $priceHelper
     * @param CollectionFactory $orderCollectionFactory
     * @param array $data [optional]
     */

    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        StoreManagerInterface $storeManager,
        Data $priceHelper,
        CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->userContext = $userContext;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
     * Get formatted price
     *
     * @return string
     */
    public function getFormattedPrice($price): string
    {
        return $this->priceHelper->currency($price, true, false);
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
     * Get order Collection
     *
     * @return array
     */
    public function getCustomerOrders(): array
    {
        $customerId = $this->getCustomerId();
        $result = array();
        if($customerId){
            $customerOrder = $this->orderCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);
            foreach ($customerOrder->getData() as $item) {
                $result[] = [
                    'items' => [
                        'order_id' => $item['entity_id'],
                        'increment_id' => $item['increment_id'],
                        'status' => $item['status'],
                        'created_at' => $item['created_at'],
                        'grand_total' => $item['grand_total'],
                        'total_qty_ordered' => $item['total_qty_ordered']
                    ]
                ];
            }
        }
        return $result;
    }
}
