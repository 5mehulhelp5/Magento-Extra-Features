<?php
/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Adobe\Student\Model;

use Adobe\Student\Api\Data\StudentInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Adobe\Student\Model\ResourceModel\Student as ResourceModel;

/**
 * Class Student
 * @package Adobe\Student\Model
 */
class Student extends AbstractExtensibleModel implements StudentInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inerhitDoc
     * @return int
     */
    public function getStudentId(): int
    {
        return $this->getData(self::STUDENT_ID);
    }

    /**
     * @inerhitDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param int $studentId
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function setStudentId(int $studentId): StudentInterface
    {
        return $this->setData(self::STUDENT_ID, $studentId);
    }

    /**
     * @param string $name
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function setName(string $name): StudentInterface
    {
        return $this->setData(self::NAME, $name);
    }
    /**
     * @inerhitDoc
     */
    public function getEmail(): string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param string $email
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function setEmail(string $email): StudentInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inerhitDoc
     */
    public function getPhone(): string
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @param string $phone
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function setPhone(string $phone): StudentInterface
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * @inerhitDoc
     */
    public function getStudentCity(): string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @param string $city
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function setStudentCity(string $city): StudentInterface
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inerhitDoc
     */
    public function getStudentMarks(): string
    {
        return $this->getData(self::MARKS);
    }

    /**
     * @param string $marks
     * @return StudentInterface
     */
    public function setStudentMarks(string $marks): StudentInterface
    {
        return $this->setData(self::MARKS, $marks);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inerhitDoc
     */
    public function setExtensionAttributes(\Adobe\Student\Api\Data\StudentExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
