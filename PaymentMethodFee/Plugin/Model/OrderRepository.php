<?php


namespace Codilar\PaymentMethodFee\Plugin\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface as Subject;
use Magento\Sales\Model\Order;

class OrderRepository
{

    /**
     * @param Subject $subject
     * @param OrderInterface $result
     * @return OrderInterface
     */
    public function afterGet(Subject $subject, $result)
    {
        $this->setPaymentMethodFee($result);
        return $result;
    }

    /**
     * @param Subject $subject
     * @param OrderSearchResultInterface $result
     * @return OrderSearchResultInterface
     */
    public function afterGetList(Subject $subject, $result)
    {
        foreach ($result->getItems() as $order) {
            $this->setPaymentMethodFee($order);
        }
        return $result;
    }

    /**
     * @param OrderInterface|Order $order
     */
    protected function setPaymentMethodFee(OrderInterface $order)
    {
        $order->getExtensionAttributes()->setPaymentMethodFee($order->getData('payment_method_fee'));
    }
}
