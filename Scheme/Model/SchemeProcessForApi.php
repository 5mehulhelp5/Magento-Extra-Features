<?php

namespace KalyanUs\Scheme\Model;

use Magento\Framework\App\ResourceConnection;
use KalyanUs\Scheme\Model\EnrollmentProcess;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use KalyanUs\Scheme\Helper\Config;
use KalyanUs\Scheme\Helper\Data;
use Magento\Authorization\Model\UserContextInterface;

class SchemeProcessForApi
{
    public const SCHEME_ENROLLMENT_TABLE = 'kj_scheme_enrollment';
    public const SCHEME_NOMINEE_TABLE = 'kj_scheme_nominee';
    public const SCHEME_SEQUENCE_TABLE = 'kj_scheme_sequence';
    public const SCHEME_PAYMENT_TABLE = 'kj_scheme_payment_history';
    public const SCHEME_INSTALLMENT_SCHEDULE_TABLE = 'kj_scheme_installment_schedules';
    public const SCHEME_ENROLLMENT_STATUS_OPEN_CODE = 'opened';

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var DateTime
     */
    private DateTime $date;

    /**
     * @var Config
     */
    private Config $helperConfigScheme;

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param DateTime $date
     * @param Config $helperConfigScheme
     * @param UserContextInterface $userContext
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezone,
        DateTime $date,
        Config $helperConfigScheme,
        UserContextInterface $userContext
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->date = $date;
        $this->helperConfigScheme = $helperConfigScheme;
        $this->userContext = $userContext;
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
        $tableName = $connection->getTableName($tablename);
        $connection->insert($tableName, $data);
        return $connection->lastInsertId();
    }


    /**
     * @param $data
     * @param $enrollmentNo
     * @return array
     * @throws LocalizedException
     */
    public function saveScheme($data, $enrollmentNo)
    {
        $paymentId='';
        $enrollmentId = $this->saveEnrollment($data, $enrollmentNo);
        if ($enrollmentId!='') {
            $nomieeId = $this->saveNominee($data, $enrollmentId);
            $paymentId = $this->savePayment($data, $enrollmentId);
        }
        return ['enrollment_id' => $enrollmentId];
    }

    public function saveEnrollment($data, $enrollmentNo)
    {
        try {
            $schemeData=[];
            $customerId = $this->userContext->getUserId() ?? null;
            if (!$customerId) {
                throw new LocalizedException(__('Customer Id is required'));
            }
            $schemeData['customer_id'] = $customerId;
            $schemeData['email_id'] = $data['personalDetails']['emailAddress'] ?? '';
            $schemeData['customer_name']= $data['personalDetails']['FirstName'] ?? ''.$data['personalDetails']['LastName'] ?? '';
            $schemeData['scheme_mobile_number'] = $data['personalDetails']['MobileNumber'] ?? '';
            $schemeData['is_mobile_verified'] = 1;
            $schemeData['address'] = $data['personalDetails']['Address1'] ?? ''. $data['personalDetails']['Address2'] ?? '';
            $schemeData['pincode'] = $data['personalDetails']['Pincode'] ?? '';
            $schemeData['state'] = $data['personalDetails']['State'] ?? '';
            $schemeData['city']= $data['personalDetails']['State'] ?? '';
            $schemeData['duration'] = $data['personalDetails']['NoOfInstallments'] ?? 0;
            $schemeData['emi_amount'] = $data['personalDetails']['EMI'] ?? 0;
            $schemeData['status'] = self::SCHEME_ENROLLMENT_STATUS_OPEN_CODE;
            $schemeData['auto_monthly_payment'] =  0;
            $schemeData['scheme_name']= $data['personalDetails']['SchemeType'] ?? '';
            $schemeData['maturity_date'] = $data['personalDetails']['MaturityDate'] ?? '';
            $schemeData['benefit_list'] = json_encode($this->helperConfigScheme->getBenefitPercentageList($data['personalDetails']['NoOfInstallments']));
            $schemeData['enrollment_no'] = $data['personalDetails']['EnrollmentNo'] ?? null;
            $condition = ['enrollment_no = ?' => $enrollmentNo];
            $existingId = $this->checkData(self::SCHEME_ENROLLMENT_TABLE,['id'],$condition);
            if ($existingId) {
                $this->update($schemeData,self::SCHEME_ENROLLMENT_TABLE,$condition);
                $lastInsertId = $existingId;
            } else {
                $lastInsertId = $this->insert($schemeData, self::SCHEME_ENROLLMENT_TABLE);
                $this->updatePlanNo($lastInsertId, $data['personalDetails']['NoOfInstallments']);
            }
            return $lastInsertId;
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Insert sequence plan
     *
     * @param string $duration
     * @return string
     */
    private function insertSequencePlan($enrollmentId, $duration)
    {
        $data = [];
        $data['sequence_value'] = $enrollmentId;
        $lastInsertId = $this->insert($data, self::SCHEME_SEQUENCE_TABLE);
        $prefixScheme=$this->helperConfigScheme->getEnrollPrefix($duration);
        return $prefixScheme.''.$lastInsertId;
    }



    public function saveNominee($data, $enrollmentId)
    {
        if ($enrollmentId!='') {
            try {
                $schemeData=[];
                $schemeData['enrollment_id'] = $enrollmentId;
                $schemeData['nominee_name'] = $data['personalDetails']['NomineeFirstName'] ?? '' .$data['personalDetails']['NomineeLastName'] ?? '';
                $schemeData['nominee_relationship']= $data['personalDetails']['NomineeRelationship'] ?? '';
                $schemeData['nominee_mobilenumber'] = $data['personalDetails']['NomineeMobileNumber'] ?? '';
                $schemeData['nominee_nationality'] = '';
                $condition = ['enrollment_id = ?' => $enrollmentId];
                $existingId = $this->checkData(self::SCHEME_NOMINEE_TABLE,['id'],$condition);
                if ($existingId) {
                    $this->update($schemeData,self::SCHEME_NOMINEE_TABLE, $condition);
                    return $existingId;
                } else {
                    return $this->insert($schemeData, self::SCHEME_NOMINEE_TABLE);
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
        return '';
    }

    /**
     * Save or update payments
     *
     * @param array $data
     * @param int $enrollmentId
     * @return string
     */
    public function savePayment($data, $enrollmentId)
    {
        if ($enrollmentId != '') {
            $dataSchedule = $this->insertInstallmentSchedules(
                $enrollmentId,
                $data['personalDetails']['NoOfInstallments'] ?? '',
                $data['Collections'][0]['Date'] ?? null
            );

            $connection = $this->resourceConnection->getConnection();
            $paymentTable = $connection->getTableName(self::SCHEME_PAYMENT_TABLE);
            $scheduleTable = $connection->getTableName(self::SCHEME_INSTALLMENT_SCHEDULE_TABLE);

            foreach ($data['Collections'] as $key => $item) {
                $schemeData = [
                    'enrollment_id' => $enrollmentId,
                    'installment_schedule_id' => $dataSchedule[$key]['id'],
                    'amount' => $item['Amount'] ?? '',
                    'month' => $item['EMIMonth'] ?? '',
                    'payment_date' => isset($item['Date']) ? $this->getFormattedDate($item['Date']) : '',
                    'payment_status' => $item['PaymentStatus'] ? strtolower($item['PaymentStatus']) :'',
                    'reference_no' => $item['ReferenceNo'] ?? '',
                    'transaction_mode' => $this->getOnlineTramsactionMode($item['MOP'] ?? ''),
                    'payment_mode' => $item['MOP'] ?? ''
                ];

                // Check if the payment already exists
                $select = $connection->select()
                    ->from($paymentTable)
                    ->where('enrollment_id = ?', $enrollmentId)
                    ->where('reference_no = ?', $item['ReferenceNo'] ?? '');

                $existingPayment = $connection->fetchRow($select);

                if ($existingPayment) {
                    // Update existing payment record
                    $connection->update(
                        $paymentTable,
                        $schemeData,
                        ['id = ?' => $existingPayment['id']]
                    );
                } else {
                    // Insert new payment record
                    $connection->insert($paymentTable, $schemeData);
                    $paymentId = $connection->lastInsertId();
                }

                // Mark installment as paid
                $connection->update(
                    $scheduleTable,
                    ['is_paid' => 1],
                    ['id = ?' => $dataSchedule[$key]['id']]
                );
            }
        }

        return '';
    }

    /**
     * Get online transaction mode
     *
     * @param string $paymentMode
     * @return string
     */
    public function getOnlineTramsactionMode($paymentMode = '')
    {
        if ($paymentMode=='CASH') {
            return 'offline';
        }
        return 'online';
    }

    /**
     * Insert or update installment schedules
     *
     * @param int $enrollment_id
     * @param string $duration
     * @param string|null $startDate
     * @return array
     */
    public function insertInstallmentSchedules($enrollment_id, $duration, $startDate = null)
    {
        try {
            if ($enrollment_id != '' && $duration != '') {
                $schedulesDateList = $this->prepareInstallmentDates($startDate, $duration);
                $dueList = array_column($schedulesDateList, 'endDate');

                $dataSchedule = [];
                $connection = $this->resourceConnection->getConnection();
                $tableName = $connection->getTableName(self::SCHEME_INSTALLMENT_SCHEDULE_TABLE);

                foreach ($dueList as $dateDue) {
                    // Check if the installment already exists
                    $select = $connection->select()
                        ->from($tableName)
                        ->where('enrollment_id = ?', $enrollment_id)
                        ->where('due_date = ?', $dateDue);

                    $existingRecord = $connection->fetchRow($select);

                    if ($existingRecord) {
                        // Update existing record
                        $connection->update(
                            $tableName,
                            ['due_date' => $dateDue],
                            ['id = ?' => $existingRecord['id']]
                        );
                        $dataSchedule[] = [
                            'id' => $existingRecord['id'],
                            'due_date' => $dateDue,
                            'enrollment_id' => $enrollment_id,
                            'action' => 'updated'
                        ];
                    } else {
                        // Insert new record
                        $data = ['due_date' => $dateDue, 'enrollment_id' => $enrollment_id];
                        $connection->insert($tableName, $data);
                        $insertedId = $connection->lastInsertId();
                        $dataSchedule[] = [
                            'id' => $insertedId,
                            'due_date' => $dateDue,
                            'enrollment_id' => $enrollment_id,
                            'action' => 'inserted'
                        ];
                    }
                }

                return $dataSchedule;
            }
        } catch (\Exception $e) {
            return [];
        }
    }


    /**
     * Prepare installment dates
     *
     * @param integer $duration
     * @return array
     */
    public function prepareInstallmentDates($startDate,$duration = 0)
    {
        $currentYearArray = [];

        $current_Date = $startDate ?? $this->timezone->date()->format('Y-m-d');
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

    public function getFormattedDate($date)
    {
        return $this->date->date('Y-m-d H:i:s',strtotime($date));
    }

    public function checkData($tablename, $selectionColumn, $conditions)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName($tablename);
        $select = $connection->select()
            ->from($tableName, $selectionColumn);

        foreach ($conditions as $condition => $value) {
            $select->where($condition, $value);
        }

        $existingId = $connection->fetchOne($select);
        return $existingId ?? null;
    }
    public function updatePlanNo($enrollmentId, $duration)
    {
        $condition = ['id = ?' => $enrollmentId];
        $data['plan_no'] = $this->insertSequencePlan($enrollmentId,$duration);
        $this->update($data,self::SCHEME_ENROLLMENT_TABLE,$condition);
    }
}
