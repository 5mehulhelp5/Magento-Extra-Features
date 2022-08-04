<?php


namespace Task\Employee\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface EmployeeInterface extends ExtensibleDataInterface
{
    public const ID = 'id';
    public const IS_ACTIVE  = 'is_active';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const DOB  = 'dob';
    public const CREATED_AT = 'created_at';
    public const PRICE= 'price';
    public const WEIGHT= 'weight';

    /**
     * Return the Employee Id
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the ID
     *
     * @param int $Id
     * @return EmployeeInterface
     */
    public function setId($Id): EmployeeInterface;

    /**
     * Return the status
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Sets the Status
     *
     * @param bool $value
     * @return EmployeeInterface
     */
    public function setIsActive(bool $value):EmployeeInterface;

    /**
     * Return the First Name
     *
     * @return string
     */
    public function getFirstName(): string;

    /**
     * Sets the First Name
     *
     * @param string $firstname
     * @return EmployeeInterface
     */
    public function setFirstName($firstname): EmployeeInterface;

    /**
     * Return the Last Name
     *
     * @return string
     */
    public function getLastName(): string;

    /**
     * Sets the Last Name
     *
     * @param string $lastname
     * @return EmployeeInterface
     */
    public function setLastName($lastname): EmployeeInterface;

    /**
     * Return the Date of Birth
     *
     * @return string
     */
    public function getDob(): string;

    /**
     * Sets the Date of Birth
     *
     * @param string $dob
     * @return EmployeeInterface
     */
    public function setDob($dob): EmployeeInterface;

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
     * @return EmployeeInterface
     */
    public function setCreatedAt($date): EmployeeInterface;

    /**
     * Return The price
     *
     * @return float
     */
    public function getPrice(): float;

    /**
     * Sets the Price
     *
     * @param float $price
     * @return EmployeeInterface
     */
    public function setPrice($price): EmployeeInterface;

    /**
     * Return the Weight
     *
     * @return float
     */
    public function getWeight(): float;

    /**
     * Sets the Weight
     *
     * @param float $weight
     * @return EmployeeInterface
     */
    public function setWeight($weight): EmployeeInterface;

    /**
     * Return The Extension Attributes
     *
     * @return \Task\Employee\Api\Data\EmployeeExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Sets Extension Attributes
     *
     * @param EmployeeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Task\Employee\Api\Data\EmployeeExtensionInterface $extensionAttributes);
}
