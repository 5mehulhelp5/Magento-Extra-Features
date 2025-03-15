<?php

namespace Candere\Accounts\Plugin;

use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
class OrderGridPlugin
{

    public function beforeLoad(OrderGridCollection $subject)
    {
        if ($subject->isLoaded()) {
            return;
        }

        $select = $subject->getSelect();
        $connection = $subject->getConnection();

        // Subquery to group item IDs
        $subquery = $connection->select()
            ->from(
                ['order_items' => $subject->getTable('sales_order_item')],
                ['order_id', 'item_ids' => new \Zend_Db_Expr('GROUP_CONCAT(order_items.item_id SEPARATOR ", ")')]
            )
            ->group('order_items.order_id');

        // Join subquery with main table
        $select->joinLeft(
            ['items_subquery' => $subquery],
            'main_table.entity_id = items_subquery.order_id',
            ['item_ids']
        );
    }
}
