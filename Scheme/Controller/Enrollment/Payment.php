<?php
// @codingStandardsIgnoreFile
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Enrollment;

// if (class_exists('Razorpay\\Api\\Api')  === false) {
//    // require in case of zip installation without composer
//     require_once __DIR__ . "/../../Razorpay/Razorpay.php";
// }

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Razorpay\Api\Api;

class Payment extends Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \KalyanUs\Scheme\Model\SchemeQuoteProcess
     */
    protected $schemeQuoteProcess;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customersession;

    /**
     * @var \KalyanUs\Scheme\Helper\Config
     */
    protected $helperConfigScheme;

    /**
     * @var \KalyanUs\Scheme\Helper\Data
     */
    protected $helperDataScheme;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess
     * @param \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess
     * @param \Magento\Customer\Model\Session $customersession
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \KalyanUs\Scheme\Helper\DataLayer $helperDataLayer
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess,
        \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess,
        \Magento\Customer\Model\Session $customersession,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \KalyanUs\Scheme\Helper\DataLayer $helperDataLayer,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme
    ) {
        $this->coreRegistry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->enrollmentProcess = $enrollmentProcess;
        $this->schemeQuoteProcess = $schemeQuoteProcess;
        $this->customersession =$customersession;
        $this->helperConfigScheme =$helperConfigScheme;
        $this->helperDataLayer =$helperDataLayer;
        $this->helperDataScheme =$helperDataScheme;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $post = $this->getRequest()->getPostValue();

            $razorpayapikeysecret = $this->helperConfigScheme->getRazorPayApiSecretKey();
            if ($post['status']=='success') {
                $generated_signature = hash_hmac(
                    'sha256',
                    $post['order_id'] . "|" . $post['razorpay_payment_id'],
                    $razorpayapikeysecret
                );
                if ($generated_signature == $post['razorpay_signature']) {
                    $schemPaymentIdArr=explode('_', $post['transactionsreceipt']);
                    $schemPaymentId=end($schemPaymentIdArr);
                    if ($schemPaymentId!='') {
                        $this->enrollmentProcess->updateTransactionSuccessful(
                            $schemPaymentId,
                            $post['razorpay_order_id'],
                            $post['razorpay_signature'],
                            $post['razorpay_payment_id']
                        );
                        $enrollmentDetail=$this->enrollmentProcess->getEnrollmentDetailByPaymentId($schemPaymentId);
                        $nextInstallmentDetail = $this->enrollmentProcess
                            ->getNextInstallmentDetail($enrollmentDetail['id']);
                        if (isset($nextInstallmentDetail['due_date'])) {
                            $enrollmentDetail['next_due_date_format'] = $this->enrollmentProcess
                                ->getNextInstallmentDateFormat($nextInstallmentDetail['due_date']);
                        }
                        $this->schemeQuoteProcess->removeQuoteByCustomerId($enrollmentDetail['customer_id']);
                        $datalayer=$this->helperDataLayer->getEnrollment($enrollmentDetail, $nextInstallmentDetail);
                        $data=['status'=>true,'payment_done'=>true,'data'=>$enrollmentDetail,'datalayer'=>$datalayer];
                    } else {
                        $data=['status'=>true,'payment_done'=>false];
                    }
                } else {
                    $this->enrollmentProcess->updateTransactionFailure(
                        $schemPaymentId,
                        $post['razorpay_order_id'],
                        $post['razorpay_signature']
                    );
                    $data=[
                        'status'=>true,
                        'payment_done'=>false,
                        'datalayer'=>$this->getFailureDatalayer($schemPaymentId)
                    ];
                }
            } else {
                $schemPaymentIdArr=explode('_', $post['transactionsreceipt']);
                $schemPaymentId=end($schemPaymentIdArr);
                if ($schemPaymentId!='') {
                    $this->enrollmentProcess->updateTransactionFailure($schemPaymentId, '', '');
                    $data=[
                        'status'=>true,
                        'payment_failure'=>true,
                        'datalayer'=>$this->getFailureDatalayer($schemPaymentId)
                    ];
                }
            }
            return $result->setData($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $message = $e->getMessage();
            $data=['status'=>false,'message'=>$message];
            return $result->setData($data);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $data=['status'=>false,'message'=>$message];
            return $result->setData($data);
        }
    }

    /**
     * Get failure data layer
     *
     * @param int $schemPaymentId
     * @return array
     */
    private function getFailureDatalayer($schemPaymentId)
    {
        $enrollmentDetail=$this->enrollmentProcess->getEnrollmentDetailByPaymentId($schemPaymentId);
        $nomineeDetail=$this->enrollmentProcess->getNominee($enrollmentDetail['id']);
        return $this->helperDataLayer->getFailureEnrollment($enrollmentDetail, $nomineeDetail);
    }
}
