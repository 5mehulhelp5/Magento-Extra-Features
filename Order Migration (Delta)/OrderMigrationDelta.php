<?php

use Exception as ExceptionAlias;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get(State::class);
$state->setAreaCode(Area::AREA_FRONTEND);
try {
    $om = $objectManager;
    /** @var LoggerInterface $logger */
    $logger = $om->create(LoggerInterface::class);
    $pageSize = 1;
    $pageNum = 1;
    $hasData = true;
    /** @var AdapterInterface $connection */
    $connection = $om->create(ResourceConnection::class)->getConnection();
    $orderTableName = $connection->getTableName('sales_flat_order_m1');
    while ($hasData) {
        $select = $connection
            ->select()
            ->from($orderTableName)
            ->where("created_at >= ?", "2024-04-11")
            ->limitPage($pageNum, $pageSize);
        $result = $connection->fetchAll($select);
        if ($result) {
            processOrders($connection, $result, $logger);
            $logger->info("Page number #" . $pageNum . " completed with " . $pageSize . " rows.");
            echo "\n Page number " . $pageNum . " completed with " . $pageSize . " rows.\n";
            $pageNum++;
            $hasData = true;
        } else {
            $hasData = false;
        }
    }
} catch (ExceptionAlias $e) {
    echo "\n" . $e->getMessage() . "\n";
}

/**
 * Process Order Migration
 *
 * @param AdapterInterface $connection
 * @param array $rows
 * @param LoggerInterface $logger
 *
 * @return void
 */
