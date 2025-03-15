<?php

namespace Casio\LotterySale\Plugin\Model\Quote\Item;

use Casio\LotterySale\Model\SalesType\Lottery;
use Casio\LotterySale\Service\QuoteValidatorService;
use Casio\SalesPeriod\Model\Resolver\SalesTypeResolver;
use Casio\SalesPeriod\Service\ValidatorSalesDateService;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item;

class Validator
{
    /**
     * @var ValidatorSalesDateService
     */
    private ValidatorSalesDateService $validatorSalesDateService;

    /**
     * @var Lottery
     */
    private Lottery $salesTypeLottery;

    /**
     * @var QuoteValidatorService
     */
    private QuoteValidatorService $quoteValidatorService;

    /**
     * @var SalesTypeResolver
     */
    private SalesTypeResolver $salesTypeResolver;

    /**
     * Validator constructor.
     * @param ValidatorSalesDateService $validatorSalesDateService
     * @param Lottery $salesTypeLottery
     * @param QuoteValidatorService $quoteValidatorService
     * @param SalesTypeResolver $salesTypeResolver
     */
    public function __construct(
        ValidatorSalesDateService $validatorSalesDateService,
        Lottery $salesTypeLottery,
        QuoteValidatorService $quoteValidatorService,
        SalesTypeResolver $salesTypeResolver
    ) {
        $this->validatorSalesDateService = $validatorSalesDateService;
        $this->salesTypeLottery = $salesTypeLottery;
        $this->quoteValidatorService = $quoteValidatorService;
        $this->salesTypeResolver = $salesTypeResolver;
    }

    /**
     * @param \Casio\SalesPeriod\Plugin\Model\Quote\Item\Validator $subject
     * @param callable $proceed
     * @param QuantityValidator $quantityValidator
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundBeforeValidate(
        \Casio\SalesPeriod\Plugin\Model\Quote\Item\Validator $subject,
        callable $proceed,
        QuantityValidator $quantityValidator,
        Observer $observer
    ) {
        /* @var $quoteItem Item */
        $quoteItem = $observer->getEvent()->getItem();
        $product = $quoteItem->getProduct();

        if (!$quoteItem->getParentItemId()) {
            $isUnValidCheckoutCart = $this->validatorSalesDateService->isUnValidCheckoutCart($product);
            $casioLotterySales = $this->salesTypeLottery->match($product);
            if ($casioLotterySales !== Lottery::NOT_LOTTERY) {
                $salesType = $this->salesTypeResolver->resolve($product);
                if (!$isUnValidCheckoutCart) {
                    if ($salesType == 'lottery') {
                        if ($hasError = $this->quoteValidatorService->hasErrorQuoteItem($quoteItem, $quoteItem->getQuote()->getCustomerId())) {
                            $this->addQuoteError($quoteItem, $hasError, true);
                        }
                    }
                } else {
                    if ($salesType == 'end_lottery' || $salesType == 'end_of_sales') {
                        if ($hasError = $this->quoteValidatorService->hasErrorQuoteItem($quoteItem, $quoteItem->getQuote()->getCustomerId())) {
                            $this->addQuoteError($quoteItem, $hasError, true);
                        } elseif ($isExpiredPurchased = $this->quoteValidatorService->isExpiredPurchased($product)) {
                            $this->addQuoteError($quoteItem, $isExpiredPurchased, true);
                        }
                    } else {
                        $this->addQuoteError($quoteItem);
                    }
                }
            } elseif ($isUnValidCheckoutCart) {
                $this->addQuoteError($quoteItem);
            }
        }
    }

    /**
     * @param Item $quoteItem
     * @param $error
     * @param false $isErrorLottery
     */
    private function addQuoteError(Item $quoteItem, $error = null, $isErrorLottery = false)
    {
        $codeError = $isErrorLottery ? 'casio_invalid_lottery_purchase' : 'sales_date_pair_product';
        if (!$error) {
            $error = 'Cannot buy this item right now.';
        }

        $quoteItem->setHasError(true)->addErrorInfo($codeError, $codeError, __($error));
        $quoteItem->getQuote()->setHasError(true)->addErrorInfo($codeError, $codeError, $codeError, __('Cart has invalid item(s).'));
    }
}
