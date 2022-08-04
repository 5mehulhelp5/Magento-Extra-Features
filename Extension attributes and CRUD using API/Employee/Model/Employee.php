<?php

namespace Task\Employee\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\AbstractExtensibleModel;
use Task\Employee\Api\Data\EmployeeExtensionInterface;
use Task\Employee\Model\ResourceModel\Employee as ResourceModel;
use Task\Employee\Api\Data\EmployeeInterface;

class Employee extends AbstractExtensibleModel implements EmployeeInterface
{
    /**
     * Employee constructor.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Return the Employee Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Sets the ID
     *
     * @param int $Id
     * @return EmployeeInterface
     */
    public function setId($Id): EmployeeInterface
    {
        return $this->setData(self::ID, $Id);
    }

    /**
     * Return the status
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Sets the Status
     *
     * @param bool $value
     * @return EmployeeInterface
     */
    public function setIsActive(bool $value): EmployeeInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * Sets the First Name
     *
     * @return EmployeeInterface
     */
    public function getFirstName(): string
    {
        return $this->getData(self::FIRST_NAME);
    }

    /**
     * Sets the First Name
     *
     * @param string $firstname
     * @return EmployeeInterface
     */
    public function setFirstName($firstname): EmployeeInterface
    {
        return $this->setData(self::FIRST_NAME, $firstname);
    }

    /**
     * Return the Last Name
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->getData(self::LAST_NAME);
    }

    /**
     * Sets the Last Name
     *
     * @param string $lastname
     * @return EmployeeInterface
     */
    public function setLastName($lastname): EmployeeInterface
    {
        return $this->setData(self::LAST_NAME, $lastname);
    }

    /**
     * Return the Date of Birth
     *
     * @return string
     */
    public function getDob(): string
    {
        return $this->getData(self::DOB);
    }

    /**
     * Sets the Date of Birth
     *
     * @param string $dob
     * @return EmployeeInterface
     */
    public function setDob($dob): EmployeeInterface
    {
        return $this->setData(self::DOB, $dob);
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
     * @return EmployeeInterface
     */
    public function setCreatedAt($date): EmployeeInterface
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * Return The price
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Sets the Price
     *
     * @param float $price
     * @return EmployeeInterface
     */
    public function setPrice($price): EmployeeInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Return the Weight
     *
     * @return float
     */
    public function getWeight(): float
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * Sets the Weight
     *
     * @param float $weight
     * @return EmployeeInterface
     */
    public function setWeight($weight): EmployeeInterface
    {
        return $this->setData(self::WEIGHT, $weight);
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
    public function setExtensionAttributes(EmployeeExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
