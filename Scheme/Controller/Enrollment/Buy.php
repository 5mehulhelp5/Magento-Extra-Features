<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Enrollment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Razorpay\Api\Api;

class Buy extends Action
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
        \KalyanUs\Scheme\Helper\Data $helperDataScheme
    ) {
        $this->coreRegistry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->enrollmentProcess = $enrollmentProcess;
        $this->schemeQuoteProcess = $schemeQuoteProcess;
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
                $this->schemeQuoteProcess->saveQuote($post['scheme']);
                $quote=$this->schemeQuoteProcess->getQuote();
                if ($quote!=false) {
                    $enrollmentInfo=$this->enrollmentProcess->convertQuoteToEnrollment($quote);
                    if ($enrollmentInfo['paymentId']=='' || $enrollmentInfo['enrollment_id']=='') {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Something goes wrong. Please try again.')
                        );
                    }
                    $paymentInfo=$this->enrollmentProcess->getPaymentInfoById($enrollmentInfo['paymentId']);
                    $EnrollmentInfo=$this->enrollmentProcess->getEnrollmentInfoById($enrollmentInfo['enrollment_id']);

                    if (count($paymentInfo)>0) {
                        $razorpayapikey = $this->helperConfigScheme->getRazorPayApiKey();
                        $razorpayapikeysecret = $this->helperConfigScheme->getRazorPayApiSecretKey();

                        $transactionsreceipt = $paymentInfo['id'];
                        $primaryLockerId = $transactionsreceipt;
                        $primaryLockerName=$EnrollmentInfo['scheme_name'];
                        $transactionsreceipt = "SCHEME_".$transactionsreceipt; //return cnd5
                        $extraInfo=[
                            'name'=>$EnrollmentInfo['customer_name'],
                            'plan_no'=>$EnrollmentInfo['plan_no'],
                            'email'=>$EnrollmentInfo['email_id'],
                            'contact'=>$EnrollmentInfo['scheme_mobile_number'],
                            'enrollId'=>$paymentInfo['enrollment_id'],
                            'paymentId'=>$paymentInfo['id'],
                            'isNew_enrollment'=>1
                        ];
                        $amount = $paymentInfo['amount']*100;
                        $order_id = $this->getOrderId($amount, $transactionsreceipt, $extraInfo);
                        $razorpayData=[];
                        $currencyCode = $this->helperConfigScheme->getSelectedCurrency();
                        $razorpayData['currency']= $currencyCode;
                        $razorpayData['name']='Kalyan Scheme';
                        $razorpayData['description']=$EnrollmentInfo['scheme_name'];
                        $razorpayData['image']='';
                        $razorpayData['prefill']=[
                            'name'=>$EnrollmentInfo['customer_name'],
                            'email'=>$EnrollmentInfo['email_id'],
                            'contact'=>$EnrollmentInfo['scheme_mobile_number']
                        ];
                        $razorpayData['notes']=['notes'=>'Razorpay Corporate Office'];
                        $razorpayData['theme']=['color'=>'#3399cc'];
                        $dataRecords=[
                            'transactionsreceipt' => $transactionsreceipt ,
                            'order_id' => $order_id ,
                            'primaryLockerId' => $primaryLockerId ,
                            'primaryLockerName' => $primaryLockerName,
                            'razorpayapikey' => $razorpayapikey ,
                            'razorpayapikeysecret' => $razorpayapikeysecret,
                            'enroll' =>$EnrollmentInfo,
                            'razorpay'=>$razorpayData
                        ];
                        $data=['status'=>true,'data'=>$dataRecords];
                        return $result->setData($data);
                    }
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
     * Get order id
     *
     * @param float $amount
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
            if ($post['scheme']['emi_amount']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Emi Amount is Required Field.'));
            }
            if ($post['scheme']['duration']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Duration is Required Field.'));
            }
            if ($post['scheme']['email_id']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Email Id is Required Field.'));
            }
            if ($post['scheme']['customer_name']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Customer Name is Required Field.'));
            }
            if ($post['scheme']['scheme_mobile_number']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Mobile Number is Required Field.'));
            } else {
                if (strlen($post['scheme']['scheme_mobile_number'])>10) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Mobile Number should be 10 digit.'));
                }
            }
            if ($post['scheme']['address']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Address is Required Field.'));
            }
            if ($post['scheme']['pincode']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Pincode is Required Field.'));
            }
            if ($post['scheme']['state']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('State is Required Field.'));
            }
            if ($post['scheme']['city']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('City is Required Field.'));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request.'));
        }
        return true;
    }
}
