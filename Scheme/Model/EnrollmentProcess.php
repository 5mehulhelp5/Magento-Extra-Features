<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
// @codingStandardsIgnoreFile

namespace KalyanUs\Scheme\Model;

use KalyanUs\Scheme\Helper\Config;
use KalyanUs\Scheme\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PG\Customerjourney\Model\CustomerjourneyService;

class EnrollmentProcess
{
    public const SCHEME_ENROLLMENT_TABLE = 'kj_scheme_enrollment';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \KalyanUs\Scheme\Helper\Data
     */
    protected $helperDataScheme;
    public const SCHEME_ENROLLMENT_PLAN_NO_PREFIX = 'CSJP';

    public const SCHEME_ENROLLMENT_STATUS_PENDING_CODE = 'pending';
    public const SCHEME_ENROLLMENT_STATUS_ACTIVE_CODE = 'opened';
    public const SCHEME_ENROLLMENT_STATUS_MATURED_CODE = 'matured';
    public const SCHEME_ENROLLMENT_STATUS_PRECLOSED_CODE = 'preclosed';

    public const SCHEME_PAYMENT_STATUS_PENDING_CODE = 'pending';
    public const SCHEME_PAYMENT_STATUS_COMPLETED_CODE = 'completed';

    public const SCHEME_PAYMENT_MODE_CODE = 'cash';
    private EventManager $_eventManager;

    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param DateTime $date
     * @param Config $helperConfigScheme
     * @param CustomerjourneyService $customerjourneyService
     * @param Data $helperDataScheme
     * @param EventManager $_eventManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \PG\Customerjourney\Model\CustomerjourneyService $customerjourneyService,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme,
        EventManager $_eventManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->timezone =  $timezone;
        $this->date=$date;
        $this->helperConfigScheme =$helperConfigScheme;
        $this->helperDataScheme =$helperDataScheme;
        $this->customerjourneyService=$customerjourneyService;
        $this->_eventManager = $_eventManager;
    }

    /**
     * Insert data
     *
     * @param array $data
     * @param string $tablename
     * @return int
     */
    public function insert($data, $tablename)
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::SCHEME_ENROLLMENT_TABLE);
        $connection->insert($tablename, $data);
        return $connection->lastInsertId();
    }

    /**
     * Convert quote to enrollment
     *
     * @param object $quote
     * @return array
     */
    public function convertQuoteToEnrollment($quote)
    {
        $paymentId='';
        $enrollmentId=$this->saveEnrollment($quote);
        if ($enrollmentId!='') {
            $nomieeId=$this->saveNominee($quote, $enrollmentId);
            $paymentId=$this->savePayment($quote, $enrollmentId);
        }
        return ['enrollment_id'=>$enrollmentId,'paymentId'=>$paymentId];
    }

    /**
     * Save enrollment
     *
     * @param object $quote
     * @param integer $isAdminCreated
     * @return mixed
     */
    public function saveEnrollment($quote, $isAdminCreated = 0)
    {
        try {
            $data=[];
            $data['customer_id']=$quote['customer_id'];
            $data['email_id']=$quote['email_id'];
            $data['customer_name']=$quote['customer_name'];
            $data['scheme_mobile_number']=$quote['scheme_mobile_number'];
            $data['is_mobile_verified']=$quote['is_mobile_verified'];
            $data['address']=$quote['address'];
            $data['pincode']=$quote['pincode'];
            $data['state']=$quote['state'];
            $data['city']=$quote['city'];
            $data['duration']=$quote['duration'];
            $data['emi_amount']=$quote['emi_amount'];
            $data['status']=self::SCHEME_ENROLLMENT_STATUS_PENDING_CODE;
            $data['auto_monthly_payment']=isset($quote['auto_monthly_payment']) ? $quote['auto_monthly_payment'] : 0;
            $data['scheme_name']=$quote['scheme_name'];
            $data['benefit_list']=json_encode($this->helperConfigScheme->getBenefitPercentageList($quote['duration']));
            if (!$isAdminCreated) {
                $utmSourceNMedium=$this->customerjourneyService->getLastJourneyCookies();
                $data['utm_source']=isset($utmSourceNMedium['last_source']) ? $utmSourceNMedium['last_source'] : '';
                $data['utm_medium']=isset($utmSourceNMedium['last_medium']) ? $utmSourceNMedium['last_medium'] : '';
                $data['utm_campaign']=isset($utmSourceNMedium['last_campaign']) ? $utmSourceNMedium['last_campaign'] : '';
            }
            $lastInsertId = $this->insert($data, self::SCHEME_ENROLLMENT_TABLE);
            $this->updatePlanNo($lastInsertId, $quote['duration']);
            return $lastInsertId;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param $enrollmentId
     * @param $duration
     * @return string
     */
    private function insertSequencePlan($enrollmentId, $duration)
    {
        $data=[];
        $data['sequence_value']=$enrollmentId;
        $lastInsertId=$this->insert($data, 'kj_scheme_sequence');
        $prefixScheme=$this->helperConfigScheme->getEnrollPrefix($duration);
        return $prefixScheme.''.$lastInsertId;
    }

    /**
     * Save nominee
     *
     * @param object $quote
     * @param int $enrollmentId
     * @return mixed
     */
    public function saveNominee($quote, $enrollmentId)
    {
        if ($enrollmentId!='') {
            if ($quote['nominee_info']!='') {
                $nomineeDetail=json_decode($quote['nominee_info'], true);
                if (count($nomineeDetail) > 0) {
                    if ($nomineeDetail['name']!='' || $nomineeDetail['relationship']!='' || $nomineeDetail['mobilenumber']!='' || $nomineeDetail['nationality']!='') {
                        try {
                            $data=[];
                            $data['enrollment_id']=$enrollmentId;
                            $data['nominee_name']=$nomineeDetail['name'];
                            $data['nominee_relationship']=$nomineeDetail['relationship'];
                            $data['nominee_mobilenumber']=$nomineeDetail['mobilenumber'];
                            $data['nominee_nationality']=$nomineeDetail['nationality'];
                            return $this->insert($data, 'kj_scheme_nominee');
                        } catch (\Exception $e) {
                            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                        }
                    }
                }
            }
        }
        return '';
    }

    /**
     * Save payment
     *
     * @param object $quote
     * @param int $enrollmentId
     * @return mixed
     */
    public function savePayment($quote, $enrollmentId)
    {
        if ($enrollmentId!='') {
            $data=[];
            $data['enrollment_id']=$enrollmentId;
            $data['amount']=$quote['emi_amount'];
            $data['month']=$this->helperDataScheme->getMonth('');
            $data['payment_date']=$this->helperDataScheme->getPaymentDate('');
            $data['payment_status']=$this->getInitialPaymentStatus();
            $paymentMode=isset($quote['payment_mode']) ? $quote['payment_mode'] : '';
            $data['transaction_mode']=$this->getOnlineTramsactionMode($paymentMode);
            $data['payment_mode']=isset($quote['payment_mode']) ? $quote['payment_mode'] : null;
            $data['store_code']=isset($quote['store_code']) ? $quote['store_code'] : null;
            return $this->insert($data, 'kj_scheme_payment_history');
        }
        return '';
    }

    /**
     * Create installment payment
     *
     * @param int $enrollmentId
     * @return mixed
     */
    private function createInstallmentPayment($enrollmentId)
    {
        if ($enrollmentId!='') {
            try {
                $installmentDetail=$this->getNextInstallmentDetail($enrollmentId);
                $enrollmentDetail=$this->getEnrollmentInfoById($enrollmentId);
                if (count($installmentDetail) > 0 && count($enrollmentDetail) > 0) {
                    $data=[];
                    $data['enrollment_id']=$enrollmentId;
                    $data['amount']=$enrollmentDetail['emi_amount'];
                    $data['installment_schedule_id']=isset($installmentDetail['id'])?$installmentDetail['id']:null;
                    $data['month']=$this->helperDataScheme->getMonth($installmentDetail['due_date']);
                    $data['payment_date']=$this->helperDataScheme->getPaymentDate('');
                    $data['payment_status']=$this->getInitialPaymentStatus();
                    $data['transaction_mode']=$this->getOnlineTramsactionMode();
                    $data['payment_mode']=isset($quote['payment_mode']) ? $quote['payment_mode'] : null;
                    $data['store_code']=isset($quote['store_code']) ? $quote['store_code'] : null;
                    return $this->insert($data, 'kj_scheme_payment_history');
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Enrollment information is not found.'));
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
        return '';
    }

    /**
     * Insert installment records
     *
     * @param array $installPayment
     */
    public function insertInstallmentRecords($installPayment)
    {
        if (isset($installPayment['installment_schedule_id']) && !empty($installPayment['installment_schedule_id'])) {
            try {
                $enrollmentDetail=$this->getEnrollmentInfoById($installPayment['enrollmentid']);
                $installmentDetail=$this->getCurrentInstallmentDetail($installPayment['installment_schedule_id']);
                if (count($installmentDetail) > 0 && count($installPayment) > 0) {
                    $paymentDate=$this->helperDataScheme->getPaymentDate('');
                    $data=[];
                    $data['enrollment_id']=$enrollmentDetail['id'];
                    $data['amount']=$enrollmentDetail['emi_amount'];
                    $data['installment_schedule_id']=isset($installmentDetail['id'])?$installmentDetail['id']:null;
                    $data['month']=$this->helperDataScheme->getMonth($installmentDetail['due_date']);
                    $data['payment_date']=$paymentDate;
                    $paymentMode=isset($quote['payment_mode']) ? $quote['payment_mode'] : '';
                    $data['payment_status']=isset($installPayment['payment_status']) ? $installPayment['payment_status'] : $this->getInitialPaymentStatus();
                    $data['transaction_mode']=$this->getOnlineTramsactionMode($paymentMode);
                    $data['payment_mode']=isset($installPayment['payment_mode']) ? $installPayment['payment_mode'] : null;
                    $data['store_code']=isset($installPayment['store_code']) ? $installPayment['store_code'] : null;
                    $lastInsertId=$this->insert($data, 'kj_scheme_payment_history');

                    $this->updateScheduleInstallment($installPayment['installment_schedule_id']);
                    $isDefaulter=$this->getIsDefaulterFlag($paymentDate, $installmentDetail['due_date']);
                    $this->changeEnrollmentStatus($enrollmentDetail['id'], $isDefaulter);
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Installment Information is not found.'));
        }
    }

    /**
     * Save installment payment
     *
     * @param int $enrollmentId
     * @return mixed
     */
    public function saveInstallmentPayment($enrollmentId)
    {
        return $this->createInstallmentPayment($enrollmentId);
    }

    /**
     * Get payment info
     *
     * @param int $id
     * @return array
     */
    public function getPaymentInfoById($id)
    {
        if ($id!='') {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()->from(
                ['sph' => 'kj_scheme_payment_history'],
                ['*']
            )->where(
                "sph.id = ?",
                $id
            );
            $records = $connection->fetchRow($select);
            if (is_array($records)) {
                if (count($records)>0) {
                    return $records;
                }
            }
        }
        return [];
    }

    /**
     * Get enrollment infor
     *
     * @param int $id
     * @return array
     */
    public function getEnrollmentInfoById($id)
    {
        if ($id!='') {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()->from(
                ['se' => 'kj_scheme_enrollment'],
                ['*']
            )->where("se.id = ?", $id);
            $records = $connection->fetchRow($select);
            if (is_array($records)) {
                if (count($records)>0) {
                    return $records;
                }
            }
        }
        return [];
    }

    /**
     * Get Customer enrollment info by id
     *
     * @param int $id
     * @param int $customer_id
     * @return array
     */
    public function geCustomertEnrollmentInfoById($id, $customer_id)
    {
        if ($id!='' && $customer_id!='') {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()->from(
                ['se' => 'kj_scheme_enrollment'],
                ['*']
            )->where("se.id = ?", $id)->where("se.customer_id = ?", $customer_id);
            $records = $connection->fetchRow($select);
            if (is_array($records)) {
                if (count($records)>0) {
                    return $records;
                }
            }
        }
        return [];
    }

    /**
     * Update transaction
     *
     * @param int $id
     * @param int $razorapay_order_id
     * @param string $razorpay_signature
     * @param string $razorpay_payment_id
     * @param boolean $isAdminUpdate
     */
    public function updateTransactionSuccessful($id, $razorapay_order_id, $razorpay_signature, $razorpay_payment_id = '', $isAdminUpdate = false)
    {
        $connection = $this->resourceConnection->getConnection();
        if (($id!='' && $razorapay_order_id!='' && $razorpay_signature!='') || ($isAdminUpdate==true && $id!='')) {
            $records=$this->getPaymentInfoById($id);
            if (count($records) > 0) {
                if ($records['payment_status']==self::SCHEME_PAYMENT_STATUS_PENDING_CODE) {
                    $enrollmentDetail=$this->getEnrollmentInfoById($records['enrollment_id']);
                    if (count($enrollmentDetail) > 0) {
                        $dueList =$this->insertInstallmentSchedules($enrollmentDetail['id'], $enrollmentDetail['duration'], $isPaidFirst=true);

                        $installmentDetail=$this->getNextInstallmentDetail($enrollmentDetail['id']);
                        if (count($installmentDetail) > 0) {
                            $data=[];
                            $data['payment_status']=self::SCHEME_PAYMENT_STATUS_COMPLETED_CODE;
                            $data['installment_schedule_id']=isset($installmentDetail['id'])?$installmentDetail['id']:null;
                            $data['transaction_info']=json_encode(['razorapay_order_id'=>$razorapay_order_id,'razorpay_signature'=>$razorpay_signature,'razorpay_payment_id'=>$razorpay_payment_id]);
                            $where = ['id = ?' => (int)$id];
                            $connection->update('kj_scheme_payment_history', $data, $where);
                            $this->updateScheduleInstallment($installmentDetail['id']);

                            if ($records['enrollment_id']!='') {
                                $data=[];

                                if ($dueList) {
                                    $data['maturity_date']=end($dueList);
                                }
                                $data['status']=self::SCHEME_ENROLLMENT_STATUS_ACTIVE_CODE;
                                $where = ['id = ?' => (int)$records['enrollment_id']];
                                $connection->update('kj_scheme_enrollment', $data, $where);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Update installment transaction
     *
     * @param int $id
     * @param int $razorapay_order_id
     * @param int $razorpay_signature
     * @param string $razorpay_payment_id
     */
    public function updateInstallmentTransactionSuccessful($id, $razorapay_order_id, $razorpay_signature, $razorpay_payment_id = '')
    {
        $connection = $this->resourceConnection->getConnection();
        if ($id!='' && $razorapay_order_id!='' && $razorpay_signature!='') {
            try {
                $records=$this->getPaymentInfoById($id);
                if (count($records) > 0) {
                    if ($records['payment_status']==self::SCHEME_PAYMENT_STATUS_PENDING_CODE) {
                        $enrollmentDetail=$this->getEnrollmentInfoById($records['enrollment_id']);
                        if (count($enrollmentDetail) > 0) {
                            $installmentDetail=$this->getCurrentInstallmentDetail($records['installment_schedule_id']);
                            if (count($installmentDetail) > 0) {
                                $data=[];
                                $data['payment_status']=self::SCHEME_PAYMENT_STATUS_COMPLETED_CODE;
                                $data['transaction_info']=json_encode(['razorapay_order_id'=>$razorapay_order_id,'razorpay_signature'=>$razorpay_signature,'razorpay_payment_id'=>$razorpay_payment_id]);
                                $where = ['id = ?' => (int)$id];
                                $connection->update('kj_scheme_payment_history', $data, $where);
                                $this->updateScheduleInstallment($installmentDetail['id']);
                                $isDefaulter=$this->getIsDefaulterFlag($records['payment_date'], $installmentDetail['due_date']);
                                $this->changeEnrollmentStatus($enrollmentDetail['id'], $isDefaulter);
                                $this->_eventManager->dispatch('scheme_installament_payment_complete_after',['scheme_payment_id' => $id, 'enrollment_data' => $enrollmentDetail]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Something goes wrong while Installment Payment. Please try again.'));
            }
        }
    }

    /**
     * Get is defaulter flag
     *
     * @param string $paymentDate
     * @param string $dueDate
     * @return int
     */
    private function getIsDefaulterFlag($paymentDate, $dueDate)
    {
        if ($paymentDate <= $dueDate) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Change enrollment status
     *
     * @param int $enrollmentId
     * @param integer $isDefaulter
     */
    public function changeEnrollmentStatus($enrollmentId, $isDefaulter = 0)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            $enrollmentDetail=$this->getEnrollmentInfoById($enrollmentId);
            if ($enrollmentDetail) {
                $data=[];
                if ($isDefaulter==1) {
                    $data['is_defaulter']=$isDefaulter;
                }
                $result = $this->getAllTotalPaidAndTotalMonth($enrollmentId);
                if ($result) {
                    $total_enrollmentAmount=$enrollmentDetail['emi_amount']*$enrollmentDetail['duration'];
                    if ($result['total_amount_paid']==$total_enrollmentAmount && $enrollmentDetail['duration']==$result['no_paid_month']) {
                        $data['status']=self::SCHEME_ENROLLMENT_STATUS_MATURED_CODE;
                        $data['maturity_date']=$this->date->date('Y-m-d h:i:s');
                    }
                }
                if (count($data) > 0) {
                    $where = ['id = ?' => (int)$enrollmentId];
                    $connection->update('kj_scheme_enrollment', $data, $where);
                }
            }
        }
    }

    /**
     * Change precosure enrollment status
     *
     * @param int $enrollmentId
     * @return bool|null
     */
    public function changePreClosureEnrollmentStatus($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();

        if ($enrollmentId!='') {
            $enrollmentDetail=$this->getEnrollmentInfoById($enrollmentId);
            if ($enrollmentDetail) {
                try {
                    $result = $this->getAllTotalPaidAndTotalMonth($enrollmentId);
                    if ($result) {
                        $noPaidMonth=$result['no_paid_month'];
                        $duration=$enrollmentDetail['duration'];
                        $preclosureMonth=$this->helperConfigScheme->getMonthForpreclouser($duration);
                        if ($noPaidMonth>=$preclosureMonth) {
                            $data=[];
                            $data['status']=self::SCHEME_ENROLLMENT_STATUS_PRECLOSED_CODE;
                            $data['maturity_date']=$this->date->date('Y-m-d h:i:s');
                            $where = ['id = ?' => (int)$enrollmentId];
                            $connection->update('kj_scheme_enrollment', $data, $where);
                            return true;
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(__('Preclouser Are not allowed for the scheme.'));
                        }
                    }
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Something goes wrong while Installment Payment. Please try again.'));
                }
            }
        }
    }

    /**
     * Get all total paid and total month
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getAllTotalPaidAndTotalMonth($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $query="SELECT sum(amount) as total_amount_paid,count(DISTINCT month) as no_paid_month FROM kj_scheme_installment_schedules schd left join kj_scheme_payment_history ph on schd.id=ph.installment_schedule_id where schd.enrollment_id = '".$enrollmentId."' and (payment_status = 'completed' and schd.is_paid=1)";
            $result = $connection->fetchRow($query);
            return $result;
        }
        return [];
    }

    /**
     * Is enrollment matured
     *
     * @param int $enrollmentId
     * @return boolean
     */
    public function isEnrollmentMatured($enrollmentId)
    {
        if ($enrollmentId!='') {
            $connection = $this->resourceConnection->getConnection();
            $enrollmentDetail=$this->getEnrollmentInfoById($enrollmentId);
            if ($enrollmentDetail) {
                if ($enrollmentDetail['status']==self::SCHEME_ENROLLMENT_STATUS_MATURED_CODE) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Update transaction failure
     *
     * @param int $id
     * @param int $razorapay_order_id
     * @param string $razorpay_signature
     */
    public function updateTransactionFailure($id, $razorapay_order_id, $razorpay_signature)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($id!='') {
            $records=$this->getPaymentInfoById($id);
            if (count($records) > 0) {
                if ($records['payment_status']==self::SCHEME_PAYMENT_STATUS_PENDING_CODE) {
                    $data=[];
                    $data['payment_status']='failed';
                    $where = ['id = ?' => (int)$id];
                    $connection->update('kj_scheme_payment_history', $data, $where);
                }
            }
        }
    }

    /**
     * Get enrollment detail by payment id
     *
     * @param int $paymentId
     * @return array
     */
    public function getEnrollmentDetailByPaymentId($paymentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($paymentId!='') {
            $records=$this->getPaymentInfoById($paymentId);
            if (count($records) > 0) {
                return $this->getEnrollmentInfoById($records['enrollment_id']);
            }
        }
        return [];
    }

    /**
     * Get online transaction mode
     *
     * @param string $paymentMode
     * @return string
     */
    public function getOnlineTramsactionMode($paymentMode = '')
    {
        if ($paymentMode==self::SCHEME_PAYMENT_MODE_CODE) {
            return 'offline';
        }
        return 'online';
    }

    /**
     * Get initial payment status
     *
     * @return string
     */
    public function getInitialPaymentStatus()
    {
        return self::SCHEME_PAYMENT_STATUS_PENDING_CODE;
    }

    /**
     * Insert installment schedules
     *
     * @param int $enrollment_id
     * @param string $duration
     * @return array|bool
     */
    public function insertInstallmentSchedules($enrollment_id, $duration)
    {
        try {
            if ($enrollment_id!='' && $duration!='') {
                $schedulesDateList=$this->prepareInstallmentDates($duration);
                $dueList = array_column($schedulesDateList, 'endDate');
                $dataSchedule=[];
                foreach ($dueList as $dateDue) {
                    $dataSchedule[]=['due_date'=>$dateDue,'enrollment_id'=>$enrollment_id];
                }
                if (count($dataSchedule) > 0) {
                    $connection  = $this->resourceConnection->getConnection();
                    $tableName = $connection->getTableName('kj_scheme_installment_schedules');
                    $connection->insertMultiple($tableName, $dataSchedule);
                    return $dueList;
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something goes wrong while enrollment save. Please try again.')
            );
            return false;
        }
    }

    /**
     * Prepare installment dates
     *
     * @param integer $duration
     * @return array
     */
    public function prepareInstallmentDates($duration = 0)
    {
        $currentYearArray = [];

        $current_Date = $this->timezone->date()->format('Y-m-d');
        for ($m=1; $m<=$duration; $m++) {
            $last_date_of_month=$this->timezone->date(new \DateTime($current_Date))->format('Y-m-t');

            $currentYearArray[$m]['startDate'] = $this->timezone->date(new \DateTime($last_date_of_month))->format('Y-m-01 00:00:00');
            $lastdateOfmonth = $this->timezone->date(new \DateTime($last_date_of_month))->format('Y-m-t');
            $currentYearArray[$m]['endDate'] = $this->timezone->date(new \DateTime($last_date_of_month))->format('Y-m-t 23:59:59');
            $this->timezone->date(new \DateTime($last_date_of_month))->format('Y-m-d');
            $current_Date=$this->timezone->date(new \DateTime($this->date->date('Y-m-d', strtotime($lastdateOfmonth." +1 days"))))->format('Y-m-d');
        }

        return $currentYearArray;
    }

    /**
     * Get next installment detail
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getNextInstallmentDetail($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $nextInstallmentquery = "SELECT * FROM kj_scheme_installment_schedules where enrollment_id='".$enrollmentId."' and is_paid=0 order by id asc limit 1";
            $result = $connection->fetchRow($nextInstallmentquery);
            return $result;
        }
        return [];
    }

    /**
     * Get current installment detail
     *
     * @param int $installmentId
     * @return array
     */
    public function getCurrentInstallmentDetail($installmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($installmentId!='') {
            // phpcs:ignore
            $nextInstallmentquery = "SELECT * FROM kj_scheme_installment_schedules where id='".$installmentId."' and is_paid=0";
            $result = $connection->fetchRow($nextInstallmentquery);
            return $result;
        }
        return [];
    }

    /**
     * Update schedule installment
     *
     * @param int $id
     * @return bool
     */
    public function updateScheduleInstallment($id)
    {
        $connection = $this->resourceConnection->getConnection();
        try {
            if ($id!='') {
                // phpcs:ignore
                $queryUpdateInstallment = "UPDATE kj_scheme_installment_schedules SET is_paid= '1' WHERE id = $id ";
                $connection->query($queryUpdateInstallment);
                return true;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something goes wrong while enrollment save. Please try again.')
            );
        }
    }

    /**
     * Get list of enrollment by customer id
     *
     * @param int $customerId
     * @return array
     */
    public function getListOfEnrollmentByCustomerId($customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($customerId!='') {
            // phpcs:ignore
            $enrollmentquery = "SELECT * FROM kj_scheme_enrollment where customer_id='".$customerId."' and status not in ('pending')";
            $result = $connection->fetchAll($enrollmentquery);
            return $result;
        }
        return [];
    }

    /**
     * Get all paid installment of enrollment id
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getAllPaidInstallmentByEnrollmentId($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $installmentSchedulesquery = "SELECT * FROM kj_scheme_installment_schedules where is_paid='1' AND enrollment_id='".$enrollmentId."'";
            $result = $connection->fetchAll($installmentSchedulesquery);
            return $result;
        }
        return [];
    }

    /**
     * Get paid installment count of enrollment id
     *
     * @param int $enrollmentId
     * @return int
     */
    public function getCountOfPaidInstallmentByEnrollmentId($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $installmentSchedulesquery = "SELECT count(id) as cnt FROM kj_scheme_installment_schedules where is_paid='1' AND enrollment_id='".$enrollmentId."'";
            $result = $connection->fetchRow($installmentSchedulesquery);
            if (count($result) > 0) {
                if (isset($result['cnt'])) {
                    return $result['cnt'];
                }
            }
        }
        return 0;
    }

    /**
     * Get pending installment count by enrollment id
     *
     * @param int $enrollmentId
     * @return int
     */
    public function getCountOfPendingInstallmentByEnrollmentId($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $installmentSchedulesquery = "SELECT count(id) as cnt FROM kj_scheme_installment_schedules where is_paid='0' AND enrollment_id='".$enrollmentId."'";
            $result = $connection->fetchRow($installmentSchedulesquery);
            if (count($result) > 0) {
                if (isset($result['cnt'])) {
                    return $result['cnt'];
                }
            }
        }
        return 0;
    }

    /**
     * Get list of enrollment of customer
     *
     * @param int $customerId
     * @return array
     */
    public function getListOfEnrollmentOfCustomer($customerId)
    {
        $enrollmentList=[];
        if ($customerId!='') {
            $listEnrollment=$this->getListOfEnrollmentByCustomerId($customerId);
            foreach ($listEnrollment as $enroll) {
                $enroll['paid_installment']=$this->getCountOfPaidInstallmentByEnrollmentId($enroll['id']);
                $enroll['status_label']=$this->getStatusLabel($enroll['status']);
                $enroll['total_enrollment_amount']=$enroll['emi_amount']*$enroll['duration'];
                $totalAmountPaid=$this->getTotalAmountPaid($enroll['id']);
                list($benefit_amount, $benefit_percentage)=$this->getBenefitOfEnrollment($enroll['id']);
                $enroll['total_enrollment_amount']=$totalAmountPaid;
                $enroll['total_amount_paid']=$totalAmountPaid;
                $enroll['benefit_amount']=$benefit_amount;
                $enroll['redemption_amount']=$benefit_amount+$totalAmountPaid;
                $enroll['view_enrollment_url']=$this->helperDataScheme->getViewEnrollmentUrl($enroll['id']);

                if ($enroll['status']==self::SCHEME_ENROLLMENT_STATUS_ACTIVE_CODE) {
                    $nextInstallmentDetail=$this->getNextInstallmentDetail($enroll['id']);
                    if ($nextInstallmentDetail) {
                        if (count($nextInstallmentDetail) > 0) {
                            if ($nextInstallmentDetail['due_date']!='') {
                                $is_allowPayment=$this->isAllowNextInstallmentPayment($nextInstallmentDetail['due_date'], $enroll['email_id']);
                                $enroll['is_installment_payment_allow']=$is_allowPayment;
                            }
                        }
                    }
                }
                $enrollmentList[]=$enroll;
            }
        }
        return $enrollmentList;
    }

    /**
     * Get status label
     *
     * @param string $statusCode
     * @return string
     */
    public function getStatusLabel($statusCode)
    {
        if ($statusCode==self::SCHEME_ENROLLMENT_STATUS_ACTIVE_CODE) {
            return 'Active';
        } elseif ($statusCode==self::SCHEME_ENROLLMENT_STATUS_MATURED_CODE) {
            return 'Matured';
        } elseif ($statusCode==self::SCHEME_ENROLLMENT_STATUS_PRECLOSED_CODE) {
            return 'Preclosed';
        }
        return '';
    }

    /**
     * Is allowed installment payment
     *
     * @param string $due_date
     * @return boolean
     */
    private function isAllowInstallmentPayment($due_date)
    {
        if ($due_date!='') {
            $current_Date = $this->timezone->date()->format('Y-m-d');
            $due_datestart = $this->date->date('Y-m-01', strtotime($due_date));
            $due_dateEnd = $this->date->date('Y-m-d', strtotime($due_date));

            if (($current_Date >= $due_datestart) && ($current_Date <= $due_dateEnd)) {
                return true;
            } elseif (($due_dateEnd <= $current_Date)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get next installment date
     *
     * @param int $enrollmentId
     * @return string
     */
    public function getNextInstallmentDate($enrollmentId)
    {
        if ($enrollmentId) {
            $nextInstallmentDetail=$this->getNextInstallmentDetail($enrollmentId);
            if ($nextInstallmentDetail) {
                if (count($nextInstallmentDetail) > 0) {
                    if ($nextInstallmentDetail['due_date']!='') {
                        return $this->helperDataScheme->getViewPageEnrollmentDateForamt($nextInstallmentDetail['due_date']);
                    }
                }
            }
        }
        return '';
    }

    /**
     * Get next installment date format
     *
     * @param string $nextInstallmentDueDate
     * @return string
     */
    public function getNextInstallmentDateFormat($nextInstallmentDueDate)
    {
        if ($nextInstallmentDueDate!='') {
            return $this->helperDataScheme->getViewPageEnrollmentDateForamt($nextInstallmentDueDate);
        }
        return '';
    }

    /**
     * Get view enrollment detail
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getViewEnrollmentDetail($enrollmentId)
    {
        $detail=[];
        if ($enrollmentId!='') {
            $enrollment=[];
            $enrollment=$this->getEnrollmentInfoById($enrollmentId);
            list($benefit_amount, $benefit_percentage)=$this->getBenefitOfEnrollment($enrollment['id']);
            $totalAmountPaid=$this->getTotalAmountPaid($enrollment['id']);
            $noPaidMonth=$this->getCountOfPaidInstallmentByEnrollmentId($enrollment['id']);
            $enrollment['paid_installment']=$noPaidMonth;
            $enrollment['total_amount_paid']=$totalAmountPaid;
            $enrollment['benefit_amount']=$benefit_amount;
            $enrollment['redemption_amount']=$benefit_amount+$totalAmountPaid;
            $enrollment['status_label']=$this->getStatusLabel($enrollment['status']);
            $enrollment['enrollment_date_format']=$this->helperDataScheme->getViewPageEnrollmentDateForamt($enrollment['created_at']);
            $enrollment['redemption_date_format']=$this->helperDataScheme->getViewPageEnrollmentDateForamt($enrollment['created_at']);

            $preclosureMonth=$this->helperConfigScheme->getMonthForpreclouser($enrollment['duration']);

            $enrollment['is_preclouser_allow']=false;
            if ($enrollment['status']==self::SCHEME_ENROLLMENT_STATUS_ACTIVE_CODE) {
                if ($noPaidMonth>=$preclosureMonth) {
                    $enrollment['is_preclouser_allow']=true;
                }
                $nextInstallmentDetail=$this->getNextInstallmentDetail($enrollment['id']);
                if ($nextInstallmentDetail) {
                    if (count($nextInstallmentDetail) > 0) {
                        if ($nextInstallmentDetail['due_date']!='') {
                            $enrollment['next_due_date_format']=$this->helperDataScheme->getViewPageEnrollmentDateForamt($nextInstallmentDetail['due_date']);
                            $is_allowPayment=$this->isAllowNextInstallmentPayment($nextInstallmentDetail['due_date'], $enrollment['email_id']);
                            $enrollment['is_installment_payment_allow']=$is_allowPayment;
                        }
                    }
                }
            }

            $installmentList=[];
            $installmentRecords=$this->getAllInstallmentRecordsWithCompletedOrNew($enrollmentId);
            if (count($installmentRecords) > 0) {
                $count=1;
                foreach ($installmentRecords as $installment) {
                    $installment['no']=$count;
                    $installment['amount']=$enrollment['emi_amount'];
                    $installment['due_date_format']=$this->helperDataScheme->getViewPageDateForamte($installment['due_date']);
                    $installment['payment_date_format']=$this->helperDataScheme->getViewPageDateForamte($installment['payment_date']);
                    $installmentList[]=$installment;
                    $count++;
                }
            }

            $detail['enrollment']=$enrollment;
            $detail['installmentList']=$installmentList;
            $detail['nominee']=$this->getNominee($enrollmentId);
        }
        return $detail;
    }

    /**
     * Allow forcegully installment payment
     *
     * @param string $email
     * @return bool
     */
    public function allowForcefullyForInstallmentPayment($email)
    {
        if ($email!='') {
            if ($this->helperConfigScheme->isAllowCandereEmailId()) {
                if (strpos($email, "@candere.com")!==false) {
                    return true;
                }
            }
            if (in_array($email, $this->helperConfigScheme->forcefullyAllowInstallmentPayment())) {
                return true;
            }
        }
    }

    /**
     * Check next installment payment allow
     *
     * @param string $due_date
     * @param string $email
     * @return boolean
     */
    public function isAllowNextInstallmentPayment($due_date, $email)
    {
        $is_allowPayment=$this->isAllowInstallmentPayment($due_date);
        if (!$is_allowPayment) {
            if ($this->allowForcefullyForInstallmentPayment($email)) {
                return true;
            }
        }
        return $is_allowPayment;
    }

    /**
     * Get Nominee
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getNominee($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $nomineequery = "SELECT * FROM kj_scheme_nominee where  enrollment_id='".$enrollmentId."'";
            $result = $connection->fetchRow($nomineequery);
            return $result;
        }
        return [];
    }

    /**
     * Get all completed/new installment record
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getAllInstallmentRecordsWithCompletedOrNew($enrollmentId)
    {
         $connection = $this->resourceConnection->getConnection();
         $response=[];
        if ($enrollmentId!='') {
            // phpcs:ignore
            $query="SELECT schd.due_date as due_date,schd.is_paid as is_paid,ph.enrollment_id as enrollment_id,ph.amount as amount,ph.payment_date as payment_date,ph.month as month FROM kj_scheme_installment_schedules schd left join kj_scheme_payment_history ph on schd.id=ph.installment_schedule_id where  schd.enrollment_id = '".$enrollmentId."' and (payment_status = 'completed') order by schd.id asc";
            $Paidresult = $connection->fetchAll($query);
            if (count($Paidresult) > 0) {
                $response=$Paidresult;
            }
            // phpcs:ignore
            $query="SELECT schd.due_date as due_date,schd.is_paid as is_paid,'' as enrollment_id,'' as amount,'' as payment_date,'' as month FROM kj_scheme_installment_schedules schd  where schd.enrollment_id = '".$enrollmentId."' and (is_paid=0) order by schd.id asc";
            $pendingresult = $connection->fetchAll($query);
            if (count($pendingresult) > 0) {
                foreach ($pendingresult as $res) {
                    array_push($response, $res);
                }
            }
        }
        return $response;
    }

    /**
     * Get all installment records
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getAllInstallmentRecords($enrollmentId)
    {
         $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $query="SELECT schd.due_date as due_date,schd.is_paid as is_paid,ph.enrollment_id as enrollment_id,ph.amount as amount,ph.payment_date as payment_date,ph.month as month,ph.transaction_mode,ph.payment_mode as payment_mode,ph.store_code as store_code,ph.payment_status as payment_status, schd.id as installmentId FROM kj_scheme_installment_schedules schd left join kj_scheme_payment_history ph on schd.id=ph.installment_schedule_id where  schd.enrollment_id = '".$enrollmentId."' order by schd.id asc";
            $result = $connection->fetchAll($query);
            if (count($result) > 0) {
                return $result;
            }
        }
        return [];
    }

    /**
     * Get total amount paid
     *
     * @param int $enrollmentId
     * @return string
     */
    public function getTotalAmountPaid($enrollmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        if ($enrollmentId!='') {
            // phpcs:ignore
            $query="SELECT sum(amount) as total_amount_paid FROM kj_scheme_installment_schedules schd left join kj_scheme_payment_history ph on schd.id=ph.installment_schedule_id where schd.enrollment_id = '".$enrollmentId."' and (payment_status = 'completed' and schd.is_paid=1)";
            $result = $connection->fetchRow($query);
            if (count($result) > 0) {
                return $result['total_amount_paid'];
            }
        }
        return '';
    }

    /**
     * Get benefit of enrollment
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getBenefitOfEnrollment($enrollmentId)
    {
        $defaulter=false;
        $benefitAmt=0;
        $paidMonth=0;
        $benefitper=0;
        if ($enrollmentId) {
            $enrollmentDetail=$this->getEnrollmentInfoById($enrollmentId);
            if ($enrollmentDetail) {
                if ($enrollmentDetail['is_defaulter']==1) {
                    $defaulter=true;
                }
                $allEnrollmentDetail=$this->getAllInstallmentRecordsWithCompletedOrNew($enrollmentId);
                $paidInstallmentListFlag=array_filter(array_column($allEnrollmentDetail, 'is_paid'));
                $paidMonth=count($paidInstallmentListFlag);
                if (!$defaulter) {
                    $benefit_list='';
                    if (isset($enrollmentDetail['benefit_list']) && !empty($enrollmentDetail['benefit_list'])) {
                        $benefit_list=json_decode($enrollmentDetail['benefit_list'], true);
                    }
                    $benefitper=$this->getBenefitPercentage($paidMonth, $enrollmentDetail['duration'], $benefit_list);
                    $benefitAmt=(($enrollmentDetail['emi_amount']*$benefitper)/100);
                }
            }
        }
        return [$benefitAmt,$benefitper];
    }

    /**
     * Get benefit amount and percentage
     *
     * @param integer $paidMonth
     * @param integer $duration
     * @param integer $emiAmount
     * @return array
     */
    public function getBenefitAmountAndPercentage($paidMonth = 0, $duration = 0, $emiAmount = 0)
    {
        $benefitper=$this->getBenefitPercentage($paidMonth, $duration);
        $benefitAmt=(($emiAmount*$benefitper)/100);
        return [$benefitAmt,$benefitper];
    }

    /**
     * Get benefit percentage
     *
     * @param int $totalPaidMonth
     * @param int $duration
     * @param array $benefitList
     * @return int
     */
    public function getBenefitPercentage($totalPaidMonth, $duration, $benefitList = [])
    {
        if ($benefitList) {
            $benefitList=$benefitList;
        } else {
            $benefitList=$this->helperConfigScheme->getBenefitPercentageList($duration);
        }
        if (array_key_exists($totalPaidMonth, $benefitList)) {
            return $benefitList[$totalPaidMonth];
        }
        return 0;
    }

    /**
     * Get Precloser Information
     *
     * @param int $enrollmentId
     * @return array
     */
    public function getPrecloserInformation($enrollmentId)
    {
        $preclouser=[];
        list($benefit_amount,$benefit_percentage)=$this->getBenefitOfEnrollment($enrollmentId);
        $totalAmountPaid=$this->getTotalAmountPaid($enrollmentId);
        $noPaidMonth=$this->getCountOfPaidInstallmentByEnrollmentId($enrollmentId);
        $preclouser['enrollmentId']=$enrollmentId;
        $preclouser['paid_installment']=$noPaidMonth;
        $preclouser['total_amount_paid']=$totalAmountPaid;
        $preclouser['benefit_amount']=$benefit_amount;
        $preclouser['benefit_percentage']=$benefit_percentage;
        $preclouser['redemption_amount']=$benefit_amount+$totalAmountPaid;
        $preclouser['missingBenefit']=$this->missingBenefit($enrollmentId, $noPaidMonth);
        return $preclouser;
    }

    /**
     * Missing benefit
     *
     * @param int $enrollmentId
     * @param int $noPaidMonth
     * @return array
     */
    public function missingBenefit($enrollmentId, $noPaidMonth)
    {
        $missingBenefit=[];
        $enrollment=$this->getEnrollmentInfoById($enrollmentId);
        $duration=$enrollment['duration'];
        $preclosureMonth=$this->helperConfigScheme->getMonthForpreclouser($duration);

        if (isset($enrollment['benefit_list']) && !empty($enrollment['benefit_list'])) {
            $benefitList=json_decode($enrollment['benefit_list'], true);
        } else {
            $benefitList=$this->helperConfigScheme->getBenefitPercentageList($duration);
        }

        if (isset($enrollment['is_defaulter']) && $enrollment['is_defaulter']) {
            return $missingBenefit;
        }
        if ($noPaidMonth>=$preclosureMonth) {

            for ($month=$noPaidMonth+1; $month <=$duration; $month++) {
                if (array_key_exists($month, $benefitList)) {
                    if ($benefitList[$month] > 0) {
                        $benefitAmt=(($enrollment['emi_amount']*$benefitList[$month])/100);
                        $total_amount=$enrollment['emi_amount']*$month;
                        $total_amount=$enrollment['emi_amount']*$month;
                        $missingBenefit[]=[
                            'month'=>$month,
                            'percentage'=>$benefitList[$month],
                            'benefit_amount'=>$benefitAmt,
                            'total_amount'=>$total_amount,
                            'total_final_amount'=>($total_amount+$benefitAmt)
                        ];
                    }
                }
            }
        }
        return $missingBenefit;
    }

    /**
     * @param $enrollmentId
     * @param $duration
     * @return void
     */
    public function updatePlanNo($enrollmentId, $duration)
    {
        $condition = ['id = ?' => $enrollmentId];
        $data['plan_no'] = $this->insertSequencePlan($enrollmentId,$duration);
        $this->update($data,self::SCHEME_ENROLLMENT_TABLE,$condition);
    }
    /**
     * Update data
     *
     * @param array $data
     * @param string $tablename
     * @param string|array $where
     * @return void
     */
    public function update($data, $tablename, $where)
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName($tablename);
        $connection->update($tableName, $data, $where);
    }
}
