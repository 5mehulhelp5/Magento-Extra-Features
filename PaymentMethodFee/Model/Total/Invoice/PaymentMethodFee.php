<?php


namespace Codilar\PaymentMethodFee\Model\Total\Invoice;


class PaymentMethodFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @inheritdoc
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);

        $order = $invoice->getOrder();

        $paymentMethodFee = (float)$order->getData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::PAYMENT_METHOD_FEE_KEY);
        $basePaymentMethodFee = (float)$order->getData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY);

        $invoice->setData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::PAYMENT_METHOD_FEE_KEY, $paymentMethodFee);
        $invoice->setData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY, $basePaymentMethodFee);

        if ($paymentMethodFee) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $paymentMethodFee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $basePaymentMethodFee);
        }

        return $this;
    }
}
