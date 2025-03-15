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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class Affiliated extends \Magento\Framework\View\Element\Template
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
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

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
     * @param Data $dataHelper
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categorycollection
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        Data $dataHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categorycollection,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->scope=$scope;
        $this->_connection = $resource->getConnection();
        $this->_categorycollection = $_categorycollection;
        $this->eavConfig = $eavConfig;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }
     
    /**
     * Get orders
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
     * Get list of order status
     *
     * @return array
     */
    public function getAllOrderStatusList()
    {
        // phpcs:ignore
        $state = "SELECT  distinct state FROM sales_order_status_state where state!='NULL' ";

        $resultstate =$this->_connection->fetchAll($state);
        $resultstate[]['state'] = 'All';
        return $resultstate;
    }
    
    /**
     * Get list of payment methods
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
     * Get list of sources
     *
     * @return array
     */
    public function getSourceList()
    {
        $date = date("Y-m-d", strtotime("-6 months"));
        // phpcs:ignore
        $source = "select distinct affiliate_source from sales_order where affiliate_source !='' and created_at > '$date' order by affiliate_source asc";
        $resultsource =$this->_connection->fetchAll($source);
        
        return $resultsource;
    }

    /**
     * Get list of Medium
     *
     * @return array
     */
    public function getMediumList()
    {
        $date = date("Y-m-d", strtotime("-6 months"));
        // phpcs:ignore
        $medium = "select distinct affiliate_medium from sales_order where affiliate_medium !='' and created_at > '$date' order by affiliate_medium asc";
        $resultmedium =$this->_connection->fetchAll($medium);
       
        return $resultmedium;
    }

    /**
     * Get category for dropdown
     *
     * @return array
     */
    public function getCategoryForDropdown()
    {
        $categoriesArray = $this->_categorycollection->getCollection()
        ->addAttributeToSelect('*')
        ->addIsActiveFilter()
        ->addAttributeToFilter('entity_id', ['in' => ['251','325','257','121']]);

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
     * Get Attribute Options
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
     * Get categories name
     *
     * @param string $categories_ids
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
     * Get list of material type with varient
     *
     * @return array
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
     * Get Affiliated List
     *
     * @return array
     */
    public function getAffiliatedList()
    {
        if (!empty($this->request->getParams())) {
            $requestParams = $this->request->getParams();
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
            $last_source =$requestParams['last_source'];
        
            if ($last_source != '') {
                $condition = " sales_order.affiliate_source = '$last_source' and";
            } else {
                $condition = '' ;
            }
    
              $last_medium = $requestParams['last_medium'];
        
            if ($last_medium != '') {
                $condition1 = " sales_order.affiliate_medium = '$last_medium' and";
            } else {
                $condition1 = '' ;
            }
    
            $condition2 = " sales_order.mktplace_order_id IS NULL and";
            $date_from         = strtotime($requestParams['from'])-86400;
            $date_to         = strtotime($requestParams['to']);
            $last_source  = $requestParams['last_source'];
            $last_medium  = $requestParams['last_medium'];
            $mysql_date_from = date('Y-m-d', $date_from).' 18:30:00';
            $mysql_date_to = date('Y-m-d', $date_to).' 18:30:00';
            
            // phpcs:ignore
            $sql = "SELECT sales_order.increment_id AS order_id,
                sales_order.base_grand_total,
                sales_order.base_total_paid,
                sales_order.affiliate_source,
                sales_order.affiliate_medium,
                sales_order.order_currency_code,
                sales_order.base_currency_code,
                sales_order.customer_id, 
                sales_order.state, 
                sales_order.status, 
                sales_order.entity_id as magento_order_id,
                GROUP_CONCAT(sales_order_item.name SEPARATOR',') AS name,					   
                GROUP_CONCAT(sales_order_item.product_id SEPARATOR',') AS product_id,
                DATE_ADD(sales_order.created_at, INTERVAL '05:30' HOUR_MINUTE) AS created_at,
                sales_order_status.label as order_status FROM (sales_order sales_order
                INNER JOIN sales_order_status sales_order_status ON (sales_order.status = sales_order_status.status))
                INNER JOIN sales_order_item sales_order_item ON (sales_order.entity_id = sales_order_item.order_id)
                where  $condition $condition1 $condition2 sales_order.state in ('processing')
                And (sales_order.created_at between '$mysql_date_from' AND '$mysql_date_to')
                AND sales_order_item.sku != 'RP0001'
                GROUP BY sales_order.increment_id
                ORDER BY sales_order.entity_id DESC";
            
            $result = $this->_connection->fetchAll($sql);
        
            return $result;
            
        } else {
            $result=[];
            return $result;
        }
    }

    /**
     * Get Product Type
     *
     * @param int $ids
     * @return string
     */
    public function getType($ids)
    {
        $productId = $ids;
        $product = $this->productRepository->getById($productId);
        $productType = $product->getTypeID();
        return $productType;
    }

    /**
     * Get Shipment Dates
     *
     * @param int $parent_id
     * @return string
     */
    public function getShipmentDate($parent_id)
    {
        // phpcs:ignore
        $shipment_date = "SELECT created_at  from sales_order_status_history where parent_id='$parent_id' and status='processing'";
        $results = $this->_connection->fetchOne($shipment_date);
                    
        return $results;
    }

    /**
     * Get Product Type
     *
     * @param int $parent_id
     * @return string
     */
    public function getProductType($parent_id)
    {
        $product_id = explode(',', $parent_id);
  
        foreach ($product_id as $ids) {
            // phpcs:ignore
            $sql = 'SELECT catalog_product_entity_int.value as type FROM catalog_product_entity_int catalog_product_entity_int WHERE (catalog_product_entity_int.row_id = '.$ids.')';
            $type = $this->_connection->fetchOne($sql);
         
            // phpcs:ignore
            $product_type = 'SELECT eav_attribute_option_value.value as product_type
                FROM catalog_product_entity_int catalog_product_entity_int
                INNER JOIN eav_attribute_option_value eav_attribute_option_value
                ON (catalog_product_entity_int.value = eav_attribute_option_value.option_id)
                WHERE (catalog_product_entity_int.row_id = '.$ids.')';
         
            $results = $this->_connection->fetchOne($product_type);
            return ($results .' ('. $type.') ');
        }
    }
}
