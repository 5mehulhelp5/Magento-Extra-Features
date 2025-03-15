<?php

namespace Casio\LotterySale\Model\SalesType;

class Lottery
{
    /**
     * Const status lottery period
     */
    const NOT_LOTTERY = -1;
    const BEFORE = 0;
    const REGULAR = 1;
    const AFTER = 2;

    /**
     * Product reviews are currently in the time of applying the lottery
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return int
     */
    public function match(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $casioLotterySales = $product->getExtensionAttributes()->getCasioLotterySales();
        if (!$casioLotterySales || !$casioLotterySales->getApplicationDateFrom() || !$casioLotterySales->getApplicationDateTo()) {
            return self::NOT_LOTTERY;
        }

        $now = new \DateTime();
        $from = $casioLotterySales->getApplicationDateFrom();
        $to = $casioLotterySales->getApplicationDateTo();

        if ($now->getTimestamp() < strtotime($from)) {
            return self::BEFORE;
        }

        if ($now->getTimestamp() > strtotime($to)) {
            return self::AFTER;
        }

        return self::REGULAR;
    }
}
