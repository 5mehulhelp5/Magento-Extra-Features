<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\ResourceModel\LotteryApplication;

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
            \Casio\LotterySale\Model\LotteryApplication::class,
            \Casio\LotterySale\Model\ResourceModel\LotteryApplication::class
        );
        $this->addFilterToMap('created_at', 'main_table.created_at');
    }
}
