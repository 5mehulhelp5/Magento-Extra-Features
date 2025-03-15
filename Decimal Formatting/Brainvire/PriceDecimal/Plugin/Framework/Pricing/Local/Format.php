<?php
/**
 * Brainvire Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brainvire.com license that is
 * available through the world-wide-web at this URL:
 * https://www.brainvire.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Brainvire
 * @package     Brainvire_PriceDecimal
 * @copyright   Copyright (c) 2019-2020 Brainvire Co., Ltd. All rights reserved. (http://www.brainvire.com/)
 * @license     https://www.brainvire.com/LICENSE.txt
 */

namespace Brainvire\PriceDecimal\Plugin\Framework\Pricing\Local;

class Format
{
    /**
     * @var \Brainvire\PriceDecimal\Helper\Data
     */
    protected $priceDecimalHelperData;

    /**
     * @param \Brainvire\PriceDecimal\Helper\Data $priceDecimalHelperData
     */
    public function __construct(
        \Brainvire\PriceDecimal\Helper\Data $priceDecimalHelperData
    ) {
        $this->priceDecimalHelperData = $priceDecimalHelperData;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Locale\FormatInterface $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetPriceFormat(
        \Magento\Framework\Locale\FormatInterface $subject,
        $result
    ) {
        if ($this->priceDecimalHelperData->_isModuleEnable()) {
            if ($this->priceDecimalHelperData->isAllowedPage()) {
                $precision = $this->priceDecimalHelperData->getDecimalLength();
                $result['precision'] = $precision;
                $result['requiredPrecision'] = $precision;               
            }  else {
                $precision = 0;
                $result['precision'] = $precision;
                $result['requiredPrecision'] = $precision;
            }
        }        
        return $result;
    }
}
