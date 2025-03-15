<?php

namespace Casio\LotterySale\Model\SalesType;

use Casio\SalesPeriod\Api\SalesTypeInterface;
use Magento\Catalog\Model\Product;

class BeforeLotterySales implements SalesTypeInterface
{
    /**
     * const lottery sales code
     */
    const BEFORE_LOTTERY_CODE = 'before_lottery';

    /**
     * @var Lottery
     */
    private Lottery $lottery;

    /**
     * LotterySales constructor.
     * @param Lottery $lottery
     */
    public function __construct(
        Lottery $lottery
    ) {
        $this->lottery = $lottery;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return self::BEFORE_LOTTERY_CODE;
    }

    /**
     * @param Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(Product $product): bool
    {
        if ($this->lottery->match($product) == Lottery::BEFORE) {
            return true;
        }

        return false;
    }
}
