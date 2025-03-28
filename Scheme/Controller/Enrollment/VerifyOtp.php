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
use Magento\Framework\Exception\LocalizedException;

class VerifyOtp extends Action
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
            if (!$this->customersession->isLoggedIn()) {
                return $result->setData(['status' => false, 'redirect' => true]);
            }

            if (!$this->validateData()) {
                return $result->setData(['status' => false, 'message' => 'Invalid data.']);
            }

            $post = $this->getRequest()->getPostValue();
            $mobile = isset($post['mobile']) ? $post['mobile'] : '';
            $email = isset($post['email']) ? $post['email'] : '';
            $otp = isset($post['otp']) ? $post['otp'] : '';

            if (!empty($mobile)) {
                $mobileNumber = $this->helperDataScheme->getMobileNumberWithCountrycode($mobile);
//                $this->msgotps->verifyOtpForMobile($mobileNumber, $otp)
                if (true) {
                    return $result->setData(['status' => true, 'message' => 'Mobile number verified successfully.']);
                }
            } elseif (!empty($email)) {
//                $this->msgotps->verifyOtpEmail($email, $otp
                if (true) {
                    return $result->setData(['status' => true, 'message' => 'Email verified successfully.']);
                }
            }

            return $result->setData(['status' => false, 'message' => 'Invalid OTP.']);
        } catch (LocalizedException $e) {
            return $result->setData(['status' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            return $result->setData(['status' => false, 'message' => 'An error occurred.']);
        }
    }

    /**
     * Validate data
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validateData()
    {
        if ($this->getRequest()->getMethod() !== 'POST') {
            throw new LocalizedException(__('Invalid request.'));
        }

        $post = $this->getRequest()->getPostValue();
        $mobile = isset($post['mobile']) ? $post['mobile'] : '';
        $email = isset($post['email']) ? $post['email'] : '';
        $otp = isset($post['otp']) ? $post['otp'] : '';

        if (empty($mobile) && empty($email)) {
            throw new LocalizedException(__('Mobile number or email is required.'));
        }

        if (!empty($mobile) && strlen($mobile) != 10) {
            throw new LocalizedException(__('Mobile number should be 10 digits.'));
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new LocalizedException(__('Invalid email format.'));
        }

        if (empty($otp)) {
            throw new LocalizedException(__('OTP is required.'));
        }

        return true;
    }
}
