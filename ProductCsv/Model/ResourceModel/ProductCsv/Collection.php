<?php

namespace Codilar\ProductCsv\Model\ResourceModel\ProductCsv;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Codilar\ProductCsv\Model\ProductCsv as Model;
use Codilar\ProductCsv\Model\ResourceModel\ProductCsv as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
