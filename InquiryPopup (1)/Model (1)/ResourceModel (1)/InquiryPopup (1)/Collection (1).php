<?php

namespace Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Codilar\InquiryPopup\Model\InquiryPopup as Model;
use Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}