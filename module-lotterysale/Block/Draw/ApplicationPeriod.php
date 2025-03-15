<?php

namespace Casio\LotterySale\Block\Draw;

use Magento\Framework\View\Element\Template;

class ApplicationPeriod extends Template
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Casio_LotterySale::checkout/cart/draw/application_period.phtml';

    /**
     * @param bool $startDate
     * @return \DateTime
     * @throws \Exception
     */
    public function getApplicationPeriod(bool $startDate = true)
    {
        $casioLotterySales = $this->getProduct()->getExtensionAttributes()->getCasioLotterySales();
        return $startDate ?
            $this->_localeDate->date(new \DateTime($casioLotterySales->getApplicationDateFrom())) :
            $this->_localeDate->date(new \DateTime($casioLotterySales->getApplicationDateTo()));
    }
}
