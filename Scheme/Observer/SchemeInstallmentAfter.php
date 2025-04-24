<?php

namespace KalyanUs\Scheme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use KalyanUs\Scheme\Model\Api\SendPaymentDetails;

class SchemeInstallmentAfter implements ObserverInterface
{
    const SCHEME_PAYMENT_HISTORY_TABLE = 'kj_scheme_payment_history';

    /**
     * @var SendPaymentDetails
     */
    private SendPaymentDetails $sendPaymentDetails;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param SendPaymentDetails $sendPaymentDetails
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
      SendPaymentDetails $sendPaymentDetails,
      ResourceConnection $resourceConnection,
      LoggerInterface $logger
    ) {
        $this->sendPaymentDetails = $sendPaymentDetails;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        try {
            $enrollmentDetail = $observer->getEvent()->getEnrollmentData();
            $schemePaymentId = $observer->getEvent()->getSchemePaymentId();
            if (isset($enrollmentDetail['enrollment_no']) && $enrollmentDetail['enrollment_no'] != '') {
                $paymentDetails = $this->getPaymentDetailById($schemePaymentId);
                $transactionInfo = isset($paymentDetails['transaction_info']) ? json_decode($paymentDetails['transaction_info'], true) : [];
                $params = [
                    "enrNo" => $enrollmentDetail['enrollment_no'] ?? '',
                    "amount" => $paymentDetails['amount'],
                    "transId" => $transactionInfo['razorpay_payment_id'] ?? '',
                    "email" => $enrollmentDetail['email_id'] ?? '',
                    "channel" => "online"
                ];

                $response = $this->sendPaymentDetails->sendPaymentData($params);
                if (isset($response['status']) && $response['status']) {
                    $data = [
                        'reference_no' => $response['data'][0]['RecieptID'] ?? ''
                    ];
                    $this->updatePaymentReference($schemePaymentId, $data);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Scheme Login Api error' .$e->getMessage());
        }
    }

    public function getPaymentDetailById($paymentId)
    {
        if ($paymentId!='') {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()->from(
                ['sph' => self::SCHEME_PAYMENT_HISTORY_TABLE],
                ['*']
            )->where(
                "sph.id = ?",
                $paymentId
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

    public function updatePaymentReference($paymentId, $data)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::SCHEME_PAYMENT_HISTORY_TABLE);
        $where = ['id = ?' => $paymentId];
        $connection->update($tableName, $data, $where);
    }
}
