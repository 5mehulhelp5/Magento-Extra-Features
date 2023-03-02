<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Block\Form;

use Codilar\RFQExtended\Helper\Data;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Authorization\Model\UserContextInterface;

class Create extends Template
{
    const FORM_SUBMIT_URL = 'quick-quote/quote/save';

    const CUSTOMER_LOGIN_URL = 'customer/account/login';

    const PASSWORD_POPUP_MESSAGE = 'codilar_guest_checkout/configs/guest_checkout_message';

    /**
     * @var UserContextInterface
     */
    protected UserContextInterface $userContext;

    /**
     * @var Attribute
     */
    protected Attribute $attributes;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param Attribute $attributes
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        Attribute $attributes,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->userContext = $userContext;
        $this->attributes = $attributes;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
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
     * @return int|null
     */
    public function getCustomerId() {
        if($this->isCustomerLoggedIn()) {
            return $this->userContext->getUserId();
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl(self::FORM_SUBMIT_URL, ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getCustomerLoginUrl()
    {
        return $this->getUrl(self::CUSTOMER_LOGIN_URL, ['_secure' => true]);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getManufactureOptions(): array
    {
        return $this->helperData->getManufactureOptionsArray();
    }

    /**
     * For getting password popup text
     *
     * @return mixed
     */
    public function getPasswordPopupText()
    {
        return $this->scopeConfig->getValue(self::PASSWORD_POPUP_MESSAGE);
    }
}
