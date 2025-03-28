<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Block\Adminhtml\Enrollment\Edit\Tab;

/**
 * Enrollment edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \KalyanUs\Scheme\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \KalyanUs\Scheme\Model\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \KalyanUs\Scheme\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \KalyanUs\Scheme\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('enrollment');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'email_id',
            'text',
            [
                'name' => 'email_id',
                'label' => __('Email Id'),
                'title' => __('Email Id'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'customer_name',
            'text',
            [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'scheme_mobile_number',
            'text',
            [
                'name' => 'scheme_mobile_number',
                'label' => __('Mobile Number'),
                'title' => __('Mobile Number'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'emi_amount',
            'text',
            [
                'name' => 'emi_amount',
                'label' => __('Emi amount'),
                'title' => __('Emi amount'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'scheme_name',
            'text',
            [
                'name' => 'scheme_name',
                'label' => __('Scheme Name'),
                'title' => __('Scheme Name'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'duration',
            'text',
            [
                'name' => 'duration',
                'label' => __('Duration'),
                'title' => __('Duration'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'text',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),

                'disabled' => $isElementDisabled
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get target option array
     *
     * @return array
     */
    public function getTargetOptionArray()
    {
        return [
            '_self' => "Self",
            '_blank' => "New Page",
        ];
    }
}
