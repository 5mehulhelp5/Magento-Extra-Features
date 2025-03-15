<?php
namespace Casio\LotterySale\Plugin\Block\Widget;

use Casio\LotterySale\Helper\Data as HelperData;
use Magento\ImportExport\Model\Import;

/**
 * Class FormPlugin
 * Casio\LotterySale\Plugin\Block\Widget
 */
class FormPlugin
{
    /**
     * @var Import
     */
    protected $_import;

    /**
     * FormPlugin constructor.
     * @param Import $import
     */
    public function __construct(
        Import $import
    ) {
        $this->_import = $import;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Form $subject
     * @param \Magento\Framework\Data\Form $form
     * @return \Magento\Framework\Data\Form[]
     */
    public function beforeSetForm(
        \Magento\Backend\Block\Widget\Form $subject,
        \Magento\Framework\Data\Form $form
    ) {
        $behaviorCode = 'lottery_application_behavior';
        if ($form->getElement($behaviorCode . '_fieldset')) {
            $form->getElement($behaviorCode . '_fieldset')->addField(
                $behaviorCode . '_' . HelperData::NUMBER_WINNER,
                'text',
                [
                    'name' => HelperData::NUMBER_WINNER,
                    'label' => __('Number of Winner'),
                    'title' => __('Number of Winner'),
                    'required' => true,
                    'disabled' => true,
                    'value' => 10,
                    'class' => $behaviorCode . ' validate-number validate-greater-than-zero input-text',
                    'note' => __(
                        'Please specify number of winner'
                    ),
                ],
                '^'
            );
        }
        return [$form];
    }
}
