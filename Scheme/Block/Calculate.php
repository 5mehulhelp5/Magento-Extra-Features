<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Block;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Calculate extends \Magento\Framework\View\Element\Template
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
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
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
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        //$this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->localeFormat = $localeFormat;
        $this->schemeQuoteProcess = $schemeQuoteProcess;
        $this->helperDataScheme = $helperDataScheme;
        $this->helperConfigScheme = $helperConfigScheme;
        $this->currencyFactory = $currencyFactory;
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
     * Get price format list
     *
     * @return array
     */
    public function getPriceFormat()
    {
        $currencyCode = $this->helperConfigScheme->getSelectedCurrency();
        $currency = $this->currencyFactory->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();

        $priceFormatList=[];
        $priceFormatList['pattern']=$currencySymbol.'%s';
        $priceFormatList['precision']='2';
        $priceFormatList['requiredPrecision']='2';
        $priceFormatList['requiredPrecision']='';
        $priceFormatList['decimalSymbol']='.';
        $priceFormatList['groupSymbol']=',';
        $priceFormatList['groupLength']='3';
        $priceFormatList['integerRequired']='';
        return $priceFormatList;
        //return $this->localeFormat->getPriceFormat(null,'INR');
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
            $benefitPercentageList=$this->helperConfigScheme->getBenefitPercentageList($valueScheme['duration']);
            $valueScheme['benefits']=$benefitPercentageList;
            list($minAmt,$maxAmt)=$this->helperConfigScheme->getEmiSchemeRange($valueScheme['duration']);
            $valueScheme['minAmt']=$minAmt;
            $valueScheme['maxAmt']=$maxAmt;
            $durationOptionList[]=$valueScheme;
        }
        $schemeData=[];
        $schemeData['defaultDuration']=$this->helperConfigScheme->getDefaultScheme();
        $schemeData['schemes']=$durationOptionList;
        $schemeData['usuallySelectedEmi']=$this->helperConfigScheme->getCustomerUsuallySelectedEmi();
        return $schemeData;
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

    /**
     * Get scheme list
     *
     * @return array
     */
    public function getSchemeList()
    {
        $durationOptionList=[];
        foreach ($this->helperConfigScheme->getSchemeData() as $key => $valueScheme) {
            $benefitPercentageList=$this->helperConfigScheme->getBenefitPercentageList($valueScheme['duration']);
            $valueScheme['benefits']=$benefitPercentageList;
            list($minAmt,$maxAmt)=$this->helperConfigScheme->getEmiSchemeRange($valueScheme['duration']);
            $valueScheme['minAmt']=$minAmt;
            $valueScheme['maxAmt']=$maxAmt;
            $durationOptionList[]=$valueScheme;
        }
        return $durationOptionList;
    }

    /**
     * Get customer selected emi
     *
     * @return mixed
     */
    public function getCustomerUsuallySelectedEmi()
    {
        return $this->helperConfigScheme->getCustomerUsuallySelectedEmi();
    }

    /**
     * Get default schema duration
     *
     * @return int
     */
    public function getDefaultSchemeDuration()
    {
        return $this->helperConfigScheme->getDefaultScheme();
    }
}
