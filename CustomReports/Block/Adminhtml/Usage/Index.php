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

use Codilar\JewelleryProduct\Helper\Data as Data;
use Magento\Framework\App\RequestInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_connection;

    public const TODAY = 'today';
    public const LAST_MONTH = 'lastMonth';
    public const THIS_MONTH = 'thisMonth';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categorycollection;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $_categories='';

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scope
     * @param Data $dataHelper
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categorycollection
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        Data $dataHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categorycollection,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->scope=$scope;
        $this->_connection = $resource->getConnection();
        $this->_categorycollection = $_categorycollection;
        $this->eavConfig = $eavConfig;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
    }
     
    /**
     * Get all orders data
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
     * Get list of all order state
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
     * Get list of all order status
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
     * Get Payment Method list
     *
     * @return array
     */
    public function getPaymentMethodList()
    {
        $paymethod= $this->scope->getValue('payment');
        $paymethod['All']  = ["All" => "All"];
        
        return $paymethod;
    }

    /**
     * Get Categories for dropdown
     *
     * @return array
     */
    public function getCategoryForDropdown()
    {
        // get list of all the categories
        $categoriesArray = $this->_categorycollection->create();
        $categoriesArray->addAttributeToSelect('*');
        foreach ($categoriesArray as $categoryId => $category) {
            if ($category->getName() != '') {
                if ($categoryId==121) {
                    $this->_categories[$categoryId] = $category->getName();
                }
                $this->getChildrenCategories($category);
            }
        }
        return $this->_categories;
    }

    /**
     * Get children categories
     *
     * @param object $category1
     */
    public function getChildrenCategories($category1)
    {
        $children = $category1->getChildrenCategories();
        if (count($children) > 0) {
            foreach ($children as $child) {
                $this->_categories[$child->getId()] = $child->getName();
                $this->getChildrenCategories($child);
            }
        }
    }

    /**
     * Get Attributes options
     *
     * @param string $attribute_code
     * @return array
     */
    public function getAttributesAllOptions($attribute_code)
    {
        $option_Arr=[];

        $attribute_data = $this->eavConfig->getAttribute('catalog_product', $attribute_code);
        $options = $attribute_data->getSource()->getAllOptions(true, true);

        foreach ($options as $option) {
            if ($option['value']!='') {
                $option_Arr[$option['value']]=$option['label'];
            }
        }
   
        return $option_Arr;
    }

    /**
     * Get Categories Name
     *
     * @param array $categories_ids
     * @param array $categoriesArr
     * @return array
     */
    public function getCategoriesName($categories_ids, $categoriesArr)
    {
        $cNameArr=[];
        if ($categories_ids!='') {
            $cIdsArr=explode(',', $categories_ids);
            foreach ($cIdsArr as $key => $value) {
                if (array_key_exists($value, $categoriesArr)) {
                    $cNameArr[]=$categoriesArr[$value];
                }
            }
        }
        return $cNameArr;
    }
  
    /**
     * Get Material type list with varient
     *
     * @return void
     */
    public function getListofMaterialTypeWithVarient()
    {
        $varientArrTypesArr=[];
        $considerMaterialTypeList=$this->dataHelper->getProductMainCategoryArr();
        if (count($considerMaterialTypeList) > 0) {
            $considerMaterialKey = array_column($considerMaterialTypeList, 'name');
            $considerMaterialValue = array_column($considerMaterialTypeList, 'identifier');
            $varientArrTypesArr = array_combine($considerMaterialKey, $considerMaterialValue);
            return $varientArrTypesArr;
        }
        return $varientArrTypesArr;
    }

    /**
     * Get Order List
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
        $requestArr=[];
        $errorMessage='';
        $isSales=0;
        $isOrderSourceMedium=0;
         
        $data['errorMessage']=$errorMessage;
       
            $isSales=0;
            
            $isOrderSourceMedium=0;
            
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

            $requestArr=$this->getRequest()->getPostValue();
                
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

            if (count($requestParams['paymentMethod'])>0) {
                if ($requestParams['paymentMethod'][0]!='All') {
                    $paymentMethod="'".implode("','", $requestParams['paymentMethod'])."'";
                    $condition = "AND sales_order_payment.method in (".$paymentMethod.")";
                }
            }
        
            $sql = "SELECT
            sales_order.increment_id AS order_id,
            sales_order.base_grand_total,
            sales_order.base_total_paid,
            sales_order.grand_total,
            sales_order.total_paid,
            sales_order.discount_amount,
            sales_order.base_discount_amount,
            sales_order.insurance,
            sales_order.order_currency_code,
            sales_order.base_currency_code,
            sales_order.customer_firstname,
            sales_order.customer_lastname,
            sales_order.customer_email,
            sales_order.customer_id,
            sales_order.mktplace_order_id,
            sales_order.state,
            sales_order.status,
            sales_order.device,
            sales_order_address.telephone,
            sales_order_address.city,
            sales_order_address.region,
            sales_order_address.country_id,
            sales_order.agent_name,
            sales_order.is_admin_created,
            sales_order.is_ga_pass as is_ga_pass,
			sales_order.is_other_pass as is_other_pass,
            sales_order.entity_id as magento_order_id,
            sales_order.affiliate_source as order_affiliate_source,
            sales_order.affiliate_medium as order_affiliate_medium,
            sales_order.affiliate_id as order_affiliate_campaign,
            sales_order.purchase_source as purchase_source,
            GROUP_CONCAT(sales_order_item.sku SEPARATOR',') AS sku,
            GROUP_CONCAT((sales_order_item.base_row_total) SEPARATOR',') AS sub_total,
            GROUP_CONCAT(sales_order_item.name SEPARATOR',') AS name,
            GROUP_CONCAT(sales_order_item.product_id SEPARATOR',') AS product_id,
            GROUP_CONCAT(if(is_priority_shipping=1,'YES','NO')) AS is_priority_shipping,
            GROUP_CONCAT(CONVERT(sales_order_item.qty_ordered,SIGNED) SEPARATOR',') AS qty_ordered,

            CONCAT(
            if(sales_order.coupon_code IS NULL,'',sales_order.coupon_code)
            ) AS coupon_code,

            DATE_ADD(sales_order.created_at, INTERVAL '05:30' HOUR_MINUTE) AS created_at,
            sales_order_payment.method AS payment_method,
            sales_order_status.label as order_status
            FROM ( sales_order sales_order
            INNER JOIN sales_order_status sales_order_status
            ON (sales_order.status = sales_order_status.status))
            INNER JOIN sales_order_payment sales_order_payment
            ON (sales_order_payment.parent_id = sales_order.entity_id)
            INNER JOIN
            sales_order_item
            ON (sales_order.entity_id = sales_order_item.order_id)
            INNER JOIN sales_order_address sales_order_address
            ON (sales_order_address.parent_id = sales_order.entity_id)
            where sales_order_address.address_type = 'billing' $condition
            AND sales_order.state in ('$order_state') AND sales_order.status in ('$order_status')
            And (sales_order.created_at between '$mysql_date_from' AND '$mysql_date_to')
            AND sales_order_item.sku != 'RP0001' 
            GROUP BY sales_order.increment_id
            ORDER BY sales_order.entity_id DESC";
           
            $result = $this->_connection->fetchAll($sql);

            if (count($result) > 0) {

                $productDetails=[];
                $listPaymentMethods=[];
                $payments= $this->scope->getValue('payment');
                foreach ($payments as $paymentCode => $payment) {
                    $listPaymentMethods[]=$paymentCode;
                }

                $realOrderIdsArr = array_column($result, 'order_id');
                $commaRealOrderIds=implode(',', $realOrderIdsArr);
                // $sql_erp="SELECT order_id FROM erp_order where order_id in (".$commaRealOrderIds.")";
                // $resultsErp = $this->db->query($sql_erp);
                // $isErpInfoArr_1 =$resultsErp->result_array();
                // $isErpInfoArr = array_map (function($value){  return $value['order_id'];} , $isErpInfoArr_1);

                $magento_order_idArr = array_column($result, 'magento_order_id');
                $magento_order_idIds=implode(',', $magento_order_idArr);
                // phpcs:ignore
                $sql_shipping_address="SELECT parent_id, firstname, lastname  FROM sales_order_address where address_type = 'shipping' and parent_id in (".$magento_order_idIds.")";
                $ShippingAddressArr_1 =$this->_connection->fetchAll($sql_shipping_address);
                $data['shippingAddressArr']=$ShippingAddressArr_1;

                // phpcs:ignore
                $sqlTotals="SELECT sum(base_grand_total) as sumOfBaseGrandTotal,sum(base_total_paid) as sumOfBaseTotalPaid , sum(insurance) as total_insuranceamount , sum(gold_rate) as sumOfGoldRate,
                SUM(CASE WHEN sales_order_payment.method = 'emi' THEN base_grand_total ELSE 0 END) AS AVGVALDGRP, 
                SUM(CASE WHEN sales_order_payment.method != 'emi' THEN base_grand_total ELSE 0 END) AS AVGVALNONDGRP, 
                COUNT(CASE WHEN sales_order_payment.method = 'emi' THEN 1  END) AS COUNTDGRP, 
                COUNT(CASE WHEN sales_order_payment.method != 'emi' THEN 1 END) AS COUNTNONDGRP
                FROM sales_order sales_order INNER JOIN sales_order_payment sales_order_payment
                ON (sales_order_payment.parent_id = sales_order.entity_id)
            where increment_id in ('.$commaRealOrderIds.')";
                $resAmountTotal =$this->_connection->fetchAll($sqlTotals);

                $productIdsArr = array_column($result, 'product_id');
                $commaProductIds=implode(',', $productIdsArr);

                if ($isSales==1) {
                    $ProductTypeoptionArr=$this->getAttributesAllOptions('candere_product_type');
                    $materialOptionArr=$this->getAttributesAllOptions('material');
            
                    $materialOptionArr=$this->getAttributesAllOptions('material');
                    // phpcs:ignore
                    $sqlVarientTypes="SELECT catalog_product_entity_int.row_id as product_id, catalog_product_entity_int.value as material_id FROM catalog_product_entity_int catalog_product_entity_int WHERE catalog_product_entity_int.row_id in ('.$commaProductIds.')";
                    $resultsVarientTypesArr =$this->_connection->fetchAll($sqlVarientTypes);

                    $varientArrTypesArr=$this->getListofMaterialTypeWithVarient();
                    foreach ($resultsVarientTypesArr as $key_v => $value_v) {
                        $productDetails[$value_v['product_id']]['varientType']=$is_type;
                        if (array_key_exists($value_v['product_id'], $productDetails)) {
                            $productDetails[$value_v['product_id']]['material_id']=$value_v['material_id'];
                            $materialName=$materialOptionArr[$value_v['material_id']];
                            $productDetails[$value_v['product_id']]['varientType']=$varientArrTypesArr[$materialName];
                        } else {
                            $productDetails[$value_v['product_id']]['material_id']=$value_v['material_id'];
                            $materialName=$materialOptionArr[$value_v['material_id']];
                            $productDetails[$value_v['product_id']]['varientType']=$varientArrTypesArr[$materialName];
                        }
                    }

                    /* get Candere product Type  */
                    // phpcs:ignore
                    $sqlProductTypes="SELECT catalog_product_entity_int.row_id as product_id, catalog_product_entity_int.value as candere_product_type FROM catalog_product_entity_int catalog_product_entity_int WHERE catalog_product_entity_int.row_id in ('.$commaProductIds.') ";
                    $resultsCandereProductTypesArr = $this->_connection->fetchAll($sqlProductTypes);
            
                    foreach ($resultsCandereProductTypesArr as $key_pt => $value_pt) {
                        if (array_key_exists($value_pt['product_id'], $productDetails)) {
                            $productDetails[$value_pt['product_id']]['candere_product_type_id'] = $value_pt[
                                'candere_product_type'
                            ];
                            $productDetails[$value_pt['product_id']]['candere_product_type']=$ProductTypeoptionArr[
                                $value_pt['candere_product_type']
                            ];
                        } else {
                            $productDetails[$value_pt['product_id']]['candere_product_type_id'] = $value_pt[
                                'candere_product_type'
                            ];
                            $productDetails[$value_pt['product_id']]['candere_product_type'] = $ProductTypeoptionArr[
                                $value_pt['candere_product_type']
                            ];
                        }
                    }
                    // phpcs:ignore
                    $categoryProductIdSQL="SELECT entity_id as product_id, GROUP_CONCAT(category_id) as category_ids FROM ( SELECT e.entity_id, at_category_id.category_id FROM catalog_product_entity AS e LEFT JOIN catalog_category_product AS at_category_id ON (at_category_id.product_id=e.entity_id) Where e.entity_id in (".$commaProductIds.")) sub_query GROUP BY entity_id";
                    $resultsCategoryProductsArr =$this->_connection->fetchAll($categoryProductIdSQL);
                    $categoriesArr=$this->getCategoryForDropdown();
                    foreach ($resultsCategoryProductsArr as $key_cp => $value_cp) {
                        if (array_key_exists($value_cp['product_id'], $productDetails)) {
                            $productDetails[$value_cp['product_id']]['category_ids']=$value_cp['category_ids'];
                            $productDetails[$value_cp['product_id']]['categories'] = $this->getCategoriesName(
                                $value_cp['category_ids'],
                                $categoriesArr
                            );
                        } else {
                            $productDetails[$value_cp['product_id']]['category_ids']=$value_cp['category_ids'];
                            $productDetails[$value_cp['product_id']]['categories'] = $this->getCategoriesName(
                                $value_cp['category_ids'],
                                $categoriesArr
                            );
                        }
                    }
                    $data['productDetails']=$productDetails;
                }

                if ($isOrderSourceMedium==1) {
                    // phpcs:ignore
                    $sqlTrafficSource="SELECT count(*) as count,affiliate_source, affiliate_medium, CONCAT(`affiliate_source`, ' / ', `affiliate_medium`) as label FROM `sales_flat_order` where increment_id in (".$commaRealOrderIds.") group by affiliate_source, affiliate_medium order by count desc";
                    $resTrafficSource =$this->_connection->fetchAll($sqlTrafficSource);
                    $data['chartOfTrafficSource']=$resTrafficSource;
                }

                $data['isSales']=$isSales;
                $data['isOrderSourceMedium']=$isOrderSourceMedium;
                $data['listPaymentMethods']=$listPaymentMethods;
                $data['totalSummary']=$resAmountTotal;
        
                foreach ($result as $key => $value) {
                    $orderid = $value['order_id'];
                    // phpcs:ignore
                    $sqlweight="SELECT weight FROM `sales_order_item` WHERE `order_id`= $orderid";
                    $metalweight =$this->_connection->fetchOne($sqlweight);
                    $result[$key]['metal_weight'] = $metalweight ;
                }
            }
            if ((count($result) > 0)) {
                $data['orderResult']= $result;
            } else {
                $data['orderResult']=[];
            }
        } else {
            $data['orderResult']=[];
        }
            $data['request']['selected']=$requestArr;
            $data['request']['allOrderStatusList']=$this->getAllOrderStatusList();
            $data['request']['allOrderStateList']=$this->getAllOrderStateList();
            $data['request']['allPaymentMethodList']=$this->getPaymentMethodList();
            return $data;
    }
}
