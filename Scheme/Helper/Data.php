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
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Data extends AbstractHelper
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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

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
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        TimezoneInterface $timezoneInterface,
        DateTime $date,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\UrlInterface $urlInterface,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->resource = $resource;
        $this->_cSession = $session;
        $this->urlInterface = $urlInterface;
        $this->helperConfigScheme =$helperConfigScheme;
        $this->regionFactory=$regionFactory;
        $this->countryFactory=$countryFactory;
        $this->regionCollection=$regionCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * Get retry url
     *
     * @return string
     */
    public function getRetryUrl()
    {
        return $this->urlInterface->getUrl('scheme/enrollment/');
    }

    /**
     * Get plan dashboard url
     *
     * @return string
     */
    public function getPlanbookDashboardUrl()
    {
        return $this->urlInterface->getUrl('scheme/enrollment/planbook');
    }

    /**
     * Get new enrollment url
     *
     * @return string
     */
    public function getNewEnrollmentUrl()
    {
        return $this->urlInterface->getUrl('scheme/enrollment/');
    }

    /**
     * Get term and condition url
     *
     * @return string
     */
    public function getTermAndConditionUrl()
    {
        return rtrim($this->urlInterface->getUrl('smart-jewellery-plan-terms.html'), '/');
    }

    /**
     * Get enrollment url
     *
     * @param int $id
     * @return string
     */
    public function getViewEnrollmentUrl($id)
    {
        if ($id!='') {
            return $this->urlInterface->getUrl('scheme/enrollment/view', ['_query'=>['id'=>$id]]);
        } else {
            return $this->getPlanbookDashboardUrl();
        }
    }

    /**
     * Get default date
     *
     * @return string
     */
    public function getDefaultDate()
    {
        return $this->timezoneInterface->date(new \DateTime($this->timezoneInterface->date()->format('Y-m-d h:i:s')))
            ->format('Y-m-t 23:59:59');
    }

    /**
     * Get month from datetime
     *
     * @param string $datetime
     * @return string
     */
    public function getMonth($datetime = '')
    {
        if ($datetime!='') {
            return $this->timezoneInterface->date(new \DateTime($datetime))->format('M-Y');
        }
        return $this->timezoneInterface->date()->format('M-Y');
    }

    /**
     * Get payment date
     *
     * @param string $datetime
     * @return string
     */
    public function getPaymentDate($datetime = '')
    {
        if ($datetime!='') {
            return $this->timezoneInterface->date(new \DateTime($datetime))->format('Y-m-d h:i:s');
        }
        return $this->timezoneInterface->date()->format('Y-m-d h:i:s');
    }

    /**
     * Get due date
     *
     * @param string $datetime
     * @return string
     */
    public function getDueDate($datetime = '')
    {
        if ($datetime!='') {
            return $this->timezoneInterface->date(new \DateTime($datetime))->format('Y-m-t 23:59:59');
        }
        return $this->timezoneInterface->date()->format('Y-m-t 23:59:59');
    }

    /**
     * Get nationality options
     *
     * @return array
     */
    public function getNationalityOptions()
    {
        return ['US','Indian', 'Other'];
    }

    /**
     * Get relation ship options
     *
     * @return array
     */
    public function getRelationshipOptions()
    {
        return $relationshipArr=['father','mother','spouse','son','daughter','brother'];
    }

    /**
     * Get state list option
     *
     * @param string $countryCode
     * @return array
     */
    public function getStateListOption($countryCode = '')
    {
        $countryCode = $this->helperConfigScheme->getSelectedCountry();
        $regionData = $this->countryFactory->create()->loadByCode($countryCode)->getRegions()->loadData()->toArray();
        $temp = array_unique(array_column($regionData['items'], 'default_name'));
        $stateOption = array_intersect_key($regionData['items'], $temp);
        array_multisort(array_column($stateOption, 'default_name'), SORT_ASC, $stateOption);
        return $stateOption;
    }

    /**
     * Get region id
     *
     * @param string $region
     * @return int
     */
    public function getRegionId($region)
    {
        if ($region) {
            $regionCode = $this->regionCollection->addRegionNameFilter($region)->getFirstItem()->toArray();
            return $regionCode['region_id'];
        }
        return $regionCode;
    }

    /**
     * Get state name by region id
     *
     * @param int $regionId
     * @return string
     */
    public function getStateNameByRegionId($regionId)
    {
        $region = $this->regionFactory->create()->load($regionId);
        if ($region && $region->getData('default_name')) {
            return $region->getData('default_name');
        }
        return '';
    }

    /**
     * Get view page date format
     *
     * @param string $datetime
     * @return string
     */
    public function getViewPageDateForamte($datetime = '')
    {
        if ($datetime!='') {
            return $this->date->date('d M Y', strtotime($datetime));
        }
        return '';
    }

    /**
     * Get view page enrollment date format
     *
     * @param string $datetime
     * @return string
     */
    public function getViewPageEnrollmentDateForamt($datetime = '')
    {
        if ($datetime!='') {
            return $this->date->date('d M Y', strtotime($datetime));
        }
        return '';
    }

    /**
     * Get mobile number with country code
     *
     * @param string|int $number
     * @return string|int
     */
    public function getMobileNumberWithCountrycode($number)
    {
        $countryCode = $this->helperConfigScheme->getSelectedCountry() == 'US' ? '1' : '91';
        if ($number!='') {
            if (substr($number, 0, 2) == $countryCode && strlen($number) > 10) {
                return $number;
            } else {
                return $countryCode.$number;
            }
        }
        return '';
    }

    /**
     * Get mobile number without country code
     *
     * @param int $number
     * @return string|int
     */
    public function getMobileNumberWithOUTCountrycode($number)
    {
        $countryCode = $this->helperConfigScheme->getSelectedCountry() == 'US' ? '1' : '91';
        if ($number!='') {
            if (substr($number, 0, 2) == $countryCode && strlen($number)) {
                return substr($number, 2);
            } else {
                return $number;
            }
        }
        return '';
    }

    /**
     * Validate emi amount for scheme
     *
     * @param int $duration
     * @param float|int $emiAmount
     * @return bool
     */
    public function validateEmiAmountForScheme($duration, $emiAmount)
    {
        if ($duration!='' && $emiAmount!='') {
            list($minAmount,$maxAmount)=$this->getEmiSchemeRange($duration);
            if ($emiAmount>=$minAmount && $emiAmount<=$maxAmount) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get emi scheme duration
     *
     * @param string $duration
     * @return array
     */
    public function getEmiSchemeRange($duration)
    {
        return $this->helperConfigScheme->getEmiSchemeRange($duration);
    }
}
