<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Block\Adminhtml;

class Enrollment extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'enrollment/enrollment.phtml';

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->_getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(KalyanUs\Scheme\Block\Adminhtml\Enrollment\Grid::class, 'pg.enrollment.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * Get add button options
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    /**
     * Get create url
     *
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'scheme/*/new'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
