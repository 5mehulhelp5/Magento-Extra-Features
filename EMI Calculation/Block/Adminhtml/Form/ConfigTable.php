<?php

namespace Prajwal\Assignment8\Block\Adminhtml\Form;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ConfigTable extends AbstractFieldArray
{
    /**
     * Prepare Table Layout
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('interest_rate', ['label' => __('Interest Rate'), 'class' => 'required-entry']);
        $this->addColumn('tenure', ['label' => __('Tenure (Months)'), 'class' => 'required-entry']);
        $this->addColumn('gender', ['label' => __('Gender'), 'class' => 'required-entry']);

        $this->_addAfter = false;

        $this->_addButtonLabel = __('Add New Row');
    }
}
