<?php
/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Adobe\Student\Model;

use Adobe\Student\Api\Data\StudentInterface;
use Adobe\Student\Api\StudentRepositoryInterface;
use Adobe\Student\Model\ResourceModel\Student;
use Adobe\Student\Model\ResourceModel\Student\CollectionFactory;
use Braintree\Exception\NotFound;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Adobe\Student\Api\Data\StudentSearchResultInterfaceFactory;

/**
 * Class StudentRepository
 * @package Adobe\Student\Model
 */
class StudentRepository implements StudentRepositoryInterface
{
    /**
     * @var StudentFactory
     */
    private StudentFactory $studentFactory;
    /**
     * @var Student
     */
    private Student $studentResource;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;
    /**
     * @var StudentSearchResultInterfaceFactory
     */
    private StudentSearchResultInterfaceFactory $SearchResultFactory;
    /**
     * @var StudentInterface
     */
    private StudentInterface $studentInterface;

    /**
     * @param StudentFactory $studentFactory
     * @param Student $studentResource
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StudentSearchResultInterfaceFactory $SearchResultFactory
     * @param StudentInterface $studentInterface
     */
    public function __construct(
        StudentFactory $studentFactory,
        Student $studentResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        StudentSearchResultInterfaceFactory $SearchResultFactory,
        StudentInterface $studentInterface
    ) {
        $this->studentFactory = $studentFactory;
        $this->studentResource = $studentResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->SearchResultFactory = $SearchResultFactory;
        $this->studentInterface = $studentInterface;
    }

    /**
     * Get Student data by id
     * @param int $studentId
     * @return \Adobe\Student\Model\Student
     */
    public function getDataById(int $studentId)
    {
        $student =$this->studentFactory->create();
        $this->studentResource->load($student, $studentId);
        return $student;
    }

    /**
     * @param StudentInterface $student
     * @return boolean
     */
    public function delete(StudentInterface $student)
    {
        try {
            return $this->deleteById($student->getStudentId());
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param int $studentId
     * @return boolean
     */
    public function deleteById($studentId)
    {
        $collection = $this->collectionFactory->create();
        try {
            $student = $collection->getItemById($studentId);
            if ($student != null) {
                $collection->getResource()->delete($student);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new NotFound('Data not Present');
        }
        return true;
    }

    /**
     * save/update student data
     * @param StudentInterface $student
     * @return StudentInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function update(StudentInterface $student): StudentInterface
    {
        $student->setStudentAddress($student->getExtensionAttributes()->getStudentAddress());
        $this->studentResource->save($student);
        return $student;
    }

    /**
     * Get Student list
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Adobe\Student\Api\Data\StudentSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, ($collection));
        $searchResults = $this->SearchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setSearchCriteria($searchCriteria);
        return $searchResults;
    }

    /**
     * Save Multiple Students data from request payload.
     * @param mixed $students
     * @return boolean
     * @api
     */
    public function saveStudents($students)
    {
        if (!empty($students)) {
            foreach ($students as $std) {
                $student = $this->studentFactory->create();
                $student->setName($std['name']);
                $student->setPhone($std['phone']);
                $student->setEmail($std['email']);
                $student->setStudentCity($std['student_city']);
                $student->setStudentMarks($std['student_marks']);
                $student->setStudentAddress($std['extension_attributes']['student_address']);
                $this->studentResource->save($student);
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        return $this->studentFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function load($value, $field = null)
    {
        $model = $this->create();
        $this->studentResource->load($model, $value, $field);
        return $model;
    }
}
