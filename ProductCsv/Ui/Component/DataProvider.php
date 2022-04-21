<?php

namespace Codilar\ProductCsv\Ui\Component;
use Codilar\ProductCsv\Model\ResourceModel\ProductCsv\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    )   
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
