<?php

namespace Codilar\Review\Plugin\Model;

use Magento\Review\Model\ResourceModel\Review\Collection as Subject;

class Collection
{

    /*
     * @param Suject $subject
     * @return String
     */
    public function afterLoad(Subject $subject, $result)
    {
        if (!$subject->getData()) {
//            $result = ['review_id' => '1',
//                'created_at' => '2022-04-26 05:35:55',
//                'entity_id' => '1',
//                'entity_pk_value' => '1',
//                'status_id' =>  '1',
//                'detail_id' =>  '1',
//                'title' => 'This Product Good',
//                'detail' => 'Product is Awasome',
//                'nickname' => 'Default',
//                'customer_id' => null,
//                'entity_code' => 'product'
//            ];
            echo '<b>Default Review</b><br><br>';
            echo 'This Product Good<br>';
            echo 'Product is Awsome<br><br>';
        }
        return $result;
    }
}
