<?php

namespace Ktpl\Homedelivery\Plugin\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\IntSetup\Helper\Data;
use Magento\OfflinePayments\Model\Cashondelivery as Cashdelivery;
use Ktpl\Homedelivery\Logger\Logger;

class Cashondelivery
{
    public const MARK_ORDER_AS_FURNITURE_PRICE = 'carriers/mark_furniture/price';
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;
    /**
     * @var Cashdelivery
     */
    protected Cashdelivery $cashondelivery;
    /**
     * @var Data
     */
    protected Data $helperIntSetup;
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * Cashondelivery constructor.
     * @param Cashdelivery $cashondelivery
     * @param Data $helperIntSetup
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     */
    public function __construct(
        Cashdelivery          $cashondelivery,
        Data                  $helperIntSetup,
        CheckoutSession       $checkoutSession,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface  $scopeConfig,
        Logger                $logger
    ) {
        $this->cashondelivery = $cashondelivery;
        $this->helperIntSetup = $helperIntSetup;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @param Cashdelivery $subject
     * @param $result
     * @param CartInterface|null $quote
     * @return bool
     */
    public function afterIsAvailable(Cashdelivery $subject, $result, CartInterface $quote = null)
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $storeId = $this->storeManager->getStore()->getId();
            $isHomesrus = $this->helperIntSetup->isHomesrusStore($storeId, ScopeInterface::SCOPE_STORE);
            //for homesrus UAE store only
            if ($isHomesrus) {
                if (!$this->isCODAllowed($quote)) {
                    return false;
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug("restricting cash on delivery error".$e->getMessage());
        }
        return $result;
    }

    /**
     * @param $quote
     * @param $storeId
     * @return bool
     */
    public function isCODAllowed($quote)
    {
        // if Order total is less than Maximum amount Cash on delivery is enabled
        $maximumAmount = (float)$this->getMaximumConfigAmount(self::MARK_ORDER_AS_FURNITURE_PRICE, $quote->getStoreId());
        $grandTotal = $quote->getGrandTotal();
        if ($grandTotal > $maximumAmount) {
            return false;
        }
        return true;
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
