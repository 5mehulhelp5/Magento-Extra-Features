<?php


namespace Task\Employee\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Task\Employee\Api\Data\EmployeeAddressExtensionInterface;

interface EmployeeAddressInterface extends ExtensibleDataInterface
{
    public const ENTITY_ID = 'entity_id';
    public const ADDRESS_ID  = 'address_id';
    public const PERMANENT_ADDRESS = 'permanent_address';
    public const TEMP_ADDRESS = 'temporary_address';
    public const CREATED_AT = 'created_at';

    /**
     * Return Entity Id
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Sets Entity Id
     *
     * @param int $entityId
     * @return EmployeeAddressInterface
     */
    public function setEntityId($entityId): EmployeeAddressInterface;

    /**
     * Return Address Id
     *
     * @return int
     */
    public function getAddressId(): int;

    /**
     * Sets Address Id
     *
     * @param int $addressId
     * @return EmployeeAddressInterface
     */
    public function setAddressId($addressId): EmployeeAddressInterface;

    /**
     * Return Permanent Address
     *
     * @return string
     */
    public function getPermanentAddress(): string;

    /**
     * Sets Permanent Address
     *
     * @param String $address
     * @return EmployeeAddressInterface
     */
    public function setPermanentAddress($address): EmployeeAddressInterface;

    /**
     * Return Temporary Address
     *
     * @return string
     */
    public function getTemporaryAddress(): string;

    /**
     * Sets the Temporary Address
     *
     * @param string $tempaddress
     * @return EmployeeAddressInterface
     */
    public function setTemporaryAddress($tempaddress): EmployeeAddressInterface;

    /**
     * Return the Created Date
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Sets the Created Date
     *
     * @param string $date
     * @return EmployeeAddressInterface
     */
    public function setCreatedAt($date): EmployeeAddressInterface;
    /**
     * Return the Extension attribute
     *
     * @return \Task\Employee\Api\Data\EmployeeAddressExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Sets the extension attribute
     *
     * @param \Task\Employee\Api\Data\EmployeeAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(EmployeeAddressExtensionInterface $extensionAttributes);
}
