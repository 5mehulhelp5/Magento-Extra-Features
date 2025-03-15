<?php
/**
 * Candere Software
 *
 * @category Codilar
 * @package  CustomReports
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace Codilar\CustomReports\Block\Adminhtml\Usage;

use Magento\Framework\App\RequestInterface;

class Survey extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var RequestInterface
     */
    protected $request;
   
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->_resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->request = $request;
    }
    
    /**
     * Get survey questions
     *
     * @return array
     */
    public function getSurveyquestions()
    {
        $connection = $this->_resource->getConnection();
        $result = [];
        if (!empty($this->request->getParams())) {
            $requestParams = $this->request->getParams();
            if (isset($requestParams['from']) && $requestParams['from']!=""
                && isset($requestParams['to']) && $requestParams['to']!=""
            ) {
                $date_from = strtotime($requestParams['from']);
                $date_to = strtotime($requestParams['to']);
                $mysql_date_from = date('Y-m-d', $date_from).' 00:00:00';
                $mysql_date_to = date('Y-m-d', $date_to).' 23:56:00';
                $conditions = " AND `created_date` BETWEEN '".$mysql_date_from."' AND '".$mysql_date_to."' ";
                // phpcs:ignore
                $survey_answers_report = " SELECT user_id,survey_answers,month,year,primary_flag,created_date FROM `customer_survey_entries` WHERE `primary_flag` = 0 AND `created_date` >= '2021-04-26 13:00:00' ".$conditions."  GROUP BY user_id ORDER BY `created_date` DESC  ";
                $result = $connection->fetchAll($survey_answers_report);
            }
        } else {
            // phpcs:ignore
            $survey_answers_report = " SELECT user_id,survey_answers,month,year,primary_flag,created_date FROM `customer_survey_entries` WHERE `primary_flag` = 0 AND `created_date` >= '2021-04-26 13:00:00' GROUP BY user_id ORDER BY `created_date` DESC";
            $result = $connection->fetchAll($survey_answers_report);

        }

        if ($result != false) {
            $data['result_display'] = $result;
        } else {
            $data['result_display'] = null;
        }

        $result_display = $data['result_display'];
        return $result_display;
    }

    /**
     * Get Primary Answers Query
     *
     * @param int $customerId
     * @return void
     */
    public function getprimaryAnswersQuery($customerId)
    {
        $connection = $this->_resource->getConnection();
        // phpcs:ignore
        $primaryAnswers = "SELECT `primary_answers` FROM `customer_survey_entries` WHERE `user_id` = '".$customerId."' AND `primary_flag` = 1 ";
        $result1 = $connection->fetchRow($primaryAnswers);
        return$result1;
    }

    /**
     * Get Customer Data
     *
     * @param int $customerId
     * @return \Magento\Customer\Model\Customer
     */
    public function getcustomerData($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        return $customer;
    }
}
