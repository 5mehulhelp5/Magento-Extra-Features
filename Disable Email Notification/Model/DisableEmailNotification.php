<?php


namespace Prajwal\Assignment4\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;

class DisableEmailNotification extends EmailNotification
{
    /**
     * Disable email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string $sendemailStoreId
     * @return void
     */
    public function newAccount(
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ): void {
    }
}
