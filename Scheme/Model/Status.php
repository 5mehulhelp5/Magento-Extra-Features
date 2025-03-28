<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Model;

class Status
{
    /**#@+
     * Status values
     */
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 2;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    // phpcs:ignore
    public static function getOptionArray()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
