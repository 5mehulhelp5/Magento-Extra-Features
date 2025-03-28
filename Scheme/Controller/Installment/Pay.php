<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Installment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Razorpay\Api\Api;

class Pay extends Action
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
     * @var \KalyanUs\Scheme\Model\EnrollmentProcess
     */
    protected $enrollmentProcess;

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
     * @param \Magento\Customer\Model\Session $customersession
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess,
        \Magento\Customer\Model\Session $customersession,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme
    ) {
        $this->coreRegistry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->enrollmentProcess = $enrollmentProcess;
        $this->customersession =$customersession;
        $this->helperConfigScheme =$helperConfigScheme;
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
            if ($this->validateData()) {
                $post = $this->getRequest()->getPostValue();
                $paymentId=$this->enrollmentProcess->saveInstallmentPayment($post['enrollment_id']);
                if ($paymentId) {
                    $paymentInfo=$this->enrollmentProcess->getPaymentInfoById($paymentId);
                    $EnrollmentInfo=$this->enrollmentProcess->getEnrollmentInfoById($paymentInfo['enrollment_id']);
                    if (count($paymentInfo)>0) {
                        $razorpayapikey = $this->helperConfigScheme->getRazorPayApiKey();
                        $razorpayapikeysecret = $this->helperConfigScheme->getRazorPayApiSecretKey();

                        $transactionsreceipt = $paymentInfo['id'];
                        $transactionsreceipt = "SCHEME_".$transactionsreceipt;
                        $extraInfo = [
                            'name'=>$EnrollmentInfo['customer_name'],
                            'plan_no'=>$EnrollmentInfo['plan_no'],
                            'email'=>$EnrollmentInfo['email_id'],
                            'contact'=>$EnrollmentInfo['scheme_mobile_number'],
                            'enrollId'=>$paymentInfo['enrollment_id'],
                            'paymentId'=>$paymentInfo['id'],
                            'isNew_enrollment'=>0
                        ];
                        $amount = $paymentInfo['amount']*100;
                        $order_id = $this->getOrderId($amount, $transactionsreceipt, $extraInfo);
                        $razorpayData=[];
                        $currencyCode = $this->helperConfigScheme->getSelectedCurrency();
                        $razorpayData['currency']= $currencyCode;
                        $razorpayData['name']='Candere Scheme';
                        $razorpayData['description']=$EnrollmentInfo['scheme_name'];
                        $razorpayData['image']='';
                        $razorpayData['prefill'] = [
                            'name' => $EnrollmentInfo['customer_name'],
                            'email' => $EnrollmentInfo['email_id'],
                            'contact' => $EnrollmentInfo['scheme_mobile_number']
                        ];
                        $razorpayData['notes']=['notes'=>'Razorpay Corporate Office'];
                        $razorpayData['theme']=['color'=>'#3399cc'];
                        $dataRecords=[
                            'transactionsreceipt' => $transactionsreceipt ,
                            'order_id' => $order_id ,
                            'primaryLockerId' => $transactionsreceipt ,
                            'primaryLockerName' => $EnrollmentInfo['scheme_name'],
                            'razorpayapikey' => $razorpayapikey ,
                            'razorpayapikeysecret' => $razorpayapikeysecret,
                            'enroll' =>$EnrollmentInfo,
                            'razorpay'=>$razorpayData
                        ];
                        $data=['status'=>true,'data'=>$dataRecords];
                        return $result->setData($data);
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Something goes wrong. Please try again.')
                    );
                }
            }
            $data=['status'=>false,'message' => 'something went wrong. Please contact to admin.'];
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
     * Get razorpay order id
     *
     * @param float|int $amount
     * @param string $transactionsreceipt
     * @param array $extraInfo
     * @return int
     */
    public function getOrderId($amount, $transactionsreceipt, $extraInfo = [])
    {
        $key_id = $this->helperConfigScheme->getRazorPayApiKey();
        $key_secret = $this->helperConfigScheme->getRazorPayApiSecretKey();
        $currencyCode = $this->helperConfigScheme->getSelectedCurrency();

        $api = new Api($key_id, $key_secret);
        $orderData = [
            'receipt'         => $transactionsreceipt,
            'amount'          => $amount ,
            'currency'        => $currencyCode,
            'notes'           => $extraInfo,
            'partial_payment' => false
        ];
        $razorpayOrder = $api->order->create($orderData);
        $razorpayOrderId = $razorpayOrder['id'];
        return $razorpayOrderId;
    }

    /**
     * Validate data
     *
     * @return bool
     */
    public function validateData()
    {
        if ($this->getRequest()->getMethod()=='POST') {
            $post = $this->getRequest()->getPostValue();
            if ($post['enrollment_id']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Enrollment Id is Required Field.'));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request.'));
        }
        return true;
    }
}
