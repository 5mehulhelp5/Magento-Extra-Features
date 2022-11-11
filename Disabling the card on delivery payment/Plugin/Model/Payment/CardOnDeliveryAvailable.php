<?php
namespace Codilar\ExtendedSales\Plugin\Model\Payment;

use Codilar\ExtendedSales\Model\Payment\CardOnDelivery;
use Ktpl\ExtendedSales\Model\Product\Attribute\Source\VendorShippingType;
use Ktpl\ExtendedSales\Setup\Patch\Data\AddOrderStatus;
use Ktpl\IntSetup\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CardOnDeliveryAvailable
{
    const ENABLE_CARD_ON_DELIVERY_FOR_ALL = "payment/enable_cod/enable_all";
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var Data
     */
    protected $helperIntSetup;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CheckoutSession $checkoutSession
     * @param Data $helperIntSetup
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CheckoutSession       $checkoutSession,
        Data                  $helperIntSetup,
        LoggerInterface       $logger,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperIntSetup = $helperIntSetup;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->scopeConfig=$scopeConfig;
    }

    /**
     *
     * @param CardOnDelivery $subject
     * @param $result
     * @param CartInterface|null $quote
     * @return bool
     */
    public function afterIsAvailable(CardOnDelivery $subject, $result, CartInterface $quote = null)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $isHomesrusQatar = $this->helperIntSetup->isHomesrusQatarStore($storeId, ScopeInterface::SCOPE_STORE);
            //for homesrus qatar store only
            if ($isHomesrusQatar) {
                if (!$this->isCODAllowed($quote, $storeId)) {
                    return false;
                }
            }

            if (!$this->isGiftCard()) {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->debug("restricting cod error".$e->getMessage());
        }
        return $result;
    }

    /**
     * @param CardOnDelivery $subject
     * @param $result
     * @param $field
     * @param $storeId
     * @return mixed|string
     * @throws LocalizedException
     */
    public function afterGetConfigData(CardOnDelivery $subject, $result, $field, $storeId = null)
    {
        if ($field === "order_status") {
             $order = $subject->getInfoInstance()->getOrder();
            if ($order->getCustomerId() && $this->helperIntSetup->customerHasMobileNumber($order->getCustomerId())) {
                $result = AddOrderStatus::NEW_ORDER_STATUS_FOR_NEW_STATE_CODE;
            }
        }
        return $result;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function isGiftCard()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item[] $quoteItems */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getProduct()->getTypeId() === "giftcard") {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $quote
     * @param $storeId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function isCODAllowed($quote, $storeId)
    {
        if (!$quote) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->checkoutSession->getQuote();
        }

        /** @var \Magento\Quote\Model\Quote\Item[] $quoteItems */
        $quoteItems = $quote->getAllItems();
	// if Order total is less than Maximum amount Card on delivery is enabled
        $maximumAmount = (float)$this->getMaximumConfigAmount('payment/cardondelivery/maximum_amount', $quote->getStoreId());
        $grandTotal =  $quote->getGrandTotal();
        if ($grandTotal > $maximumAmount) {
            return false;
        }
        //if enabled it will allow all products to have cod as payment method
        $enabled_cod_all =$this->getConfig(self::ENABLE_CARD_ON_DELIVERY_FOR_ALL, $storeId);
        if (!$enabled_cod_all) {
            foreach ($quoteItems as $quoteItem) {
                if (!$quoteItem->getProduct()->getIsAccessories()) {
                    return false;
                }
            }
        }
        if (stristr($quote->getShippingAddress()->getShippingMethod(), 'clickncollect_')) {
            return false;
        }

        return true;
    }

    /**
     * @param $path
     * @param $storeId
     * @return bool
     */
    public function getConfig($path, $storeId): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    public function getMaximumConfigAmount($path, $storeId) :mixed
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
