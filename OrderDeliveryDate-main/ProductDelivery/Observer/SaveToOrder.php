<?php

namespace Codilar\ProductDelivery\Observer;

class SaveToOrder implements \Magento\Framework\Event\ObserverInterface
{   
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $quote = $event->getQuote();
        $order = $event->getOrder();
        $order->setData('delivery_date', $quote->getData('delivery_date'));
       
      
        // $quote= $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
        // /** @var \Magento\Quote\Model\Quote $quote */
        // $quote = $quoteRepository->get($order->getQuoteId());
        // $order->setDeliveryDate( $quote->getDeliveryDate() );

        // return $this;
    }
}