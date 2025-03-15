<?php

namespace Ktpl\ExtendedRma\Block\Adminhtml\Rma;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Rma\Block\Adminhtml\Rma\Grid as RmaGrid;
use Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory;
use Magento\Rma\Model\RmaFactory;
use Ktpl\ExtendedRma\Model\Store;

/**
 * RMA Grid
 */
class Grid extends RmaGrid
{
    /**
     * Rma grid collection
     *
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Rma model
     *
     * @var RmaFactory
     */
    protected $_rmaFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param RmaFactory $rmaFactory
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Store $store,
        RmaFactory $rmaFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_rmaFactory        = $rmaFactory;
        $this->store              = $store;
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $rmaFactory
        );
    }

    /**
     * @inheritdoc
     */
    protected function _beforePrepareCollection()
    {
        if (!$this->getCollection()) {
            $subQueryRmaShippingLabel = new \Zend_Db_Expr(
                '(
                    SELECT
                    magento_rma_shipping_label.rma_entity_id,
                    GROUP_CONCAT(magento_rma_shipping_label.track_number SEPARATOR ";") AS `track_number`
                    from magento_rma_shipping_label
                    group by magento_rma_shipping_label.rma_entity_id
                )'
            );
            $subQueryRmaItem = new \Zend_Db_Expr(
                '(
                    SELECT
                    magento_rma_item_entity.rma_entity_id as magentoRmaItemEntityId,
                    sum(magento_rma_item_entity.qty_requested) as return_items
                    from magento_rma_item_entity
                    group by magentoRmaItemEntityId
                )'
            );
            // @codingStandardsIgnoreStart
            $subQueryRmaReturnValue = new \Zend_Db_Expr(
                '(
                    SELECT
                    magento_rma_item_entity.rma_entity_id as magentoRmaItemID,

                    SUM(
                    IF (sales_order_item.price_incl_tax IS NULL, (((((sales_order_item.price * sales_order_item.qty_ordered) -
                    discount_amount) / sales_order_item.qty_ordered) * (magento_rma_item_entity.qty_requested))),
                    (((((sales_order_item.price_incl_tax * sales_order_item.qty_ordered) -
                    discount_amount) / sales_order_item.qty_ordered) * (magento_rma_item_entity.qty_requested))))
                    ) as order_return_value

                    FROM magento_rma_item_entity
                    LEFT JOIN sales_order_item ON magento_rma_item_entity.order_item_id = sales_order_item.item_id
                    GROUP BY magentoRmaItemID
                )'
            );
            // @codingStandardsIgnoreStop
            $subQueryPaymentMethod = new \Zend_Db_Expr(
                '(
                    SELECT
                    sales_order_payment.parent_id as magentoPaymentOrderId,
                    JSON_EXTRACT(additional_information, "$.method_title") AS payment_method
                    FROM sales_order_payment
                    GROUP BY magentoPaymentOrderId
                )'
            );

            /** @var $collection \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection */
            $collection = $this->_collectionFactory->create();
            $collection->getSelect()
                ->joinLeft(
                    ['rmaShippingLabelTable' => $subQueryRmaShippingLabel],
                    "main_table.entity_id = rmaShippingLabelTable.rma_entity_id"
                );
            $collection->getSelect()
                ->joinLeft(
                    ['rmaItemTable' => $subQueryRmaItem],
                    "main_table.entity_id = rmaItemTable.magentoRmaItemEntityId"
                );
            $collection->getSelect()
                ->joinLeft(
                    ['rmaItemReturnValue' => $subQueryRmaReturnValue],
                    "main_table.entity_id = rmaItemReturnValue.magentoRmaItemID"
                );
            $collection->getSelect()
            ->joinLeft(
                ['orderPaymentTable' => $subQueryPaymentMethod],
                "main_table.order_id = orderPaymentTable.magentoPaymentOrderId"
            );
            $this->setCollection($collection);
        }
        parent::_beforePrepareCollection();
    }

    /**
     * Add grid columns
     *
     * @return RmaGrid
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'track_number',
            [
                'header' => __('AWB Number'),
                'index' => 'track_number',
                'type' => 'text',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'customer_name'
        );
        $this->addColumnAfter(
            'order_return_value',
            [
                'header' => __('Total Return Value'),
                'index' => 'order_return_value',
                'type' => 'currency',
                'renderer' => Currency::class,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'customer_name'
        );
        $this->addColumnAfter(
            'store_id',
            [
                'header' => __('Store Name'),
                'index' => 'store_id',
                'type' => 'options',
                'renderer' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store::class,
                'options' =>$this->store->toOptionArray(),
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'customer_name'
        );
        $this->addColumnAfter(
            'return_items',
            [
                'header' => __('Number of Items (Requested)'),
                'index' => 'return_items',
                'type' => 'text',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'customer_name'
        );
        $this->addColumnAfter(
            'payment_method',
            [
                'header' => __('Payment Method'),
                'index' => 'payment_method',
                'type' => 'text',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ],
            'track_number'
        );

        return parent::_prepareColumns();
    }
}

