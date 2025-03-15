<?php


namespace Codilar\PaymentMethodFee\Model\Sales\Pdf;


use Codilar\PaymentMethodFee\Model\Config;

class PaymentMethodFee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * PaymentMethodFee constructor.
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        Config $config,
        array $data = []
    )
    {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getTotalsForDisplay()
    {
        $totals = [];

        $paymentMethodFee = $this->getAmount();

        if ($paymentMethodFee) {
            $totals[] = [
                'amount' => $this->getOrder()->formatPriceTxt($paymentMethodFee),
                'label' => $this->getOrder()->getData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::PAYMENT_METHOD_FEE_LABEL_KEY),
                'font_size' => 10
            ];
        }

        return $totals;
    }

    public function getAmount()
    {
        $order = $this->getOrder();
        return $order->getData(\Codilar\PaymentMethodFee\Model\Total\PaymentMethodFee::PAYMENT_METHOD_FEE_KEY);
    }
}
