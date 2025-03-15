<?php

namespace Casio\LotterySale\Model\SalesType\Validator;

use Casio\BackOrder\Service\BackOrderValidatorService;
use Casio\SalesPeriod\Api\PurchaseValidatorInterface;
use Magento\Catalog\Model\Product;

class RegularLotterySales implements PurchaseValidatorInterface
{
    /**
     * @var BackOrderValidatorService
     */
    private BackOrderValidatorService $backOrderValidatorService;

    /**
     * RegularLotterySales constructor.
     * @param BackOrderValidatorService $backOrderValidatorService
     */
    public function __construct(
        BackOrderValidatorService $backOrderValidatorService
    ) {
        $this->backOrderValidatorService = $backOrderValidatorService;
    }

    /**
     * @param Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(Product $product): bool
    {
        return $this->backOrderValidatorService->isSalable($product);
    }
}
