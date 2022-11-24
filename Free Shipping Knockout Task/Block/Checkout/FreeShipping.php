<?php

namespace Codilar\Sales\Block\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;

class FreeShipping extends Template
{
    const FREE_SHIPPING_AMOUNT_CODE = 'carriers/freeshipping/free_shipping_subtotal';
    const FREE_SHIPPING_ENABLE = 'carriers/freeshipping/active';

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var Context
     */
    public $context;
    /**
     * @var CurrencyInterface
     */
    public $currency;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param CurrencyInterface $currency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        CurrencyInterface $currency,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->context = $context;
    }
    public function getFreeShippingMinimumAmount()
    {
        $enable = intval($this->scopeConfig->getValue(self::FREE_SHIPPING_ENABLE));
        if($enable){
            return $this->scopeConfig->getValue(self::FREE_SHIPPING_AMOUNT_CODE);
        }
        else {
            return 0;
        }
    }
    public function getCurrency()
    {
        $currencycode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        return $this->currency->getCurrency($currencycode)->getSymbol();
    }
}
