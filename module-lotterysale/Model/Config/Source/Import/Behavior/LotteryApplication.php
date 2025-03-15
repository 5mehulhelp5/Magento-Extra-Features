<?php
namespace Casio\LotterySale\Model\Config\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

/**
 * Class LotteryApplication
 * Casio\LotterySale\Model\Config\Source\Import\Behavior
 */
class LotteryApplication extends \Magento\ImportExport\Model\Source\Import\Behavior\Basic
{
    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Update'),
        ];
    }
    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'lottery_application';
    }
}
