<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\ExtendedCheckout\Plugin;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

class GuestPaymentInfoManagement
{
    /**
     * Throws Exception if Address is Po Box Number
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     * @throws \Zend_Log_Exception
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $streetAddress = $billingAddress->getStreet();
        $matches  = preg_grep ('/^P(ost)?\.?\s*O(ffice)?\.?\s*B(ox)?/i', $streetAddress);
        if ($matches) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('PO Box addresses are not accepted.')
            );
        }
    }
}
