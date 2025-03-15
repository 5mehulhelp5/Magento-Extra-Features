<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\ExtendedCheckout\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;

class PaymentInfoManagement
{

    /**
     * Throws Exception if  Address is Po Box Number
     *
     * @param PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $streetAddress= $billingAddress->getStreet();
        $matches  = preg_grep ('/^P(ost)?\.?\s*O(ffice)?\.?\s*B(ox)?/i', $streetAddress);
        if ($matches) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('PO Box addresses are not accepted.')
            );
        }
    }
}
