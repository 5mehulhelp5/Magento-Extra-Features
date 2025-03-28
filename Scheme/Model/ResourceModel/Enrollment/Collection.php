<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Model\ResourceModel\Enrollment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \KalyanUs\Scheme\Model\Enrollment::class,
            \KalyanUs\Scheme\Model\ResourceModel\Enrollment::class
        );
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
