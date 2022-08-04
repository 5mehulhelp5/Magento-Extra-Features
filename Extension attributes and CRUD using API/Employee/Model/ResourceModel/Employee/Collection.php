<?php

namespace Task\Employee\Model\ResourceModel\Employee;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Task\Employee\Model\Employee as Model;
use Task\Employee\Model\ResourceModel\Employee as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Collection constructor.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
