<?php

namespace Task\Employee\Model\ResourceModel\EmployeeAddress;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Task\Employee\Model\EmployeeAddress as Model;
use Task\Employee\Model\ResourceModel\EmployeeAddress as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * EmployeeAddress Collection constructor.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
