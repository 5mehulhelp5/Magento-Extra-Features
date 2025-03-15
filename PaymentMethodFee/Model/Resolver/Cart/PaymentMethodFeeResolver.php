<?php

namespace Codilar\PaymentMethodFee\Model\Resolver\Cart;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
class PaymentMethodFeeResolver implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }



    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        $value = null,
        $args = null
    ) {
        if (isset($value['model']) && $value['model'] instanceof \Magento\Quote\Model\Quote) {
            $quote = $value['model'];

            $feeValue = (float) $quote->getPaymentMethodFee();
            $label =  $quote->getPaymentMethodFeeLabel() ?? 'Payment Method Fee';
            $currency = $quote->getQuoteCurrencyCode();

            if ($feeValue > 0) {
                return [
                    'value' => $feeValue,
                    'label' => $label,
                    'currency' => $currency
                ];
            }
        }

        return null;
    }
}
