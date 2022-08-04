<?php

namespace Prajwal\Assignment8\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Locale\CurrencyInterface;

class EmiData implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeconfig;
    /**
     * @var Context
     */
    protected Context $httpcontext;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;
    /**
     * @var Currency
     */
    protected Currency $currencySymbol;
    /**
     * @var CurrencyInterface
     */
    protected CurrencyInterface $localecurrency;

    /**
     * EmiData constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $httpcontext
     * @param StoreManagerInterface $storeManager
     * @param Currency $currencySymbol
     * @param CurrencyInterface $localeCurrency
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $httpcontext,
        StoreManagerInterface $storeManager,
        Currency $currencySymbol,
        CurrencyInterface $localeCurrency
    ) {
        $this->scopeconfig=$scopeConfig;
        $this->httpcontext=$httpcontext;
        $this->storeManager = $storeManager;
        $this->currencySymbol = $currencySymbol;
        $this->localecurrency = $localeCurrency;
    }

    /**
     * Return Emi Data
     *
     * @return string
     */
    public function getEmidata()
    {
        $data = $this->scopeconfig->getValue('emi/general/config_table', ScopeInterface::SCOPE_STORE);
        $data = (array)json_decode($data, true);
        $data = array_values($data);
        $data = json_encode($data);
        return $data;
    }

    /**
     * Return Customer login or not
     *
     * @return bool
     */
    public function isCustomerloggedin()
    {
        return (bool)$this->httpcontext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Return Currency
     *
     * @return string
     */
    public function getCurrency()
    {
        $currencycode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        return $this->localecurrency->getCurrency($currencycode)->getSymbol();
    }
}
