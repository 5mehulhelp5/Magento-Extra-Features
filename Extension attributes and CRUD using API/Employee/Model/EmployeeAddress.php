<?php

namespace Task\Employee\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Task\Employee\Model\ResourceModel\EmployeeAddress as ResourceModel;
use Task\Employee\Api\Data\EmployeeAddressInterface;
use Task\Employee\Api\Data\EmployeeAddressExtensionInterface;

class EmployeeAddress extends AbstractExtensibleModel implements EmployeeAddressInterface
{
    /**
     * EmployeeAddress constructor.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Return Entity Id
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Sets Entity Id
     *
     * @param int $entityId
     * @return EmployeeAddressInterface
     */
    public function setEntityId($entityId): EmployeeAddressInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Return Address Id
     *
     * @return int
     */
    public function getAddressId(): int
    {
        return $this->getData(self::ADDRESS_ID);
    }

    /**
     * Sets Address Id
     *
     * @param int $addressId
     * @return EmployeeAddressInterface
     */
    public function setAddressId($addressId): EmployeeAddressInterface
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * Return Permanent Address
     *
     * @return string
     */
    public function getPermanentAddress(): string
    {
        return $this->getData(self::PERMANENT_ADDRESS);
    }

    /**
     * Sets Permanent Address
     *
     * @param String $address
     * @return EmployeeAddressInterface
     */
    public function setPermanentAddress($address): EmployeeAddressInterface
    {
        return $this->setData(self::PERMANENT_ADDRESS, $address);
    }

    /**
     * Return Temporary Address
     *
     * @return string
     */
    public function getTemporaryAddress(): string
    {
        return $this->getData(self::TEMP_ADDRESS);
    }

    /**
     * Sets the Temporary Address
     *
     * @param string $tempaddress
     * @return EmployeeAddressInterface
     */
    public function setTemporaryAddress($tempaddress): EmployeeAddressInterface
    {
        return $this->setData(self::TEMP_ADDRESS, $tempaddress);
    }

    /**
     * Return the Created Date
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Sets the Created Date
     *
     * @param string $date
     * @return EmployeeAddressInterface
     */
    public function setCreatedAt($date): EmployeeAddressInterface
    {
        return $this->setData(self::CREATED_AT, $date);
    }
    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(EmployeeAddressExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
