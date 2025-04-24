<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Block\Adminhtml\Enrollment;

use KalyanUs\Scheme\Model\EnrollmentProcess;
use Magento\Backend\Block\Widget\Context;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Registry;

class View extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var RegionFactory
     */
    private RegionFactory $regionFactory;

    /**
     * @param Context $context
     * @param EnrollmentProcess $enrollmentProcess
     * @param RegionFactory $regionFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        EnrollmentProcess $enrollmentProcess,
        RegionFactory $regionFactory,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->enrollmentProcess = $enrollmentProcess;
        $this->_coreRegistry = $registry;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Get enrollment detail
     *
     * @return object
     */
    public function getEnrollmentDetail()
    {
        $id=$this->getEnrollmentId();
        return $this->enrollmentProcess->getEnrollmentInfoById($id);
    }

    /**
     * Get nominee detail
     *
     * @return mixed
     */
    public function getNomineeDetail()
    {
        $id=$this->getEnrollmentId();
        return $this->enrollmentProcess->getNominee($id);
    }

    /**
     * Get enrollment id
     *
     * @return int
     */
    public function getEnrollmentId()
    {
        return $this->_coreRegistry->registry('view_enrollment')->getId();
    }

    /**
     * Get status label
     *
     * @param string $code
     * @return string
     */
    public function getStatusLabel($code)
    {
        return $this->enrollmentProcess->getStatusLabel($code);
    }

    /**
     * Get total amount paid
     *
     * @return float|int
     */
    public function getTotalAmountPaid()
    {
        $id=$this->getEnrollmentId();
        return $this->enrollmentProcess->getTotalAmountPaid($id);
    }

    /**
     * Get coupon of paid installment by enrollment id
     *
     * @return int
     */
    public function getCountOfPaidInstallmentByEnrollmentId()
    {
        $id = $this->getEnrollmentId();
        return $this->enrollmentProcess->getCountOfPaidInstallmentByEnrollmentId($id);
    }

    /**
     * Get all installment detail
     *
     * @return array
     */
    public function getAllInstallmentDetail()
    {
        $id=$this->getEnrollmentId();
        return $this->enrollmentProcess->getAllInstallmentRecords($id);
    }

    /**
     * Return the Region Name
     *
     * @param $regionId
     * @return string
     */
    public function getRegionNameById($regionId)
    {
        $region = $this->regionFactory->create()->load($regionId);
        return $region->getId() ? $region->getName() : $regionId;
    }
}
