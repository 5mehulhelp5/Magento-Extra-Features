<?php

namespace Codilar\InquiryPopup\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\InquiryPopup\Model\ResourceModel\InquiryPopup as ResourceModel;

class InquiryPopup extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}