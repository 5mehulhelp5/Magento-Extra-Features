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

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \KalyanUs\Scheme\Model\enrollmentFactory
     */
    protected $_enrollmentFactory;

    /**
     * @var \KalyanUs\Scheme\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \KalyanUs\Scheme\Model\EnrollmentFactory $EnrollmentFactory
     * @param \KalyanUs\Scheme\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \KalyanUs\Scheme\Model\EnrollmentFactory $EnrollmentFactory,
        \KalyanUs\Scheme\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_enrollmentFactory = $EnrollmentFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_enrollmentFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'plan_no',
            [
                'header' => __('Plan No'),
                'index' => 'plan_no',
            ]
        );

        $this->addColumn(
            'email_id',
            [
                'header' => __('Email Id'),
                'index' => 'email_id',
            ]
        );

        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_name',
            ]
        );

        $this->addColumn(
            'scheme_mobile_number',
            [
                'header' => __('Mobile Number'),
                'index' => 'scheme_mobile_number',
            ]
        );

        $this->addColumn(
            'emi_amount',
            [
                'header' => __('Emi amount'),
                'index' => 'emi_amount',
            ]
        );

        $this->addColumn(
            'scheme_name',
            [
                'header' => __('Scheme Name'),
                'index' => 'scheme_name',
            ]
        );

        $this->addColumn(
            'duration',
            [
                'header' => __('Duration'),
                'index' => 'duration',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('scheme/*/index', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param \KalyanUs\Scheme\Model\enrollment|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'scheme/*/view',
            ['id' => $row->getId()]
        );
    }
}
