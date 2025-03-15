<?php


namespace Codilar\PaymentMethodFee\Model\Total\CreditMemo;


class PaymentMethodFee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @inheritdoc
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditMemo)
    {
        parent::collect($creditMemo);

        $order = $creditMemo->getOrder();

        $paymentMethodFee = (float)$order->getData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::PAYMENT_METHOD_FEE_KEY);
        $basePaymentMethodFee = (float)$order->getData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY);

        $creditMemo->setData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::PAYMENT_METHOD_FEE_KEY, $paymentMethodFee);
        $creditMemo->setData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY, $basePaymentMethodFee);


        if ($paymentMethodFee) {
            $creditMemo->setGrandTotal($creditMemo->getGrandTotal() + $paymentMethodFee);
            $creditMemo->setBaseGrandTotal($creditMemo->getBaseGrandTotal() + $basePaymentMethodFee);
        }

        return $this;
    }
}
