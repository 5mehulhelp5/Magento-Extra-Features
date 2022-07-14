<?php
/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Adobe\Student\Api;

use Adobe\Student\Api\Data\StudentInterface;

/**
 * Interface StudentRepositoryInterface
 * @package Adobe\Student\Api
 */
interface StudentRepositoryInterface
{
    /**
     * Get Student data by id
     * @param int $studentId
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function getDataById(int $studentId);

    /**
     * @param \Adobe\Student\Api\Data\StudentInterface $student
     * @return boolean
     */
    public function delete(StudentInterface $student);

    /**
     * Delete Student by id
     * @param int $studentId
     * @return boolean
     */
    public function deleteById($studentId);

    /**
     * @param \Adobe\Student\Api\Data\StudentInterface $student
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function update(\Adobe\Student\Api\Data\StudentInterface $student);

    /**
     * Get Student list
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Adobe\Student\Api\Data\StudentSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Updates the specified products in item array.
     *
     * @param mixed $students
     * @return boolean
     * @api
     */
    public function saveStudents($students);

    /**
     * @return \Adobe\Student\Model\Student
     */
    public function create();

    /**
     * @param $value
     * @param null $field
     * @return \Adobe\Student\Model\Student
     */
    public function load($value, $field = null);
}
