<?php


namespace Prajwal\Assignment4\Plugin;

use Magento\Customer\Model\EmailNotification as Subject;

class EmailNotification
{
    /**
     * Return brand[]\
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @return Subject
     */
    public function aroundNewAccount(Subject $subject, \Closure $proceed)
    {
        return $subject;
    }
}
