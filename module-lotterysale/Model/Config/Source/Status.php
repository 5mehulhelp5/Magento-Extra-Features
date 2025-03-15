<?php

namespace Casio\LotterySale\Model\Config\Source;

use Casio\LotterySale\Helper\Data as DataHelper;

/**
 * Class Status
 * Casio\LotterySale\Model\Config\Source
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $results = [];
        foreach ($options as $value => $label) {
            $results[] = [
                'value' => $value,
                'label' => __($label)
            ];
        }
        return $results;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $options = $this->getOptionArray();
        $results = [];
        foreach ($options as $value => $label) {
            $results[$value] = __($label);
        }
        return $results;
    }

    /**
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            DataHelper::STATUS_APPLYING => 'Applying',
            DataHelper::STATUS_LOST => 'Lost',
            DataHelper::STATUS_WINNING => 'Winning'
        ];
    }
}
