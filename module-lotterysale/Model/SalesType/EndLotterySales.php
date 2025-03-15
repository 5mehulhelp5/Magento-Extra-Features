<?php

namespace Casio\LotterySale\Model\SalesType;

use Casio\SalesPeriod\Api\SalesTypeInterface;
use Magento\Catalog\Model\Product;

class EndLotterySales implements SalesTypeInterface
{
    /**
     * const lottery sales code
     */
    const END_LOTTERY_CODE = 'end_lottery';

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
        return self::END_LOTTERY_CODE;
    }

    /**
     * @param Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(Product $product): bool
    {
        if ($this->lottery->match($product) == Lottery::AFTER) {
            return true;
        }

        return false;
    }
}
