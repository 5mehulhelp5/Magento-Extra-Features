<?php
/**
 * Brainvire Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brainvire.com license that is
 * available through the world-wide-web at this URL:
 * https://www.brainvire.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Brainvire
 * @package     Brainvire_PriceDecimal
 * @copyright   Copyright (c) 2019-2020 Brainvire Co., Ltd. All rights reserved. (http://www.brainvire.com/)
 * @license     https://www.brainvire.com/LICENSE.txt
 */

namespace Brainvire\PriceDecimal\Helper;

/**
 * This class helps us to get the value from the configuration
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_BV_PRICEDECIMAL_ENABLE = 'bv_price_decimal/general/enable';
    const XML_PATH_BV_PRICEDECIMAL_SHOW_ALL = 'bv_price_decimal/general/show_all';
    const XML_PATH_BV_PRICEDECIMAL_SHOW_CART = 'bv_price_decimal/general/show_cart';
    const XML_PATH_BV_PRICEDECIMAL_SHOW_CHECKOUT = 'bv_price_decimal/general/show_checkout';
    const XML_PATH_BV_PRICEDECIMAL_DECIMAL_LENGTH = 'bv_price_decimal/general/decimal_length';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        parent::__construct($context);
    }


    /**
     * Retrieve the enable
     *
     * @return boolean
     */
    public function isAllowedPage()
    {

        $moduleName = $this->request->getModuleName();
        $route      = $this->request->getRouteName();
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();

        if($this->_isEnableForAll()) {
            return true;
        }

        if($this->_isEnableForCart()) {
            if($route == 'checkout'  && $controller == 'cart' && $action == 'index') {
                return true;
            }
        }

        if($this->_isEnableForCheckout()) {
            if($route  == 'checkout'  && $controller  == 'index' && $action  == 'index') {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve the enable
     *
     * @return boolean
     */
    public function _isModuleEnable()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BV_PRICEDECIMAL_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Retrieve the enable
     *
     * @return boolean
     */
    protected function _isEnableForAll()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BV_PRICEDECIMAL_SHOW_ALL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the enable
     *
     * @return boolean
     */
    protected function _isEnableForCart()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BV_PRICEDECIMAL_SHOW_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the enable
     *
     * @return boolean
     */
    protected function _isEnableForCheckout()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BV_PRICEDECIMAL_SHOW_CHECKOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the decimal length
     *
     * @return int
     */
    public function getDecimalLength()
    {
        return intval($this->scopeConfig->getValue(
            self::XML_PATH_BV_PRICEDECIMAL_DECIMAL_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }
}
