<?php


namespace Codilar\PaymentMethodFee\Observer;


use Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $fieldsToCopy = [
            PaymentMethodFee::PAYMENT_METHOD_FEE_KEY,
            PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY,
            PaymentMethodFee::PAYMENT_METHOD_FEE_LABEL_KEY
        ];

        foreach ($fieldsToCopy as $field) {
            if ($quote->getData($field)) {
                $order->setData($field, $quote->getData($field));
            }
        }

        return $this;
    }
}
