<?php

namespace Casio\LotterySale\Model\SalesType\Validator;

use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\InputException;

class Address
{
    /**
     * @var AddressFactory
     */
    protected AddressFactory $addressFactory;

    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    /**
     * Address constructor.
     * @param AddressFactory $addressFactory
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(
        AddressFactory $addressFactory,
        CustomerRegistry $customerRegistry
    ) {
        $this->addressFactory = $addressFactory;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @throws InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $customerModel = $this->customerRegistry->retrieve($address->getCustomerId());
        $addressModel = $this->addressFactory->create();
        $addressModel->updateData($address);
        $addressModel->setCustomer($customerModel);
        $addressModel->setStoreId($customerModel->getStoreId());

        $errors = $addressModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
    }
}
