<?php

namespace Casio\LotterySale\Model\SalesType;

use Casio\LotterySale\Helper\Data;
use Casio\SalesPeriod\Model\SalesType\BeforeSales;
use Magento\Catalog\Model\Product;

class SalesTypeResolver extends \Casio\SalesPeriod\Model\Resolver\SalesTypeResolver
{
    const UNDEFINED = 0;
    const LOTTERY_BEFORE_SALES = 1;
    const SALES_BEFORE_LOTTERY = 2;
    const LOTTERY_LINKED_SALES = 3;
    const SALES_LINKED_LOTTERY = 4;
    const LOTTERY_INSIDE_SALES = 5;
    const SALES_INSIDE_LOTTERY = 6;
    const LOTTERY_ONLY = 7;
    const SALES_ONLY = 8;

    /**
     * @var array
     */
    private array $salesTypesInit = [];

    /**
     * @var Data
     */
    private Data $dataHelper;

    /**
     * SalesTypeResolver constructor.
     * @param array $salesTypes
     * @param BeforeSales $beforeSales
     * @param Data $dataHelper
     */
    public function __construct(
        array $salesTypes,
        BeforeSales $beforeSales,
        Data $dataHelper
    ) {
        parent::__construct($salesTypes, $beforeSales);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Product $product
     * @return string
     */
    public function resolve(Product $product): string
    {
        $casioLotterySales = $product->getExtensionAttributes()->getCasioLotterySales();

        if (!$this->salesTypesInit) {
            $this->salesTypesInit = $this->salesTypes;
        }

        if ($casioLotterySales && ($casioLotterySales->getApplicationDateFrom() || $casioLotterySales->getApplicationDateTo())) {
            unset($this->salesTypes['preorder']);
            $this->modifySalesTypes($product);
        } else {
            unset($this->salesTypes['before_lottery']);
            unset($this->salesTypes['lottery']);
            unset($this->salesTypes['end_lottery']);
        }

        $salesType = parent::resolve($product);

        // Reset array salesTypes to list default
        $this->salesTypes = $this->salesTypesInit;

        return $salesType;
    }

    /**
     * @param Product $product
     * @return int
     */
    private function getTypeRelationship(Product $product): int
    {
        if (!$product->getCasioSalesStartDate() && !$product->getCasioSalesEndDate()) {
            return self::LOTTERY_ONLY;
        }

        $casioLotterySales = $product->getExtensionAttributes()->getCasioLotterySales();
        if (!$casioLotterySales->getApplicationDateFrom() || !$casioLotterySales->getApplicationDateTo()) {
            return self::SALES_ONLY;
        }

        $salesStartDate = $product->getCasioSalesStartDate();
        $salesEndDate = $product->getCasioSalesEndDate();
        if ($product->getData(\Casio\Core\Helper\Data::CASIO_SET_TIME_ZONE_KEY)) {
            $lotteryStartDate = $this->dataHelper->getDate($casioLotterySales->getApplicationDateFrom());
            $lotteryEndDate = $this->dataHelper->getDate($casioLotterySales->getApplicationDateTo());
        } else {
            $lotteryStartDate = $casioLotterySales->getApplicationDateFrom();
            $lotteryEndDate = $casioLotterySales->getApplicationDateTo();
        }

        if ($salesStartDate && $salesEndDate) {
            if ($lotteryEndDate <= $salesStartDate) {
                return self::LOTTERY_BEFORE_SALES;
            }

            if ($salesEndDate <= $lotteryStartDate) {
                return self::SALES_BEFORE_LOTTERY;
            }

            if ($lotteryStartDate <= $salesStartDate && $salesStartDate <= $lotteryEndDate) {
                if ($lotteryEndDate < $salesEndDate) {
                    return self::LOTTERY_LINKED_SALES;
                }
                return self::SALES_INSIDE_LOTTERY;
            }

            if ($salesStartDate <= $lotteryStartDate && $lotteryStartDate <= $salesEndDate) {
                if ($salesEndDate < $lotteryEndDate) {
                    return self::SALES_LINKED_LOTTERY;
                }
                return self::LOTTERY_INSIDE_SALES;
            }
        }

        if (!$salesStartDate) {
            if ($lotteryEndDate <= $salesEndDate) {
                return self::LOTTERY_INSIDE_SALES;
            }

            if ($salesEndDate <= $lotteryStartDate) {
                return self::SALES_BEFORE_LOTTERY;
            } else {
                return self::SALES_LINKED_LOTTERY;
            }
        }

        if (!$salesEndDate) {
            if ($lotteryEndDate <= $salesStartDate) {
                return self::LOTTERY_BEFORE_SALES;
            }

            if ($salesStartDate <= $lotteryStartDate) {
                return self::LOTTERY_INSIDE_SALES;
            } else {
                return self::LOTTERY_LINKED_SALES;
            }
        }

        return self::UNDEFINED;
    }

    /**
     * @param Product $product
     */
    private function modifySalesTypes(Product $product)
    {
        $typeRelationship = $this->getTypeRelationship($product);

        switch ($typeRelationship) {
            case self::LOTTERY_BEFORE_SALES:
                $salesTypes = [
                    'lottery' => $this->salesTypes['lottery'],
                    'regular' => $this->salesTypes['regular'],
                    'before_lottery' => $this->salesTypes['before_lottery'],
                    'end_of_sales' => $this->salesTypes['end_of_sales'],
                    'end_lottery' => $this->salesTypes['end_lottery']
                ];
                break;
            case self::SALES_BEFORE_LOTTERY:
                $salesTypes = [
                    'lottery' => $this->salesTypes['lottery'],
                    'regular' => $this->salesTypes['regular'],
                    'before_sales' => $this->salesTypes['before_sales'],
                    'before_lottery' => $this->salesTypes['before_lottery'],
                    'end_lottery' => $this->salesTypes['end_lottery']
                ];
                break;
            case self::LOTTERY_LINKED_SALES:
                $salesTypes = [
                    'lottery' => $this->salesTypes['lottery'],
                    'regular' => $this->salesTypes['regular'],
                    'before_lottery' => $this->salesTypes['before_lottery'],
                    'end_of_sales' => $this->salesTypes['end_of_sales']
                ];
                break;
            case self::SALES_LINKED_LOTTERY:
                $salesTypes = [
                    'lottery' => $this->salesTypes['lottery'],
                    'regular' => $this->salesTypes['regular'],
                    'before_sales' => $this->salesTypes['before_sales'],
                    'end_lottery' => $this->salesTypes['end_lottery']
                ];
                break;
            case self::LOTTERY_INSIDE_SALES:
                $salesTypes = [
                    'lottery' => $this->salesTypes['lottery'],
                    'regular' => $this->salesTypes['regular'],
                    'before_sales' => $this->salesTypes['before_sales'],
                    'end_of_sales' => $this->salesTypes['end_of_sales']
                ];
                break;
            case self::SALES_INSIDE_LOTTERY:
            case self::LOTTERY_ONLY:
                $salesTypes = [
                    'lottery' => $this->salesTypes['lottery'],
                    'before_lottery' => $this->salesTypes['before_lottery'],
                    'end_lottery' => $this->salesTypes['end_lottery']
                ];
                break;
            case self::SALES_ONLY:
                $salesTypes = [
                    'regular' => $this->salesTypes['regular'],
                    'before_sales' => $this->salesTypes['before_sales'],
                    'end_of_sales' => $this->salesTypes['end_of_sales']
                ];
                break;
            default:
                $salesTypes = $this->salesTypes;
        }

        $this->salesTypes = $salesTypes;
    }
}