function processOrders(AdapterInterface $connection, array $rows, LoggerInterface $logger): void
{
    foreach ($rows as $row) {
        $orderId = (int)$row['entity_id'];
        $salesOrderData = getOrderData($row);
        try {
            $newOrderId = insertOrderData($connection, $salesOrderData, $logger, 'sales_order', false);
        } catch (\Exception $e) {
            $logger->error("Order Migration failed for order ID: " . $orderId,  [
                "message" => $e->getMessage()
            ]);
            echo "\n\tOrder Migration failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            continue;
        }
        if ($newOrderId) {
            try {
                $orderItemData = getItemsData($connection, $row, $newOrderId);
                insertOrderData($connection, $orderItemData, $logger, 'sales_order_item', true);
            }  catch (\Exception $e) {
                $logger->error("Order Items Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Items Migration failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                $orderAddressData = getAddressData($connection, $row, $newOrderId);
                insertOrderData($connection, $orderAddressData, $logger, 'sales_order_address', true);
            }  catch (\Exception $e) {
                $logger->error("Order Address Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Address Migration failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                $orderPaymentData = getPaymentData($connection, $row, $newOrderId);
                insertOrderData($connection, $orderPaymentData, $logger, 'sales_order_payment', false);
            } catch (\Exception $e) {
                $logger->error("Order Payment Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Payment Migration failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                $orderGridData = getSalesOrderGridData($connection, $row, $newOrderId);
                insertOrderData($connection, $orderGridData, $logger, 'sales_order_grid', false);
            } catch (\Exception $e) {
                $logger->error("Order Grid Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Grid Migration failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                $orderShipmentData = getShipmentData($connection, $row, $newOrderId);
                if ($orderShipmentData) {
                    insertOrderData($connection, $orderShipmentData, $logger, 'sales_shipment', true);
                }
            } catch (\Exception $e) {
                $orderShipmentData = [];
                $logger->error("Order Shipment Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Shipment Shipment failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                if ($orderShipmentData) {
                    $shipmentIds = getShipmentIds($orderShipmentData);
                    $orderShipmentItemData = getShipmentItemData($connection, $row, $shipmentIds);
                    insertOrderData($connection, $orderShipmentItemData, $logger, 'sales_shipment_item', true);
                }

            } catch (\Exception $e) {
                $logger->error("Order Shipment Items Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Shipment Items Shipment failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                if ($orderShipmentData) {
                    $orderShipmentTrackData = getShipmentTrackData($connection, $row, $newOrderId);
                    insertOrderData($connection, $orderShipmentTrackData, $logger, 'sales_shipment_track', true);
                }
            } catch (\Exception $e) {
                $logger->error("Order Shipment Track Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Shipment Track Shipment failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            try {
                if ($orderShipmentData) {
                    $orderShipmentGridData = getShipmentGridData($connection, $row, $newOrderId);
                    insertOrderData($connection, $orderShipmentGridData, $logger, 'sales_shipment_grid', true);
                }
            } catch (\Exception $e) {
                $logger->error("Order Shipment Track Migration failed for order ID: " . $orderId,  [
                    "message" => $e->getMessage()
                ]);
                echo "\n\tOrder Shipment Track Shipment failed for order ID: " . $orderId . " | Error: " . $e->getMessage() . "\n";
            }
            $logger->info("New Order created for Order ID: " . $newOrderId, [
                "old_order_id" => $orderId
            ]);
            echo "\nNew Order ID: " . $newOrderId . " created for Order ID: " . $orderId . "\n";
        } else {
            $logger->info("New Order creation failed for Order ID: " . $orderId);
            echo "\nNew Order creation failed for Order ID: " . $orderId . "\n";
        }
    }
}

/**
 * Get Shipment ID
 *
 * @param array $orderShipmentData
 * @return array|null
 */
function getShipmentIds(array $orderShipmentData): ?array
{
    $shipmentIds = [];
    foreach ($orderShipmentData as $row) {
        $shipmentIds[] = $row['entity_id'];
    }

    return $shipmentIds;
}

/**
 * Insert order data in DB
 *
 * @param AdapterInterface $connection
 * @param array $data
 * @param LoggerInterface $logger
 * @param string $tableName
 * @param bool $multi
 *
 * @return int|null
 */
function insertOrderData(AdapterInterface $connection, array $data, LoggerInterface $logger, string $tableName, bool $multi = false): ?int
{
    $tableName = $connection->getTableName($tableName);
    try {
        if ($multi) {
            foreach ($data as $row) {
                $connection->insertOnDuplicate($tableName, $row);
            }
        } else {
            $connection->insertOnDuplicate($tableName, $data);
        }

        return (int)$connection->lastInsertId($tableName);
    } catch (\Exception $e) {
        $logger->error("Error occurred during order data insert. ", [
            "message" => $e->getMessage(),
            "data" => $data
        ]);
    }

    return null;
}

/**
 * Get sales order grid data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getSalesOrderGridData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $orderId = $row['entity_id'];
    $row = getDbData($connection, 'sales_flat_order_grid', 'entity_id', $orderId);
    if ($row) {
        $row['entity_id'] = $newOrderId;

        return $row;
    }

    return null;
}

/**
 * Get order data
 *
 * @param array $row
 * @return array|null
 */
function getOrderData(array $row): ?array
{
    if ($row) {
        return [
            'entity_id' => $row['entity_id'],
            'state' => $row['state'],
            'status' => $row['status'],
            'coupon_code' => $row['coupon_code'],
            'protect_code' => $row['protect_code'],
            'shipping_description' => $row['shipping_description'],
            'is_virtual' => $row['is_virtual'],
            'store_id' => $row['store_id'],
            'customer_id' => $row['customer_id'],
            'base_discount_amount' => $row['base_discount_amount'],
            'base_discount_canceled' => $row['base_discount_canceled'],
            'base_discount_invoiced' => $row['base_discount_invoiced'],
            'base_discount_refunded' => $row['base_discount_refunded'],
            'base_grand_total' => $row['base_grand_total'],
            'base_shipping_amount' => $row['base_shipping_amount'],
            'base_shipping_canceled' => $row['base_shipping_canceled'],
            'base_shipping_invoiced' => $row['base_shipping_invoiced'],
            'base_shipping_refunded' => $row['base_shipping_refunded'],
            'base_shipping_tax_amount' => $row['base_shipping_tax_amount'],
            'base_shipping_tax_refunded' => $row['base_shipping_tax_refunded'],
            'base_subtotal' => $row['base_subtotal'],
            'base_subtotal_canceled' => $row['base_subtotal_canceled'],
            'base_subtotal_invoiced' => $row['base_subtotal_invoiced'],
            'base_subtotal_refunded' => $row['base_subtotal_refunded'],
            'base_tax_amount' => $row['base_tax_amount'],
            'base_tax_canceled' => $row['base_tax_canceled'],
            'base_tax_invoiced' => $row['base_tax_invoiced'],
            'base_tax_refunded' => $row['base_tax_refunded'],
            'base_to_global_rate' => $row['base_to_global_rate'],
            'base_to_order_rate' => $row['base_to_order_rate'],
            'base_total_canceled' => $row['base_total_canceled'],
            'base_total_invoiced' => $row['base_total_invoiced'],
            'base_total_invoiced_cost' => $row['base_total_invoiced_cost'],
            'base_total_offline_refunded' => $row['base_total_offline_refunded'],
            'base_total_online_refunded' => $row['base_total_online_refunded'],
            'base_total_paid' => $row['base_total_paid'],
            'base_total_qty_ordered' => $row['base_total_qty_ordered'],
            'base_total_refunded' => $row['base_total_refunded'],
            'discount_amount' => $row['discount_amount'],
            'discount_canceled' => $row['discount_canceled'],
            'discount_invoiced' => $row['discount_invoiced'],
            'discount_refunded' => $row['discount_refunded'],
            'grand_total' => $row['grand_total'],
            'shipping_amount' => $row['shipping_amount'],
            'shipping_canceled' => $row['shipping_canceled'],
            'shipping_invoiced' => $row['shipping_invoiced'],
            'shipping_refunded' => $row['shipping_refunded'],
            'shipping_tax_amount' => $row['shipping_tax_amount'],
            'shipping_tax_refunded' => $row['shipping_tax_refunded'],
            'store_to_base_rate' => $row['store_to_base_rate'],
            'store_to_order_rate' => $row['store_to_order_rate'],
            'subtotal' => $row['subtotal'],
            'subtotal_canceled' => $row['subtotal_canceled'],
            'subtotal_invoiced' => $row['subtotal_invoiced'],
            'subtotal_refunded' => $row['subtotal_refunded'],
            'tax_amount' => $row['tax_amount'],
            'tax_canceled' => $row['tax_canceled'],
            'tax_invoiced' => $row['tax_invoiced'],
            'tax_refunded' => $row['tax_refunded'],
            'total_canceled' => $row['total_canceled'],
            'total_invoiced' => $row['total_invoiced'],
            'total_offline_refunded' => $row['total_offline_refunded'],
            'total_online_refunded' => $row['total_online_refunded'],
            'total_paid' => $row['total_paid'],
            'total_qty_ordered' => $row['total_qty_ordered'],
            'total_refunded' => $row['total_refunded'],
            'can_ship_partially' => $row['can_ship_partially'],
            'can_ship_partially_item' => $row['can_ship_partially_item'],
            'customer_is_guest' => $row['customer_is_guest'],
            'customer_note_notify' => $row['customer_note_notify'],
            'billing_address_id' => $row['billing_address_id'],
            'customer_group_id' => $row['customer_group_id'],
            'edit_increment' => $row['edit_increment'],
            'email_sent' => $row['email_sent'],
            'send_email' => null,
            'forced_shipment_with_invoice' => $row['forced_shipment_with_invoice'],
            'payment_auth_expiration' => $row['payment_auth_expiration'],
            'quote_address_id' => $row['quote_address_id'],
            'quote_id' => $row['quote_id'],
            'shipping_address_id' => $row['shipping_address_id'],
            'adjustment_negative' => $row['adjustment_negative'],
            'adjustment_positive' => $row['adjustment_positive'],
            'base_adjustment_negative' => $row['base_adjustment_negative'],
            'base_adjustment_positive' => $row['base_adjustment_positive'],
            'base_shipping_discount_amount' => $row['base_shipping_discount_amount'],
            'base_subtotal_incl_tax' => $row['base_subtotal_incl_tax'],
            'base_total_due' => $row['base_total_due'],
            'payment_authorization_amount' => $row['payment_authorization_amount'],
            'shipping_discount_amount' => $row['shipping_discount_amount'],
            'subtotal_incl_tax' => $row['subtotal_incl_tax'],
            'total_due' => $row['total_due'],
            'weight' => $row['weight'],
            'customer_dob' => $row['customer_dob'],
            'increment_id' => $row['increment_id'],
            'applied_rule_ids' => $row['applied_rule_ids'],
            'base_currency_code' => $row['base_currency_code'],
            'customer_email' => $row['customer_email'],
            'customer_firstname' => $row['customer_firstname'],
            'customer_lastname' => $row['customer_lastname'],
            'customer_middlename' => $row['customer_middlename'],
            'customer_prefix' => $row['customer_prefix'],
            'customer_suffix' => $row['customer_suffix'],
            'customer_taxvat' => $row['customer_taxvat'],
            'discount_description' => $row['discount_description'],
            'ext_customer_id' => $row['ext_customer_id'],
            'ext_order_id' => $row['ext_order_id'],
            'global_currency_code' => $row['global_currency_code'],
            'hold_before_state' => $row['hold_before_state'],
            'hold_before_status' => $row['hold_before_status'],
            'order_currency_code' => $row['order_currency_code'],
            'original_increment_id' => $row['original_increment_id'],
            'relation_child_id' => $row['relation_child_id'],
            'relation_child_real_id' => $row['relation_child_real_id'],
            'relation_parent_id' => $row['relation_parent_id'],
            'relation_parent_real_id' => $row['relation_parent_real_id'],
            'remote_ip' => $row['remote_ip'],
            'shipping_method' => $row['shipping_method'],
            'store_currency_code' => $row['store_currency_code'],
            'store_name' => $row['store_name'],
            'x_forwarded_for' => $row['x_forwarded_for'],
            'customer_note' => $row['customer_note'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'total_item_count' => $row['total_item_count'],
            'customer_gender' => $row['customer_gender'],
            'discount_tax_compensation_amount' => null,
            'base_discount_tax_compensation_amount' => null,
            'shipping_discount_tax_compensation_amount' => null,
            'base_shipping_discount_tax_compensation_amnt' => null,
            'discount_tax_compensation_invoiced' => null,
            'base_discount_tax_compensation_invoiced' => null,
            'discount_tax_compensation_refunded' => null,
            'base_discount_tax_compensation_refunded' => null,
            'shipping_incl_tax' => null,
            'base_shipping_incl_tax' => null,
            'coupon_rule_name' => $row['coupon_rule_name'],
            'base_customer_balance_amount' => null,
            'customer_balance_amount' => null,
            'base_customer_balance_invoiced' => null,
            'customer_balance_invoiced' => null,
            'base_customer_balance_refunded' => null,
            'customer_balance_refunded' => null,
            'bs_customer_bal_total_refunded' => null,
            'customer_bal_total_refunded' => null,
            'gift_cards' => null,
            'base_gift_cards_amount' => null,
            'gift_cards_amount' => null,
            'base_gift_cards_invoiced' => null,
            'gift_cards_invoiced' => null,
            'base_gift_cards_refunded' => null,
            'gift_cards_refunded' => null,
            'gift_message_id' => $row['gift_message_id'],
            'gw_id' => null,
            'gw_allow_gift_receipt' => null,
            'gw_add_card' => null,
            'gw_base_price' => null,
            'gw_price' => null,
            'gw_items_base_price' => null,
            'gw_items_price' => null,
            'gw_card_base_price' => null,
            'gw_card_price' => null,
            'gw_base_tax_amount' => null,
            'gw_tax_amount' => null,
            'gw_items_base_tax_amount' => null,
            'gw_items_tax_amount' => null,
            'gw_card_base_tax_amount' => null,
            'gw_card_tax_amount' => null,
            'gw_base_price_incl_tax' => null,
            'gw_price_incl_tax' => null,
            'gw_items_base_price_incl_tax' => null,
            'gw_items_price_incl_tax' => null,
            'gw_card_base_price_incl_tax' => null,
            'gw_card_price_incl_tax' => null,
            'gw_base_price_invoiced' => null,
            'gw_price_invoiced' => null,
            'gw_items_base_price_invoiced' => null,
            'gw_items_price_invoiced' => null,
            'gw_card_base_price_invoiced' => null,
            'gw_card_price_invoiced' => null,
            'gw_base_tax_amount_invoiced' => null,
            'gw_tax_amount_invoiced' => null,
            'gw_items_base_tax_invoiced' => null,
            'gw_items_tax_invoiced' => null,
            'gw_card_base_tax_invoiced' =>  null,
            'gw_card_tax_invoiced' => null,
            'gw_base_price_refunded' => null,
            'gw_price_refunded' => null,
            'gw_items_base_price_refunded' => null,
            'gw_items_price_refunded' => null,
            'gw_card_base_price_refunded' => null,
            'gw_card_price_refunded' => null,
            'gw_base_tax_amount_refunded' => null,
            'gw_tax_amount_refunded' => null,
            'gw_items_base_tax_refunded' => null,
            'gw_items_tax_refunded' => null,
            'gw_card_base_tax_refunded' => null,
            'gw_card_tax_refunded' => null,
            'paypal_ipn_customer_notified' => $row['paypal_ipn_customer_notified'],
            'reward_points_balance' => null,
            'base_reward_currency_amount' => null,
            'reward_currency_amount' => null,
            'base_rwrd_crrncy_amt_invoiced' => null,
            'rwrd_currency_amount_invoiced' => null,
            'base_rwrd_crrncy_amnt_refnded' => null,
            'rwrd_crrncy_amnt_refunded' => null,
            'reward_points_balance_refund' => null,
            'order_estimate' => null,
            'order_notification_sent' => 1,
            'rfq_quote_id' => null,
            'tax_file_id' => null,
            'mp_smtp_email_marketing_synced' => 0,
            'delivery_timestamp' => null,
            'delivery_utc_offset' => null,
            'exclude_import_pending' => 0,
            'exclude_import_complete' => 0,
            'penalty_amount' => 0.0000,
            'shipping_additional_data' => null,
            'stripe_radar_risk_score' => null,
            'stripe_radar_risk_level' => 'N.A',
        ];
    }

    return null;
}

/**
 * Get order items data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getItemsData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $items = [];
    $orderId = $row['entity_id'];
    $rows = getDbData($connection, 'sales_flat_order_item_m1', 'order_id', $orderId, true);
    if ($rows) {
        foreach ($rows as $row) {
            $productOptions = $row['product_options'];
            if ($productOptions) {
                $productOptions = json_encode(unserialize($productOptions));
            }
            $weeTaxApplied = $row['weee_tax_applied'];
            if ($weeTaxApplied) {
                $weeTaxApplied = json_encode(unserialize($weeTaxApplied));
            }
            $items[] =  [
                'item_id' => $row['item_id'],
                'order_id' => $orderId,
                'parent_item_id' => $row['parent_item_id'],
                'quote_item_id' => $row['quote_item_id'],
                'store_id' => $row['store_id'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'product_id' => $row['product_id'],
                'product_type' => $row['product_type'],
                'product_options' => $productOptions,
                'weight' => $row['weight'],
                'is_virtual' => $row['is_virtual'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'description' => $row['description'],
                'applied_rule_ids' => $row['applied_rule_ids'],
                'additional_data' => $row['additional_data'],
                'is_qty_decimal' => $row['is_qty_decimal'],
                'no_discount' => $row['no_discount'],
                'qty_backordered' => $row['qty_backordered'],
                'qty_canceled' => $row['qty_canceled'],
                'qty_invoiced' => $row['qty_invoiced'],
                'qty_ordered' => $row['qty_ordered'],
                'qty_refunded' => $row['qty_refunded'],
                'qty_shipped' => $row['qty_shipped'],
                'base_cost' => $row['base_cost'],
                'price' => $row['price'],
                'base_price' => $row['base_price'],
                'original_price' => $row['original_price'],
                'base_original_price' => $row['base_original_price'],
                'tax_percent' => $row['tax_percent'],
                'tax_amount' => $row['tax_amount'],
                'base_tax_amount' => $row['base_tax_amount'],
                'tax_invoiced' => $row['tax_invoiced'],
                'base_tax_invoiced' => $row['base_tax_invoiced'],
                'discount_percent' => $row['discount_percent'],
                'discount_amount' => $row['discount_amount'],
                'base_discount_amount' => $row['base_discount_amount'],
                'discount_invoiced' => $row['discount_invoiced'],
                'base_discount_invoiced' => $row['base_discount_invoiced'],
                'amount_refunded' => $row['amount_refunded'],
                'base_amount_refunded' => $row['base_amount_refunded'],
                'row_total' => $row['row_total'],
                'base_row_total' => $row['base_row_total'],
                'row_invoiced' => $row['row_invoiced'],
                'base_row_invoiced' => $row['base_row_invoiced'],
                'row_weight' => $row['row_weight'],
                'base_tax_before_discount' => $row['base_tax_before_discount'],
                'tax_before_discount' => $row['tax_before_discount'],
                'ext_order_item_id' => $row['ext_order_item_id'],
                'locked_do_invoice' => $row['locked_do_invoice'],
                'locked_do_ship' => $row['locked_do_ship'],
                'price_incl_tax' => $row['price_incl_tax'],
                'base_price_incl_tax' => $row['base_price_incl_tax'],
                'row_total_incl_tax' => $row['row_total_incl_tax'],
                'base_row_total_incl_tax' => $row['base_row_total_incl_tax'],
                'discount_tax_compensation_amount' => null,
                'base_discount_tax_compensation_amount' => null,
                'discount_tax_compensation_invoiced' => null,
                'base_discount_tax_compensation_invoiced' => null,
                'discount_tax_compensation_refunded' => null,
                'base_discount_tax_compensation_refunded' => null,
                'tax_canceled' => $row['tax_canceled'],
                'discount_tax_compensation_canceled' => null,
                'tax_refunded' => $row['tax_refunded'],
                'discount_refunded' => $row['discount_refunded'],
                'base_tax_refunded' => $row['base_tax_refunded'],
                'base_discount_refunded' => $row['base_discount_refunded'],
                'giftregistry_item_id' => null,
                'event_id' => null,
                'gift_message_id' => null,
                'gift_message_available' => $row['gift_message_available'],
                'gw_id' => null,
                'gw_base_price' => null,
                'gw_price' => null,
                'gw_base_tax_amount' => null,
                'gw_tax_amount' => null,
                'gw_base_price_invoiced' => null,
                'gw_price_invoiced' => null,
                'gw_base_tax_amount_invoiced' => null,
                'gw_tax_amount_invoiced' => null,
                'gw_base_price_refunded' => null,
                'gw_price_refunded' => null,
                'gw_base_tax_amount_refunded' => null,
                'gw_tax_amount_refunded' => null,
                'weee_tax_applied' => $weeTaxApplied,
                'weee_tax_applied_amount' => $row['weee_tax_applied_amount'],
                'weee_tax_applied_row_amount' => $row['weee_tax_applied_row_amount'],
                'weee_tax_disposition' => $row['weee_tax_disposition'],
                'weee_tax_row_disposition' => $row['weee_tax_row_disposition'],
                'base_weee_tax_applied_amount' => $row['base_weee_tax_applied_amount'],
                'base_weee_tax_applied_row_amnt' => $row['base_weee_tax_applied_row_amnt'],
                'base_weee_tax_disposition' => $row['base_weee_tax_disposition'],
                'base_weee_tax_row_disposition' => $row['base_weee_tax_row_disposition'],
                'free_shipping' => $row['free_shipping'],
                'qty_returned' => 0.0000,
                'initial_fee' => null,
                'base_initial_fee' => null,
                'initial_fee_tax' => null,
                'base_initial_fee_tax' => null,
            ];
        }

        return $items;
    }

    return null;
}

/**
 * Get Payment Data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getPaymentData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $orderId = $row['entity_id'];
    $row = getDbData($connection, 'sales_flat_order_payment_m1', 'parent_id', $orderId);
    if ($row) {
        $additionalInformation = $row['additional_information'];
        if ($additionalInformation) {
            $additionalInformation = json_encode(unserialize($additionalInformation));
        }
        return [
            "parent_id" => $newOrderId,
            "base_shipping_captured" => $row['base_shipping_captured'],
            "shipping_captured" => $row['shipping_captured'],
            "base_amount_paid" => $row['base_amount_paid'],
            "amount_canceled" => $row['amount_canceled'],
            "base_amount_authorized" => $row['base_amount_authorized'],
            "base_amount_paid_online" => $row['base_amount_paid_online'],
            "base_amount_refunded_online" => $row['base_amount_refunded_online'],
            "amount_paid" => $row['amount_paid'],
            "amount_authorized" => $row['amount_authorized'],
            "base_amount_ordered" => $row['base_amount_ordered'],
            "amount_ordered" => $row['amount_ordered'],
            "base_amount_canceled" => $row['base_amount_canceled'],
            "quote_payment_id" => $row['quote_payment_id'],
            "cc_exp_month" => $row['cc_exp_month'],
            "cc_ss_start_year" => $row['cc_ss_start_year'],
            "echeck_bank_name" => $row['echeck_bank_name'],
            "method" => $row['method'],
            "cc_debug_request_body" => $row['cc_debug_request_body'],
            "cc_secure_verify" => $row['cc_secure_verify'],
            "protection_eligibility" => $row['protection_eligibility'],
            "cc_approval" => $row['cc_approval'],
            "cc_last_4" => $row['cc_last4'],
            "cc_status_description" => $row['cc_status_description'],
            "echeck_type" => $row['echeck_type'],
            "cc_debug_response_serialized" => $row['cc_debug_response_serialized'],
            "cc_ss_start_month" => $row['cc_ss_start_month'],
            "echeck_account_type" => $row['echeck_account_type'],
            "last_trans_id" => $row['last_trans_id'],
            "cc_cid_status" => $row['cc_cid_status'],
            "cc_owner" => $row['cc_owner'],
            "cc_type" => $row['cc_type'],
            "po_number" => $row['po_number'],
            "cc_exp_year" => $row['cc_exp_year'],
            "cc_status" => $row['cc_status'],
            "echeck_routing_number" => $row['echeck_routing_number'],
            "account_status" => $row['account_status'],
            "anet_trans_method" => $row['anet_trans_method'],
            "cc_debug_response_body" => $row['cc_debug_response_body'],
            "cc_ss_issue" => $row['cc_ss_issue'],
            "echeck_account_name" => $row['echeck_account_name'],
            "cc_avs_status" => $row['cc_avs_status'],
            "cc_number_enc" => $row['cc_number_enc'],
            "cc_trans_id" => $row['cc_trans_id'],
            "address_status" => $row['address_status'],
            "additional_information" => $additionalInformation
        ];
    }

    return null;
}

/**
 * Get address data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getAddressData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $orderId = $row['entity_id'];
    $address = [];
    $rows = getDbData($connection, 'sales_flat_order_address_m1', 'parent_id', $orderId);
    if ($rows) {
        foreach ($rows as $row) {
            $address[] = [
                "parent_id" => $newOrderId,
                "customer_address_id" => $row['customer_address_id'],
                "quote_address_id" => $row['quote_address_id'],
                "region_id" => $row['region_id'],
                "customer_id" => $row['customer_id'],
                "fax" => $row['fax'],
                "region" => $row['region'],
                "postcode" => $row['postcode'],
                "lastname" => $row['lastname'],
                "street" => $row['street'],
                "city" => $row['city'],
                "email" => $row['email'],
                "telephone" => $row['telephone'],
                "country_id" => $row['country_id'],
                "firstname" => $row['firstname'],
                "address_type" => $row['address_type'],
                "prefix" => $row['prefix'],
                "middlename" => $row['middlename'],
                "suffix" => $row['suffix'],
                "company" => $row['company'],
                "vat_id" => $row['vat_id'],
                "vat_is_valid" => $row['vat_is_valid'],
                "vat_request_id" => $row['vat_request_id'],
                "vat_request_date" => $row['vat_request_date'],
                "vat_request_success" => $row['vat_request_success']
            ];
        }
        return $address;
    }

    return null;
}

/**
 * Get Shipment Data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getShipmentData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $orderId = $row['entity_id'];
    $shipment = [];
    $rows = getDbData($connection, 'sales_flat_shipment', 'order_id', $orderId, true);
    if ($rows) {
        foreach ($rows as $row) {
            $shipment[] = [
                'entity_id' => $row['entity_id'],
                'store_id' => $row['store_id'],
                'total_weight' => $row['total_weight'],
                'total_qty' => $row['total_qty'],
                'email_sent' => $row['email_sent'],
                'send_email' => null,
                'order_id' => $row['order_id'],
                'customer_id' => $row['customer_id'],
                'shipping_address_id' => $row['shipping_address_id'],
                'billing_address_id' => $row['billing_address_id'],
                'shipment_status' => $row['shipment_status'],
                'increment_id' => $row['increment_id'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'packages' => $row['packages'],
                'shipping_label' => $row['shipping_label'],
                'customer_note' => null,
                'customer_note_notify' => null,
                'is_delivered' => 1,
                'delivery_date' => $row['updated_at']
            ];
        }

        return $shipment;
    }

    return null;
}

/**
 * Get Shipment Items data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $shipmentId
 *
 * @return array|null
 */
function getShipmentItemData(AdapterInterface $connection, array $row, array $shipmentIds): ?array
{
    $shipmentItem = [];
    foreach ($shipmentIds as $shipmentId) {
        $rows = getDbData($connection, 'sales_flat_shipment_item', 'parent_id', $shipmentId, true);
        if ($rows) {
            foreach ($rows as $row) {
                $shipmentItem[] = [
                    'entity_id' => $row['entity_id'],
                    'parent_id' => $row['parent_id'],
                    'row_total' => $row['row_total'],
                    'price' => $row['price'],
                    'weight' => $row['weight'],
                    'qty' => $row['qty'],
                    'product_id' => $row['product_id'],
                    'order_item_id' => $row['order_item_id'],
                    'additional_data' => $row['additional_data'],
                    'description' => $row['description'],
                    'name' => $row['name'],
                    'sku' => $row['sku']
                ];
            }

        }
    }

    return $shipmentItem;
}

/**
 * Get shipment track data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getShipmentTrackData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $orderId = $row['entity_id'];
    $shipmentItem = [];
    $rows = getDbData($connection, 'sales_flat_shipment_track', 'order_id', $orderId, true);
    if ($rows) {
        foreach ($rows as $row) {
            $shipmentItem[] = [
                'entity_id' => $row['entity_id'],
                'parent_id' => $row['parent_id'],
                'weight' => $row['weight'],
                'qty' => $row['qty'],
                'order_id' => $row['order_id'],
                'track_number' => $row['track_number'],
                'description' => $row['description'],
                'title' => $row['title'],
                'carrier_code' => $row['carrier_code'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'wesupply_order_update' => 0
            ];
        }
        return $shipmentItem;
    }
    return null;
}

/**
 * Get Shipment Grid data
 *
 * @param AdapterInterface $connection
 * @param array $row
 * @param int $newOrderId
 *
 * @return array|null
 */
function getShipmentGridData(AdapterInterface $connection, array $row, int $newOrderId): ?array
{
    $orderId = $row['entity_id'];
    $shipmentGrid = [];
    $rows = getDbData($connection, 'sales_flat_shipment_grid', 'order_id', $orderId, true);
    if ($rows) {
        foreach ($rows as $row) {
            $shipmentGrid[] = [
                'entity_id' => $row['entity_id'],
                'increment_id' => $row['increment_id'],
                'store_id' => $row['store_id'],
                'order_increment_id' => $row['order_increment_id'],
                'order_id' => $row['order_id'],
                'order_created_at' => $row['order_created_at'],
                'customer_name' => $row['shipping_name'],
                'total_qty' => $row['total_qty'],
                'shipment_status' => $row['shipment_status'],
                'order_status' => null,
                'billing_address' => null,
                'shipping_address' => null,
                'billing_name' => $row['shipping_name'],
                'shipping_name' => $row['shipping_name'],
                'customer_email' => null,
                'customer_group_id' => null,
                'payment_method' => null,
                'shipping_information' => null,
                'created_at' => $row['created_at'],
                'updated_at' => null
            ];
        }
        return $shipmentGrid;
    }
    return null;
}

/**
 * Get Data from DB
 *
 * @param AdapterInterface $connection
 * @param string $tableName
 * @param string $conditionColumn
 * @param mixed $value
 * @param bool $multi
 *
 * @return array|null
 */
function getDbData(
    AdapterInterface $connection,
    string $tableName,
    string $conditionColumn,
    mixed $value,
    bool $multi = false
): ?array
{
    $tableName = $connection->getTableName($tableName);
    $select = $connection
        ->select()
        ->from($tableName)
        ->where("$conditionColumn = ?", $value);
    $result = $connection->fetchAll($select);
    if ($result) {
        if (count($result) == 1) {
            if ($multi) {
                return $result;
            } else {
                return $result[0];
            }
        } else {
            return $result;
        }
    }

    return null;
}
