<?php

namespace Prajwal\Assignment6\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;

class CustomSection implements SectionSourceInterface
{
    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * CustomSection constructor.
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession=$customerSession;
    }

    /**
     * Return the Customer details
     *
     * @return array|string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSectionData()
    {
        return [
            'customerdetail' =>$this->customerSession->getCustomer()->getName(),
        ];
    }
}
