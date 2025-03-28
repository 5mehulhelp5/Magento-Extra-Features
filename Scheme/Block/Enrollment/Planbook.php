<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Block\Enrollment;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Planbook extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \KalyanUs\Scheme\Model\EnrollmentProcess
     */
    protected $enrollmentProcess;

    /**
     * @var \KalyanUs\Scheme\Helper\Data
     */
    protected $helperDataScheme;

    /**
     * @var \KalyanUs\Scheme\Helper\Config
     */
    protected $helperConfigScheme;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \KalyanUs\Scheme\Model\EnrollmentProcess $enrollmentProcess,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        //$this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->localeFormat = $localeFormat;
        $this->enrollmentProcess = $enrollmentProcess;
        $this->helperDataScheme = $helperDataScheme;
        $this->helperConfigScheme = $helperConfigScheme;

        parent::__construct($context, $data);
    }

    /**
     * Return the Customer given the customer Id stored in the session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * Retrieve the Url for editing the customer's account.
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/edit', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for editing the specified address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressEditUrl($address)
    {
        return $this->_urlBuilder->getUrl(
            'customer/address/edit',
            ['_secure' => true, 'id' => $address->getId()]
        );
    }

    /**
     * Get price format
     *
     * @return array
     */
    public function getPriceFormat()
    {
        return $this->localeFormat->getPriceFormat();
    }

    /**
     * Get url lists
     *
     * @return array
     */
    public function getUrls()
    {
        return [
            'retryUrl'=>$this->helperDataScheme->getRetryUrl(),
            'planbookUrl'=>$this->helperDataScheme->getPlanbookDashboardUrl()
        ];
    }

    /**
     * Get new enrollment url
     *
     * @return string
     */
    public function getNewEnrollmentUrl()
    {
        return $this->helperDataScheme->getNewEnrollmentUrl();
    }

    /**
     * Get list of enrollment
     *
     * @return array
     */
    public function getListOfEnrollment()
    {
        return $this->enrollmentProcess->getListOfEnrollmentOfCustomer($this->customerSession->getCustomerId());
    }

    /**
     * Get term condition url
     *
     * @return string
     */
    public function getTermConditionUrl()
    {
        return $this->helperDataScheme->getTermAndConditionUrl();
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSchemeApiEnabled()
    {
        return $this->helperConfigScheme->isSchemeApiEnabled();
    }
}
