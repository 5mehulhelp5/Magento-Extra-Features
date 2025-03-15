<?php


namespace Codilar\PaymentMethodFee\Model\Total;


use Codilar\PaymentMethodFee\Model\Config;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class PaymentMethodFee extends AbstractTotal
{

    const PAYMENT_METHOD_FEE_KEY = 'payment_method_fee';
    const BASE_PAYMENT_METHOD_FEE_KEY = 'base_payment_method_fee';
    const PAYMENT_METHOD_FEE_LABEL_KEY = 'payment_method_fee_label';

    const CODE = 'payment_method_fee';

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var Config
     */
    private $config;

    /**
     * PaymentMethodFee constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $config
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Config $config
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->config = $config;
    }

    public function collect(
        Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);

        if ($total->getBaseSubtotal()) {
            $address = $shippingAssignment->getShipping()->getAddress();
            $store = $quote->getStore();
            $currency = $quote->getCurrency();

            $baseAmount = $this->getFee($quote);
            $amount = $this->priceCurrency->convert($baseAmount, $store, $currency);
            $label = $this->config->getLabel($quote->getPayment()->getMethod());

            $total->setTotalAmount($this->getCode(), $amount);
            $total->setBaseTotalAmount($this->getCode(), $baseAmount);

            $quote->setData(static::PAYMENT_METHOD_FEE_KEY, $amount);
            $quote->setData(static::BASE_PAYMENT_METHOD_FEE_KEY, $baseAmount);
            $quote->setData(static::PAYMENT_METHOD_FEE_LABEL_KEY, $label);

        }

        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $store = $quote->getStore();
        $currency = $quote->getCurrency();

        $baseAmount = $this->getFee($quote);
        $amount = $this->priceCurrency->convert($baseAmount, $store, $currency);

        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'base_value' => $baseAmount,
            'value' => $amount
        ];
    }

    public function getCode()
    {
        return static::CODE;
    }

    public function getLabel()
    {
        return $this->config->getLabel();
    }

    protected function getFee(Quote $quote)
    {
        $paymentMethod = $quote->getPayment()->getMethod();
        return $this->config->getPaymentMethodFee($paymentMethod);
    }

}
