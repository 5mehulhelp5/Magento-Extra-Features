<?php

namespace Casio\LotterySale\Model\SalesType\Validator;

use Casio\SalesPeriod\Api\PurchaseValidatorInterface;
use Magento\Catalog\Model\Product;

class EndLotterySales implements PurchaseValidatorInterface
{
    /**
     * @param Product $product
     * @return bool
     */
    public function validate(Product $product): bool
    {
        return false;
    }
}
