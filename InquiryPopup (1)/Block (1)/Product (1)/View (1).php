<?php

namespace Codilar\InquiryPopup\Block\Product;

use Magento\Framework\View\Element\Template;


class View extends Template
{
    public function __construct(
        Template\Context $context,
        array   $data = []
    )

    {

        parent::__construct($context, $data);

        }
        public function getAddUrl()
        {
            return $this->getUrl('inquirydetails/inquirypopup/save');
        }

}
