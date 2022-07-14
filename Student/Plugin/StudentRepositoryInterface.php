<?php
/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Adobe\Student\Plugin;

use Adobe\Student\Model\ResourceModel\Student\CollectionFactory;

/**
 * Class StudentRepositoryInterface
 * @package Adobe\Student\Plugin
 */
class StudentRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * StudentRepositoryInterface constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Adding extension attribute student_address to getDataById()
     * @param \Adobe\Student\Api\StudentRepositoryInterface $subject
     * @param \Adobe\Student\Api\Data\StudentInterface $student
     * @return \Adobe\Student\Api\Data\StudentInterface
     */
    public function afterGetDataById(
        \Adobe\Student\Api\StudentRepositoryInterface $subject,
        \Adobe\Student\Api\Data\StudentInterface $student
    ) {
        if ($student->getExtensionAttributes() && $student->getExtensionAttributes()->getStudentAddress()) {
            return $student;
        }
        $studentAddress = $this->getStudentAddress($student->getStudentId());
        $extensionAttribute = $student->getExtensionAttributes()->setStudentAddress($studentAddress);
        $student->setExtensionAttributes($extensionAttribute);
        return $student;
    }

    /**
     * Get Student Address data
     * @param $studentId
     * @return array|mixed|null
     */
    private function getStudentAddress($studentId)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('student_id', ['eq' => $studentId])
            ->getFirstItem()->getData('student_address');
    }

    /**
     * Adding extension attribute student_address to getList()
     * @param \Adobe\Student\Api\StudentRepositoryInterface $subject
     * @param \Adobe\Student\Api\Data\StudentSearchResultInterface $searchCriteria
     * @return \Adobe\Student\Api\Data\StudentSearchResultInterface
     */
    public function afterGetList(
        \Adobe\Student\Api\StudentRepositoryInterface $subject,
        \Adobe\Student\Api\Data\StudentSearchResultInterface $searchCriteria
    ) : \Adobe\Student\Api\Data\StudentSearchResultInterface {
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
            /** Get Current Extension Attributes from Student */
            $extensionAttributes = $entity->getExtensionAttributes();
            $studentAddress = $this->getStudentAddress($entity->getStudentId());
            $extensionAttributes->setStudentAddress($studentAddress);
            $entity->setExtensionAttributes($extensionAttributes);
            $products[] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }
}
