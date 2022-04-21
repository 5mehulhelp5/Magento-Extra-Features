<?php

namespace Codilar\ProductDelivery\Plugin\Checkout;

class LayoutProcessorPlugin
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['before-form']['children']['delivery_date'] = [
            'component' => 'Magento_Ui/js/form/element/date',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/date',
                'options' => [],
                'id' => 'delivery_date'
            ],
            'dataScope' => 'shippingAddress.delivery_date',
            'label' => __('Delivery Date'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => 200,
            'id' => 'delivery_date'
        ];


        return $jsLayout;
    }
}
