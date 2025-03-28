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

class DataLayer extends AbstractHelper
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
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $date
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     * @param \KalyanUs\Scheme\Model\EnrollmentProcess $schemeEnrollmentProcess
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
        \KalyanUs\Scheme\Helper\Data $helperDataScheme,
        \KalyanUs\Scheme\Model\EnrollmentProcess $schemeEnrollmentProcess,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->resource = $resource;
        $this->_cSession = $session;
        $this->urlInterface = $urlInterface;
        $this->helperConfigScheme =$helperConfigScheme;
        $this->helperDataScheme =$helperDataScheme;
        $this->schemeEnrollmentProcess = $schemeEnrollmentProcess;
        $this->storeManager = $storeManager;
    }

    /**
     * Get enrollment
     *
     * @param array $enrollment
     * @param array $nexInstallment
     * @return array
     */
    public function getEnrollment($enrollment, $nexInstallment = [])
    {
        $dataLayer=[];
        $dataLayer['customer_name']=$enrollment['customer_name'];
        $dataLayer['email_id']=$enrollment['email_id'];
        $dataLayer['scheme_mobile_number']=$enrollment['scheme_mobile_number'];
        $dataLayer['customer_id']=$enrollment['customer_id'];
        $dataLayer['created_at']=$enrollment['created_at'];
        $dataLayer['address']=$enrollment['address'];
        $dataLayer['city']=$enrollment['city'];
        $dataLayer['state']=$this->helperDataScheme->getStateNameByRegionId($enrollment['state']);
        $dataLayer['pincode']=$enrollment['pincode'];
        $dataLayer['scheme_id']=$enrollment['id'];
        $dataLayer['status']=$enrollment['status'];
        $dataLayer['duration']=$enrollment['duration'];
        $dataLayer['maturity_date']=$enrollment['maturity_date'];
        $dataLayer['total_installment_remaining'] = $this->schemeEnrollmentProcess
            ->getCountOfPendingInstallmentByEnrollmentId($enrollment['id']);
        $dataLayer['emi_amount']=(int)number_format($enrollment['emi_amount'], 0, '', '');
        $dataLayer['plan_no']=$enrollment['plan_no'];
        if ($nexInstallment) {
            $dataLayer['next_installment_date']=isset($nexInstallment['due_date']) ? $nexInstallment['due_date'] : '';
        }
        return $dataLayer;
    }

    /**
     * Get failure enrollment details
     *
     * @param array $enrollment
     * @param array $nomineeDetail
     * @return array
     */
    public function getFailureEnrollment($enrollment, $nomineeDetail = [])
    {
        $dataLayer=[];
        if ($enrollment) {
            $dataLayer['customer_name']=$enrollment['customer_name'];
            $dataLayer['email_id']=$enrollment['email_id'];
            $dataLayer['phone']=$enrollment['scheme_mobile_number'];
            $dataLayer['created_at']=$enrollment['created_at'];
            $dataLayer['address']=$enrollment['address'];
            $dataLayer['city']=$enrollment['city'];
            $dataLayer['state']=$this->helperDataScheme->getStateNameByRegionId($enrollment['state']);
            $dataLayer['pincode']=$enrollment['pincode'];
            $dataLayer['scheme_id']=$enrollment['id'];
            $dataLayer['status']=$enrollment['status'];
            $dataLayer['duration']=$enrollment['duration'];
            $dataLayer['monthly_pay_amount']=(int)number_format($enrollment['emi_amount'], 0, '', '');
            list($benefitAmt,$benefitpercentage)=$this->schemeEnrollmentProcess->getBenefitAmountAndPercentage(
                $enrollment['duration'],
                $enrollment['duration'],
                $enrollment['emi_amount']
            );
            $dataLayer['benefit_amount']=$benefitAmt;
            $dataLayer['redeemable_amount']=(($enrollment['emi_amount']*$enrollment['duration'])+$benefitAmt);
            if ($nomineeDetail) {
                $dataLayer['nominee_name']=isset($nomineeDetail['nominee_name'])
                    ? $nomineeDetail['nominee_name'] : '';
                $dataLayer['nominee_relationship']=isset($nomineeDetail['nominee_relationship'])
                    ? $nomineeDetail['nominee_relationship'] : '';
                $dataLayer['nominee_nationality']=isset($nomineeDetail['nominee_nationality'])
                    ? $nomineeDetail['nominee_nationality'] : '';
                $dataLayer['nominee_phone']=isset($nomineeDetail['nominee_mobilenumber'])
                    ?$nomineeDetail['nominee_mobilenumber']:'';
            }
        }
        return $dataLayer;
    }
}
