<?php
/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Adobe\Student\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StudentInterface
 * @package Adobe\Student\Api\Data
 */
interface StudentInterface extends ExtensibleDataInterface
{
    const STUDENT_ID = 'student_id';
    const NAME  = 'name';
    const EMAIL = 'email';
    const PHONE = 'phone';
    const CITY  = 'student_city';
    const MARKS = 'student_marks';

    /**
     * @return int
     */
    public function getStudentId(): int;

    /**
     * @param int $studentId
     * @return $this
     */
    public function setStudentId(int $studentId): StudentInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): StudentInterface;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): StudentInterface;

    /**
     * @return string
     */
    public function getPhone(): string;

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): StudentInterface;

    /**
     * @return string
     */
    public function getStudentCity(): string;

    /**
     * @param string $phone
     * @return $this
     */
    public function setStudentCity(string $phone): StudentInterface;

    /**
     * @return string
     */
    public function getStudentMarks(): string;

    /**
     * @param string $phone
     * @return $this
     */
    public function setStudentMarks(string $phone): StudentInterface;

    /**
     * @return \Adobe\Student\Api\Data\StudentExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Adobe\Student\Api\Data\StudentExtensionInterface $extensionAttribute
     * @return mixed
     */
    public function setExtensionAttributes(\Adobe\Student\Api\Data\StudentExtensionInterface $extensionAttribute);
}
