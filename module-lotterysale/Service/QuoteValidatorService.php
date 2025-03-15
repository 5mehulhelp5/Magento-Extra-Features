<?php

namespace Casio\LotterySale\Service;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Helper\Data;
use Casio\LotterySale\Model\LotteryApplicationRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class QuoteValidatorService
{
    const EXPIRED_ERROR = 'Cannot purchase because the purchase deadline has expired';
    const UNWINNED_ERROR = 'Cannot be purchased because it is an unwinned lottery product';
    const PURCHASED_ERROR = 'Cannot be purchased because it is a lottery product that has already been purchased';

    /**
     * @var LotteryApplicationRepository
     */
    private LotteryApplicationRepository $lotteryApplicationRepository;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;
    /**
     * @var Data
     */
    private Data $data;

    /**
     * QuoteValidatorService constructor.
     * @param LotteryApplicationRepository $lotteryApplicationRepository
     * @param RequestInterface $request
     * @param Data $data
     */
    public function __construct(
        LotteryApplicationRepository $lotteryApplicationRepository,
        RequestInterface $request,
        Data $data
    ) {
        $this->lotteryApplicationRepository = $lotteryApplicationRepository;
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * @param CartItemInterface $quoteItem
     * @param $customerId
     * @param bool $isCheckout
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasErrorQuoteItem($quoteItem, $customerId, $isCheckout = false)
    {
        if (!$this->data->isEnabled() || ($this->request->getFullActionName() != 'checkout_cart_index' && !$isCheckout)) {
            return false;
        }

        $product = $quoteItem->getProduct();
        $casioLotterySales = $this->getLotteryProduct($product);
        if ($casioLotterySales) {
            $lotteryApplication = $this->lotteryApplicationRepository
                ->getByUserIdAndProductId($customerId, $product->getId());
            if (!$lotteryApplication || $lotteryApplication->getStatus() != Data::STATUS_WINNING) {
                return self::UNWINNED_ERROR;
            }
            if ($lotteryApplication->getOrdered()) {
                return self::PURCHASED_ERROR;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return false|string
     */
    public function isExpiredPurchased(\Magento\Catalog\Model\Product $product)
    {
        $casioLotterySales = $this->getLotteryProduct($product);
        if ($casioLotterySales) {
            $purchaseDeadline = $casioLotterySales->getPurchaseDeadline();
            $now = new \DateTime();
            if (strtotime($purchaseDeadline) < $now->getTimestamp()) {
                return self::EXPIRED_ERROR;
            }
        }

        return false;
    }

    /**
     * @param $product
     * @return bool|LotterySalesInterface
     */
    public function getLotteryProduct($product)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $casioLotterySales = $extensionAttributes->getCasioLotterySales();
        if ($casioLotterySales &&
            ($casioLotterySales->getApplicationDateFrom() || $casioLotterySales->getApplicationDateTo())) {
            return $casioLotterySales;
        }
        return false;
    }
}
