<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\ResourceModel\LotterySales;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Casio\LotterySale\Model\LotterySales::class,
            \Casio\LotterySale\Model\ResourceModel\LotterySales::class
        );
    }
}
