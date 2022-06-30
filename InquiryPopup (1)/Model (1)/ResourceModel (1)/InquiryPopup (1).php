<?php

namespace Codilar\InquiryPopup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InquiryPopup extends AbstractDb
{

    const TABLE_NAME = 'codilar_inquirydetails';
    const ID_FIELD_NAME = 'entity_id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }
}