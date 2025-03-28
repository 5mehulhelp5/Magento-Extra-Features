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

class SendOtp extends Action
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
     * @param \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess
     * @param \Magento\Customer\Model\Session $customersession
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     * @param \Candere\Msgotp\Helper\MSGotp $msgotps
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess,
        \Magento\Customer\Model\Session $customersession,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme,
        \Candere\Msgotp\Helper\MSGotp $msgotps
    ) {
        $this->coreRegistry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->schemeQuoteProcess = $schemeQuoteProcess;
        $this->customersession =$customersession;
        $this->helperConfigScheme =$helperConfigScheme;
        $this->helperDataScheme =$helperDataScheme;
        $this->msgotps =$msgotps;
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
            if ($this->customersession->isLoggedIn()) {
                if ($this->validateData()) {
                    $post = $this->getRequest()->getPostValue();
                    $otp = rand(100000, 999999);

                    $email = isset($post['email']) ? $post['email'] : '';
                    $mobile = isset($post['mobile']) ? $post['mobile'] : '';

                    if (!empty($mobile)) {
                        // Send OTP to mobile
                        $mobileNumber = $this->helperDataScheme->getMobileNumberWithCountrycode($mobile);
                        if ($this->msgotps->sendOtpForMobile($mobileNumber, $otp)) {
                            $data = ['status' => true, 'message' => 'OTP sent successfully to mobile.'];
                        } else {
                            $data = ['status' => false, 'message' => 'Something went wrong. Please contact admin.'];
                        }
                    } elseif (!empty($email)) {
                        // Send OTP to email
                        if ($this->msgotps->sendOtpEmail($email, $otp)) {
                            $data = ['status' => true, 'message' => 'OTP sent successfully to email.'];
                        } else {
                            $data = ['status' => false, 'message' => 'Something went wrong. Please contact admin.'];
                        }
                    } else {
                        $data = ['status' => false, 'message' => 'Email or mobile is required.'];
                    }
                    return $result->setData($data);
                }
            } else {
                $data = ['status' => false, 'redirect' => true];
            }
            return $result->setData($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $message = $e->getMessage();
            $data = ['status' => false, 'message' => $message];
            return $result->setData($data);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $data = ['status' => false, 'message' => $message];
            return $result->setData($data);
        }
    }

    /**
     * Validate data
     *
     * @return bool
     */
    public function validateData()
    {
        if ($this->getRequest()->getMethod() == 'POST') {
            $post = $this->getRequest()->getPostValue();
            $mobile = isset($post['mobile']) ? $post['mobile'] : '';
            $email = isset($post['email']) ? $post['email'] : '';

            if (empty($mobile) && empty($email)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Mobile number or email is required.'));
            }

            if (!empty($mobile) && strlen($mobile) != 10) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Mobile number should be 10 digits.'));
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid email format.'));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid request.'));
        }
        return true;
    }
}
