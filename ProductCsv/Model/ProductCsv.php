<?php

namespace Codilar\ProductCsv\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\ProductCsv\Model\ResourceModel\ProductCsv as ResourceModel;

class ProductCsv extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
