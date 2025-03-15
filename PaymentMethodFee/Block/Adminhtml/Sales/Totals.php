<?php


namespace Codilar\PaymentMethodFee\Block\Adminhtml\Sales;

use Codilar\PaymentMethodFee\Model\Config;
use Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee;
use Magento\Framework\View\Element\Template;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Config
     */
    private Config $config;

    /**
     * Totals constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Initialize gift wrapping and printed card totals for order/invoice/creditmemo
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        $order = null;
        if ($source instanceof \Magento\Sales\Model\Order) {
            $order = $source;
        } else if ($source instanceof \Magento\Sales\Model\Order\Invoice) {
            $order = $source->getOrder();
        } else if ($source instanceof \Magento\Sales\Model\Order\Creditmemo) {
            $order = $source->getOrder();
        }

        if ($order && $order instanceof \Magento\Sales\Model\Order) {
            $paymentMethodFee = floatval($order->getData(PaymentMethodFee::PAYMENT_METHOD_FEE_KEY));
            if ($paymentMethodFee && $paymentMethodFee > 0) {
                $totalData = [
                    'code' => PaymentMethodFee::PAYMENT_METHOD_FEE_KEY,
                    'value' => $paymentMethodFee,
                    'base_value' => $order->getData(PaymentMethodFee::BASE_PAYMENT_METHOD_FEE_KEY),
                    'label' => $order->getData(PaymentMethodFee::PAYMENT_METHOD_FEE_LABEL_KEY)
                ];
                $this->getParentBlock()->addTotal(new \Magento\Framework\DataObject($totalData), 'shipping');
            }
        }

        return $this;
    }
}
