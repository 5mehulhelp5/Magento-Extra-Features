<?php

/**
 * Copyright © Lehan, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Adobe\Student\Controller\Adminhtml\Student;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Adobe\Student\Api\StudentRepositoryInterface;

/**
 * Class Save
 * @package Adobe\Student\Controller\Adminhtml\Student
 */
class Save extends Action
{
    /**
     * @var StudentRepositoryInterface
     */
    private StudentRepositoryInterface $studentRepository;


    /**
     * Save constructor.
     * @param Context $context
     * @param StudentRepositoryInterface $studentRepository
     */
    public function __construct(
        Context $context,
        StudentRepositoryInterface $studentRepository
    ) {
        parent::__construct($context);
        $this->studentRepository = $studentRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $post = $this->getRequest()->getParams();

            if (isset($post['student_id'])) {
                $student = $this->studentRepository->load($post['student_id']);
            } else {
                $student = $this->studentRepository->create();
            }
            $student->setName($post['name']);
            $student->setEmail($post['email']);
            $student->setPhone($post['phone']);
            $student->setStudentMarks($post['student_marks']);
            $student->setStudentCity($post['student_city']);
            $student->getExtensionAttributes()->setStudentAddress($post['student_address']);
            $this->studentRepository->update($student);
            $this->messageManager->addSuccessMessage('Student Updated Successfully!!!');
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage('There is an error wile saving student!!');
        }
        $this->_redirect('student/student/index');
    }
}
