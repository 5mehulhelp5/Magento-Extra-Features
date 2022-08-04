<?php

namespace Task\Employee\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EmployeeAddress extends AbstractDb
{
    public const TABLE_NAME = 'assignment2_address';
    public const ID_FIELD_NAME = 'entity_id';

    /**
     * EmployeeAddress constructor.
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }
}
