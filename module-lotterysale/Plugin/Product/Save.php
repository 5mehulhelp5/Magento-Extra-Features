<?php

namespace Casio\LotterySale\Plugin\Product;

class Save
{
    const USE_DEFAULT_FIELDS = [
        'casio_pre_order_start_date',
        'casio_pre_order_end_date'
    ];

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Save $subject
     */
    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Product\Save $subject)
    {
        $request = $subject->getRequest();
        $productParams = $request->getPostValue('product');
        $useDefaultParams = $request->getPostValue('use_default');
        if(isset($productParams['casio_lottery_sales']['application_date_to'],
            $productParams['casio_lottery_sales']['application_date_from']) &&
            $productParams['casio_lottery_sales']['application_date_to'] &&
            $productParams['casio_lottery_sales']['application_date_from']
        ){
            foreach (self::USE_DEFAULT_FIELDS as $key) {
                $useDefaultParams[$key] = 0;
            }
        }
        $request->setPostValue('use_default', $useDefaultParams);
    }
}
