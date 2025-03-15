<?php

namespace Casio\LotterySale\Plugin\Model\Checkout;

use Casio\Core\Helper\Data;
use Casio\LotterySale\Model\SalesType\Lottery;
use Casio\LotterySale\Service\QuoteValidatorService;
use Casio\SalesPeriod\Model\Resolver\SalesTypeResolver;
use Casio\SalesPeriod\Model\SalesType\PurchaseValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class AbstractValidation
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var PurchaseValidator
     */
    private PurchaseValidator $purchaseValidator;

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

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
     * @param PurchaseValidator $purchaseValidator
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Lottery $salesTypeLottery
     * @param QuoteValidatorService $quoteValidatorService
     * @param SalesTypeResolver $salesTypeResolver
     */
    public function __construct(
        PurchaseValidator $purchaseValidator,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Lottery $salesTypeLottery,
        QuoteValidatorService $quoteValidatorService,
        SalesTypeResolver $salesTypeResolver
    ) {
        $this->purchaseValidator = $purchaseValidator;
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->salesTypeLottery = $salesTypeLottery;
        $this->quoteValidatorService = $quoteValidatorService;
        $this->salesTypeResolver = $salesTypeResolver;
    }

    /**
     * @param \Casio\SalesPeriod\Plugin\Model\Checkout\AbstractValidation $subject
     * @param callable $proceed
     * @param $cartId
     * @param false $isGuest
     * @return void
     * @throws LocalizedException
     */
    public function aroundValidate(\Casio\SalesPeriod\Plugin\Model\Checkout\AbstractValidation $subject, callable $proceed, $cartId, $isGuest = false)
    {
        if ($isGuest) {
            $cartId = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id')->getQuoteId();
        }

        $items = $this->cartRepository->getActive($cartId)->getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();

            $valid = $this->purchaseValidator->validate($product);
            $casioLotterySales = $this->salesTypeLottery->match($product);
            if ($casioLotterySales !== Lottery::NOT_LOTTERY) {
                $salesType = $this->salesTypeResolver->resolve($product);
                if ($valid) {
                    if ($salesType == 'lottery' &&
                        $this->quoteValidatorService->hasErrorQuoteItem($item, $item->getQuote()->getCustomerId(), true)
                    ) {
                        $this->checkoutError();
                    }
                } else {
                    if ($salesType == 'end_lottery' || $salesType == 'end_of_sales') {
                        if ($this->quoteValidatorService->hasErrorQuoteItem($item, $item->getQuote()->getCustomerId(), true) ||
                            $this->quoteValidatorService->isExpiredPurchased($product)
                        ) {
                            $this->checkoutError();
                        }
                    } else {
                        $this->checkoutError();
                    }
                }
            } elseif (!$valid) {
                $this->checkoutError();
            }
        }
    }

    /**
     * @throws LocalizedException
     */
    private function checkoutError()
    {
        throw new LocalizedException(__('This product is currently unsellable.'), null, Data::CASIO_CHECKOUT_ERROR_CODE);
    }
}
