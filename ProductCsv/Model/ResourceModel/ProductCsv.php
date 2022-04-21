<?php

namespace Codilar\ProductCsv\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductCsv extends AbstractDb
{

    const TABLE_NAME = 'codilar_productcsv';
    const ID_FIELD_NAME = 'entity_id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }
}
