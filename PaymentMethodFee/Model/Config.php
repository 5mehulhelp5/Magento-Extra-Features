<?php


namespace Codilar\PaymentMethodFee\Model;


use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{

    /**
     * @var array
     */
    protected $feeData;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->feeData = \json_decode($scopeConfig->getValue('payment_method_fee/general/fee_data'), true);
    }

    /**
     * @param string $paymentMethod
     * @return float
     */
    public function getPaymentMethodFee($paymentMethod)
    {
        foreach ($this->feeData ?? [] as $fee) {
            $feeDataPaymentMethod = $fee['payment_method'] ?? null;
            $feeDataFee = $fee['fee'] ?? null;
            if ($feeDataPaymentMethod === $paymentMethod) {
                return floatval($feeDataFee);
            }
        }
        return 0;
    }

    /**
     * @param string|null $paymentMethod
     * @return string
     */
    public function getLabel($paymentMethod = null)
    {
        if ($paymentMethod) {
            foreach ($this->feeData ?? [] as $fee) {
                $feeDataPaymentMethod = $fee['payment_method'] ?? null;
                $feeDataLabel = $fee['label'] ?? null;
                if ($feeDataPaymentMethod === $paymentMethod) {
                    return (string)$feeDataLabel;
                }
            }
        }
        return __('Payment method fee');
    }
}
