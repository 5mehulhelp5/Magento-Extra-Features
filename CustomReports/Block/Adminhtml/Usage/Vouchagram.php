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

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class Vouchagram extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;
    
    /**
     * @var object
     */
    protected $_connection;
    
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storemanager;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storemanager
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storemanager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        RequestInterface $request
    ) {

        parent::__construct($context);
        $this->_session = $session;
        $this->_connection = $resource->getConnection();
        $this->customerModel = $customerModel;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->storemanager = $storemanager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }

    /**
     * Return URL parameters
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->request->getParams();
    }

    /**
     * Get Redeem info
     *
     * @return array|string
     */
    public function getRedeeminfo()
    {
        $data = [];
        $date_from = '';
        $date_to = '';
        $errorMessage = '';
        $conditions = "";
        $data['errorMessage'] = $errorMessage;

        if (!empty($this->request->getParams())) {
            $requestParams = $this->request->getParams();
            if (isset($requestParams['from']) && $requestParams['from'] != ""
                && isset($requestParams['to']) && $requestParams['to'] != "") {
                $date_from = $requestParams['from'];
                $date_to = $requestParams['to'];
                $date_from = strtotime($date_from);
                $date_to = strtotime($date_to);
                $mysql_date_from = date('Y-m-d 00:00:00', $date_from);
                $mysql_date_to = date('Y-m-d 23:59:00', $date_to);

                $mysql_date_from = date("Y-m-d H:i:s", strtotime('-330 minutes', strtotime($mysql_date_from)));
                $mysql_date_to = date("Y-m-d H:i:s", strtotime('-330 minutes', strtotime($mysql_date_to)));

                if ($date_from > $date_to) {
                    return;
                } else {
                    $conditions .= " AND `order_date` BETWEEN '" . $mysql_date_from . "' AND '" . $mysql_date_to . "'";
                }
            }
            if (isset($requestParams['voucher_code']) && $requestParams['voucher_code'] != "") {
                $conditions .= " AND `voucher_number` like '%" . $requestParams['voucher_code'] . "%' ";
            }
            if (isset($requestParams['order_id']) && $requestParams['order_id'] != "") {
                $conditions .= " AND `order_id` = '" . $requestParams['order_id'] . "' ";
            }

            if (isset($requestParams['email_id']) && $requestParams['email_id'] != "") {
                $email_id = trim($requestParams['email_id']);
                if (!filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
                    return 'The email id is not valid';
                } else {
                    $websiteID = $this->storemanager->getStore()->getWebsiteId();
                    $customer_check = $this->customerFactory->create()
                        ->setWebsiteId($websiteID)->loadByEmail($email_id);
                    $customerId = null;
                    if ($customer_check->getId()) {
                        $customerId = $customer_check->getId();
                    }

                    $conditions .= " AND `user_id` = '" . $customerId . "' ";
                }
            }
            // phpcs:ignore
            $totalEmpSQL = " SELECT * FROM `vouchagram_transaction_history` WHERE `order_id` != \"\" " . $conditions . " ORDER BY `order_date` DESC ";
            $resultstate = $this->_connection->fetchAll($totalEmpSQL);
            return $resultstate;
        } else {
            $data = [];
            // phpcs:ignore
            $totalEmpSQL = " SELECT * FROM `vouchagram_transaction_history` WHERE order_id!=\"\" ORDER BY `order_date` DESC LIMIT 10 ";

            $resultstate = $this->_connection->fetchAll($totalEmpSQL);
            return $resultstate;
        }
    }

    /**
     * Get Customer Email
     *
     * @param int $customerId
     * @return string
     */
    public function getCustomerEmail($customerId)
    {
        $customerEmailId = null;
        try {
            $customer_check = $this->customerModel->load($customerId);
            if ($customer_check->getId()) {
                $customer = $this->customerRepository->getById($customerId);
                $customerEmailId = $customer->getEmail();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $customerEmailId;
    }

    /**
     * Get Order details by increment Id
     *
     * @param int $incrementId
     * @return array
     */
    public function getOrderByIncrementId($incrementId)
    {
        $orderDetails = [];
        try {
            $orderCollection = $this->_connection->fetchRow(
                "select `entity_id`, `vouchagram_amount`, `base_grand_total` from `sales_order`
                    where `increment_id` = '".$incrementId."' "
            );
            $orderDetails = $orderCollection;

            $order_id = $orderCollection['entity_id'];
            $orderItemsCollection = $this->_connection->fetchAll(
                " select `product_id` from `sales_order_item` where `order_id` = '".$order_id."' "
            );
            foreach ($orderItemsCollection as $orderItem) {
                $orderDetails['product_id'][] = $orderItem['product_id'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $orderDetails;
    }

    /**
     * Get Product Details
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductDetails($productId)
    {
        $productDetails = null;
        try {
            $productDetails = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $productDetails;
    }
}
