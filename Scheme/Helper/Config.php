<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Config extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_cSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * Constructor
     *
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $date
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        TimezoneInterface $timezoneInterface,
        DateTime $date,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->resource = $resource;
        $this->_cSession = $session;
        $this->storeManager = $storeManager;
    }

    /**
     * Get config data
     *
     * @param string $field
     * @param string $store
     * @return mixed
     */
    public function getConfigData($field, $store = null)
    {
        $store = $this->storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue($field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $result;
    }

    /**
     * Scheme enabled or not
     *
     * @param int $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        if ($this->getConfigData('scheme/general/enable', $storeId)==1) {
            return true;
        }
        return false;
    }

    /**
     * Get razor pay api key
     *
     * @param int $storeId
     * @return mixed
     */
    public function getRazorPayApiKey($storeId = null)
    {
        return $this->getConfigData('scheme/payment/razorpay_api_key', $storeId);
    }

    /**
     * Get razor pay api secret key
     *
     * @param int $storeId
     * @return mixed
     */
    public function getRazorPayApiSecretKey($storeId = null)
    {
        return $this->getConfigData('scheme/payment/razorpay_api_secret_key', $storeId);
    }

    // phpcs:ignore
    public function getSchemeName()
    {
        //return 'scheme 6 month';
    }

    /**
     * Get list of scheme
     *
     * @param int $storeId
     * @return array
     */
    public function getListOfScheme($storeId = null)
    {
        $schemeData=[];
        $schemes=$this->getConfigData('scheme/detail', $storeId);
        usort($schemes, function($a, $b) {
            return $a['duration'] <=> $b['duration'];
        });
        if($schemes){
            foreach($schemes as $scheme){
                if(isset($scheme['is_active']) && $scheme['is_active']==1){
                    $benefit_percentage=0;
                    $duration=$scheme['duration'];
                    if(isset($scheme['benefit_percentage_monthly']) && isset($scheme['duration']))
                    {
                        $benefitPerList=json_decode($scheme['benefit_percentage_monthly'],true);
                        $ListBenefit=array_column($benefitPerList,'benefit_percentage','no_of_month');
                        if(isset($scheme['duration'])){
                            if(isset($ListBenefit[$scheme['duration']])){
                                $benefit_percentage=$ListBenefit[$scheme['duration']];
                            }
                        }
                    }
                    $schemeData[]=[
                        'name'=>$scheme['name'],
                        'benefit_percentage'=>$benefit_percentage,
                        'duration'=>$duration,
                        'is_default'=> isset($scheme['is_default']) ? $scheme['is_default'] : 0
                    ];
                }
            }
        }
        return $schemeData;
    }

    /**
     * Get scheme data
     *
     * @param int $storeId
     * @return array
     */
    public function getSchemeData($storeId = null)
    {
        $schemeData=[];
        $schemes=$this->getListOfScheme($storeId);
        if ($schemes) {
            foreach ($schemes as $scheme) {
                $schemeData[] = [
                    'name' => $scheme['name'],
                    'benefit_percentage' => $scheme['benefit_percentage'],
                    'duration'=>$scheme['duration']
                ];
            }
        }
        return $schemeData;
    }

    /**
     * Allow installment payment forcefully
     *
     * @param int $storeId
     * @return array
     */
    public function forcefullyAllowInstallmentPayment($storeId = null)
    {
        $emailForcefullyInstallment=$this->getConfigData(
            'scheme/payment/email_for_forcefully_allow_installment_payment',
            $storeId
        );
        if ($emailForcefullyInstallment!='') {
            return explode(',', $emailForcefullyInstallment);
        }
        return [];
    }

    /**
     * Candere email id allowed
     *
     * @param int $storeId
     * @return boolean
     */
    public function isAllowCandereEmailId($storeId = null)
    {
        if ($this->getConfigData('scheme/payment/allow_only_candere_email', $storeId)==1) {
            return true;
        }
        return false;
    }

    /**
     * Get customer selected emi
     *
     * @param int $storeId
     * @return array
     */
    public function getCustomerUsuallySelectedEmi($storeId = null)
    {
        $customerselectedUsuallyEmi=$this->getConfigData('scheme/general/customer_usulllay_selected_emi', $storeId);
        if ($customerselectedUsuallyEmi!='') {
            return array_column(json_decode($customerselectedUsuallyEmi, true), 'emi');
        }
        return [];
    }

    /**
     * Get default scheme
     *
     * @param int $storeId
     * @return int
     */
    public function getDefaultScheme($storeId = null)
    {
        $schemes=$this->getListOfScheme($storeId);
        if ($schemes) {
            foreach ($schemes as $scheme) {
                if ((isset($scheme['is_default']) && $scheme['is_default']==1)) {
                    return $scheme['duration'];
                }
            }
        }
        return 0;
    }

    /**
     * Get slider option
     *
     * @param int $storeId
     * @return array
     */
    public function getSliderOption($storeId = null)
    {
        return [
            'min'=>(int)$this->getConfigData('scheme/general/min_scheme_amount', $storeId),
            'max'=>(int)$this->getConfigData('scheme/general/max_scheme_amount', $storeId),
            'range'=>'min',
            'step'=>100
        ];
    }

    /**
     * Get scheme name by duration
     *
     * @param int $duration
     * @return string
     */
    public function getSchemeNameByDuration($duration = '')
    {
        if ($duration!='') {
            foreach ($this->getSchemeData() as $key => $scheme) {
                if ($scheme['duration']==$duration) {
                    return $scheme['name'];
                }
            }
        }
        return '';
    }

    /**
     * Get benefit percentage list
     *
     * @param int $duration
     * @return array
     */
    public function getBenefitPercentageList($duration)
    {
        $benefitArr=[];
        if ($duration!='') {
            $benefitlist=$this->getConfigData('scheme/detail/month_'.$duration.'/benefit_percentage_monthly');
            if ($benefitlist) {
                $benefitArr=array_column(json_decode($benefitlist, true), 'benefit_percentage', 'no_of_month');
            }
        }
        return $benefitArr;
    }

    /**
     * Get month for preclouser
     *
     * @param int $duration
     * @return int
     */
    public function getMonthForpreclouser($duration)
    {
        if ($duration!='') {
            $allowPreclosingMonth=$this->getConfigData(
                'scheme/detail/month_'.$duration.'/allow_preclosing_scheme_after_month'
            );
            if ($allowPreclosingMonth!='') {
                return $allowPreclosingMonth;
            }
        }
        return 0;
    }

    /**
     * Get scheme detail
     *
     * @param int $duration
     * @return array
     */
    public function getSchemeDetail($duration)
    {
        if ($duration!='') {
            $schemeDetail=$this->getConfigData('scheme/detail/month_'.$duration);
            if ($schemeDetail) {
                return $schemeDetail;
            }
        }
        return [];
    }

    /**
     * Get emi scheme range
     *
     * @param int $duration
     * @return array
     */
    public function getEmiSchemeRange($duration)
    {
        $schemeDetail=$this->getSchemeDetail($duration);
        $minAmt=$maxAmt=0;
        if ($schemeDetail) {
            $minAmt=$schemeDetail['min_amount'];
            $maxAmt=$schemeDetail['max_amount'];
        }
        return [$minAmt,$maxAmt];
    }

    /**
     * Get enroll prefix
     *
     * @param int $duration
     * @return string
     */
    public function getEnrollPrefix($duration)
    {
        if ($duration!='') {
            $prefix=$this->getConfigData('scheme/detail/month_'.$duration.'/enroll_prefix');
            if ($prefix!='') {
                return $prefix;
            }
        }
        return '';
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSelectedCurrency()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue('scheme/general/currency', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSelectedCountry()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue('scheme/general/country', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSchemeApiEnabled()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return (bool) $this->scopeConfig->getValue('scheme/api/enable',ScopeInterface::SCOPE_STORE, $storeId);
    }
}
