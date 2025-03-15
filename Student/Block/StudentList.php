<?php
/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Adobe\Student\Block;

use Magento\Framework\View\Element\Template;
use Adobe\Student\Api\StudentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class StudentList
 * @package Adobe\Student\Block
 */
class StudentList extends Template
{
    /**
     * @var StudentRepositoryInterface
     */
    private StudentRepositoryInterface $studentRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * StudentList constructor.
     * @param Template\Context $context
     * @param StudentRepositoryInterface $studentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StudentRepositoryInterface $studentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->studentRepository = $studentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * returns array of students from bangalore whose marks > 75
     * @return array
     */
    public function getText(): array
    {
        $studentArray = [];
        $this->searchCriteriaBuilder->addFilter(
            'student_city',
            'Bangalore',
        )->addFilter(
            'student_marks',
            '75',
            'gt'
        );

        $students = $this->studentRepository->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        if ($students) {
            foreach ($students as $student) {
                $studentArray[] = [
                'name' => $student->getName(),
                'student_city' => $student->getStudentCity(),
                'student_marks' => $student->getStudentMarks()
                ];
            }
        }
        return $studentArray;
    }
}
