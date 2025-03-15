<?php

namespace Casio\LotterySale\Ui\DataProvider\LotteryApplication;

use Casio\LotterySale\Model\ResourceModel\LotteryApplication\Grid\CollectionFactory;

/**
 * Class DataProvider
 * Casio\LotterySale\Ui\DataProvider\LotteryApplication
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /** @var \Casio\LotterySale\Model\ResourceModel\LotteryApplication\Grid\Collection  */
    protected $collection;

    /**
     * DataProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'id') {
            $filter->setField('main_table.id');
        }
        parent::addFilter($filter);
    }
}
