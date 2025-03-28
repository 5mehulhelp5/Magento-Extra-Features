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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Index extends \Magento\Framework\View\Element\Template
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
     * @var \KalyanUs\Scheme\Model\SchemeQuoteProcess
     */
    protected $schemeQuoteProcess;

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
     * @param \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->localeFormat = $localeFormat;
        $this->schemeQuoteProcess = $schemeQuoteProcess;
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
     * Get quote data
     *
     * @return array
     */
    public function getQuote()
    {
        return $this->schemeQuoteProcess->getFullQuote();
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function optionArr()
    {
        $nationalityArr=$this->helperDataScheme->getNationalityOptions();
        $relationshipArr=$this->helperDataScheme->getRelationshipOptions();
        $usuallySelectedEmiAmount=$this->helperConfigScheme->getCustomerUsuallySelectedEmi();

        $stateOption=$this->helperDataScheme->getStateListOption();

        return [
            'stateOption'=>$stateOption,
            'relationshipOption'=>$relationshipArr,
            'nationalityOption'=>$nationalityArr,
            'usuallySelectedEmiAmount'=>$usuallySelectedEmiAmount
        ];
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
     * Get scheme data
     *
     * @return array
     */
    public function getSchemeData()
    {
        $durationOptionList=[];
        foreach ($this->helperConfigScheme->getSchemeData() as $key => $valueScheme) {
            $durationOptionList[]=['label'=>$valueScheme['name'],'value'=>$valueScheme['duration']];
        }
        $schemeData=[];
        $schemeData['schemes']=$this->helperConfigScheme->getSchemeData();
        $schemeData['defaultDuration']=$this->helperConfigScheme->getDefaultScheme();
        $schemeData['durationOption']=$durationOptionList;
        $schemeData['urls']=$this->getUrls();
        $schemeData['slideroption']=$this->helperConfigScheme->getSliderOption();
        return $schemeData;
    }

    /**
     * Get urls
     *
     * @return void
     */
    public function getUrls()
    {
        return [
            'retryUrl'=>$this->helperDataScheme->getRetryUrl(),
            'termAndConditionUrl'=>$this->helperDataScheme->getTermAndConditionUrl(),
            'planbookUrl'=>$this->helperDataScheme->getPlanbookDashboardUrl()
        ];
    }

    /**
     * Scheme enabled or not
     *
     * @return boolean
     */
    public function isEnabledScheme()
    {
        return $this->helperConfigScheme->isEnabled();
    }
}
