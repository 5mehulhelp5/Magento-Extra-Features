<?php
/**
 * Candere Software
 *
 * @category Codilar
 * @package  CustomReports
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace Codilar\CustomReports\Block\Adminhtml\Usage;

use Magento\Framework\App\RequestInterface;

class Master extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_connection;

    public const TODAY = 'today';

    public const LAST_MONTH = 'lastMonth';

    public const THIS_MONTH = 'thisMonth';

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var string
     */
    protected $_categories='';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scope
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        \Magento\Eav\Model\Config $eavConfig,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->scope=$scope;
        $this->_connection = $resource->getConnection();
        $this->eavConfig = $eavConfig;
        $this->request = $request;
    }

    /**
     * Get sales Data
     *
     * @return array
     */
    public function getSalesData()
    {
        $myTable = $this->_connection->getTableName('sales_order');
        $sql     = $this->_connection->select()->from(
            ["tn" => $myTable]
        );
        $result  = $this->_connection->fetchAll($sql);
        return $result;
    }

    /**
     * Get order state list
     *
     * @return array
     */
    public function getAllOrderStateList()
    {
        // phpcs:ignore
        $state = "SELECT  distinct state FROM sales_order_status_state where state!='NULL' ";

        $resultstate =$this->_connection->fetchAll($state);
        $resultstate[]['state'] = 'All';
        return $resultstate;
    }

    /**
     * Get all order status list
     *
     * @return array
     */
    public function getAllOrderStatusList()
    {
        // phpcs:ignore
        $status = "SELECT  distinct status FROM sales_order_status_state where status!='NULL' ";

        $resultstatus =$this->_connection->fetchAll($status);
        $resultstatus[]['status'] = 'All';
        return $resultstatus;
    }

    /**
     * Get total orders count
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getTotalOrdersCount($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT COUNT(entity_id) FROM `sales_order` WHERE `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=0) ? $result : 0;
    }

    /**
     * Get GDPR orders count
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getTotalOrdersCountDGRP($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT COUNT(sales_order.entity_id) FROM (sales_order sales_order INNER JOIN sales_order_payment sales_order_payment ON (sales_order_payment.parent_id = sales_order.entity_id)) WHERE sales_order_payment.method = 'dgrp' AND `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=0) ? $result : 0;
    }

    /**
     * Get Non GDPR orders count
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getTotalOrdersCountNonDGRP(
        $order_status,
        $order_state,
        $mysql_date_from,
        $mysql_date_to,
        $condition
    ) {
        // phpcs:ignore
        $sql = "SELECT COUNT(sales_order.entity_id) FROM (sales_order sales_order INNER JOIN sales_order_payment sales_order_payment ON (sales_order_payment.parent_id = sales_order.entity_id)) WHERE sales_order_payment.method != 'dgrp' AND `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=0) ? $result : 0;
    }

    /**
     * Get Sum Base Grand Total
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getSumBaseGrandTotal($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT SUM(base_grand_total) FROM `sales_order` WHERE `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=null) ? $result : 0;
    }

    /**
     * Get Sum Base Grand Total GDPR
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getSumBaseGrandTotalDGRP($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT AVG(sales_order.base_grand_total) FROM (sales_order sales_order INNER JOIN sales_order_payment sales_order_payment ON (sales_order_payment.parent_id = sales_order.entity_id)) WHERE sales_order_payment.method = 'dgrp' AND `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=null) ? $result : 0;
    }

    /**
     * Get Sum Base Grand Total Non GDPR
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getSumBaseGrandTotalNonDGRP(
        $order_status,
        $order_state,
        $mysql_date_from,
        $mysql_date_to,
        $condition
    ) {
        // phpcs:ignore
        $sql = "SELECT AVG(sales_order.base_grand_total) FROM (sales_order sales_order INNER JOIN sales_order_payment sales_order_payment ON (sales_order_payment.parent_id = sales_order.entity_id)) WHERE sales_order_payment.method != 'dgrp' AND `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=null) ? $result : 0;
    }

    /**
     * Get Sum base Total Paid
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getSumBaseTotalPaid($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT SUM(base_total_paid) FROM `sales_order` WHERE `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=null) ? $result : 0;
    }

    /**
     * Get Sum Insurance
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getSumInsurance($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT SUM(insurance) FROM `sales_order` WHERE `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=null) ? $result : 0;
    }

    /**
     * Get Average Gold Rate
     *
     * @param string $order_status
     * @param string $order_state
     * @param string $mysql_date_from
     * @param string $mysql_date_to
     * @param string $condition
     * @return float|null
     */
    public function getAverageGoldRate($order_status, $order_state, $mysql_date_from, $mysql_date_to, $condition)
    {
        // phpcs:ignore
        $sql = "SELECT AVG(gold_rate) FROM `sales_order` WHERE `status` = '". $order_status ."' AND `state` = '". $order_state ."' AND`created_at` BETWEEN ('". $mysql_date_from ."' AND '". $mysql_date_to ."') ". $condition;
        $result  = $this->_connection->fetchone($sql);

        return ($result!=null) ? $result : 0;
    }

    /**
     * Get Order list
     *
     * @return array
     */
    public function getorderlist()
    {
        $data=[];
        $date_from = '';
        $date_to = '';
        $order_status = '';
        $order_state = '';
        $errorMessage='';
         
        $data['errorMessage']=$errorMessage;
            
        if (!empty($this->request->getParams())) {
            $requestParams = $this->request->getParams();
            $date_from = $requestParams['from'];
            $date_to = $requestParams['to'];
            if ($requestParams['order_status'][0]=='All') {
                $requestParams['order_status'] = [
                    'complete',
                    'processing',
                    'new',
                    'pending_payment',
                    'closed',
                    'canceled',
                    'holded',
                    'payment_review'
                ];
            }
            if ($requestParams['order_state'][0]=='All') {
                $requestParams['order_state'] = array_column($this->getAllOrderStatusList(), 'status');
            }
            $order_status = $requestParams['order_status'];
            $order_state = $requestParams['order_state'];
            $status=$requestParams['channel'];
                
            $order_status     = implode("','", $order_status);
            $order_state     = implode("','", $order_state);
            $date_from         = strtotime($date_from)-86400;
            $date_to         = strtotime($date_to);
            
            $mysql_date_from = date('Y-m-d', $date_from).' 18:30:00';
            $mysql_date_to = date('Y-m-d', $date_to).' 18:30:00';

            $condition="";
            if ($status == 'marketplace') {
                $condition = 'AND sales_order.mktplace_order_id IS NOT NULL';
            } elseif ($status == 'direct') {
                $condition = "AND sales_order.mktplace_order_id IS NULL";
            } elseif ($status == 'corporate') {
                $condition = "AND  sales_order.customer_email = 'corporate@candere.com'";
            }

            $totalOrders = $this->getTotalOrdersCount(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $sumBaseGrandTotal = $this->getSumBaseGrandTotal(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $sumBaseTotalPaid = $this->getSumBaseTotalPaid(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $sumInsurance = $this->getSumInsurance(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $averageGoldRate = $this->getAverageGoldRate(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $averageTicketValue = $sumBaseGrandTotal/$totalOrders;
            $totalOrdersDGRP = $this->getTotalOrdersCountDGRP(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $sumBaseGrandTotalDGRP = $this->getSumBaseGrandTotalDGRP(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $averageTicketValueDGRP = ($totalOrdersDGRP > 0) ? $sumBaseGrandTotalDGRP/$totalOrdersDGRP : 0;
            $totalOrdersNonDGRP = $this->getTotalOrdersCountNonDGRP(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $sumBaseGrandTotalNonDGRP = $this->getSumBaseGrandTotalNonDGRP(
                $order_status,
                $order_state,
                $mysql_date_from,
                $mysql_date_to,
                $condition
            );
            $averageTicketValueNonDGRP = $sumBaseGrandTotalNonDGRP/$totalOrdersNonDGRP;

            $data['total_orders'] = $totalOrders;
            $data['base_grand_total'] = $sumBaseGrandTotal;
            $data['base_total_paid'] = $sumBaseTotalPaid;
            $data['insurance'] = $sumInsurance;
            $data['gold_rate'] = $averageGoldRate;
            $data['average_ticket_value'] = $averageTicketValue;
            $data['average_ticket_value_dgrp'] = $averageTicketValueDGRP;
            $data['average_ticket_value_non_dgrp'] = $averageTicketValueNonDGRP;

            return $data;
        }
    }
}
