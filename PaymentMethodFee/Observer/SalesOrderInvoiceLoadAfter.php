<?php


namespace Codilar\PaymentMethodFee\Observer;


use Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderInvoiceLoadAfter implements ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        $order = $invoice->getOrder();

        $invoice->setData(PaymentMethodFee::PAYMENT_METHOD_FEE_KEY, $order->getData(PaymentMethodFee::PAYMENT_METHOD_FEE_KEY));
        $invoice->setData(PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY, $order->getData(PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY));
        $invoice->setData(PaymentMethodFee::PAYMENT_METHOD_FEE_LABEL_KEY, $order->getData(PaymentMethodFee::PAYMENT_METHOD_FEE_LABEL_KEY));

    }
}
