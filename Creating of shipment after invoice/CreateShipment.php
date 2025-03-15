<?php
namespace Codilar\CustomNotify\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Shipping\Model\ShipmentNotifier;
class CreateShipment implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    /**
     * @var Order
     */
    protected $_convertOrder;
    /**
     * @var ShipmentNotifier
     */
    protected $_shipmentNotifier;
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Order          $convertOrder
     * @param ShipmentNotifier    $shipmentNotifier
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Order $convertOrder,
        ShipmentNotifier $shipmentNotifier
    ){
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->_shipmentNotifier = $shipmentNotifier;
        // Observer initialization code...
        // You can use dependency injection to get any class this observer may need.
    }
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $orderId = $invoice->getOrderId();
        if($orderId && $orderId != null) {
            $order = $this->_orderRepository->get($orderId);
            // to check order can ship or not
            if (!$order->canShip()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You cant create the Shipment of this order.') );
            }
            $orderShipment = $this->_convertOrder->toShipment($order);
            foreach ($order->getAllItems() AS $orderItem) {
                // Check virtual item and item Quantity
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qty = $orderItem->getQtyToShip();
                $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);
                $orderShipment->addItem($shipmentItem);
            }
            $orderShipment->register();
            $orderShipment->getOrder()->setIsInProcess(true);
            try {
                // Save created Order Shipment
                $orderShipment->save();
                $orderShipment->getOrder()->save();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }
        }

    }
}
