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

class Voucher extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
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
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storemanager
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storemanager,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->_connection = $resource->getConnection();
        $this->customerModel = $customerModel;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->storemanager = $storemanager;
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
     * Get Redeem Info
     *
     * @return void
     */
    public function getRedeeminfo()
    {
        $data=[];
        $date_from = '';
        $date_to = '';
        $errorMessage='';
        $conditions="";
        $data['errorMessage']=$errorMessage;
       
        if (!empty($this->request->getParams())) {
            $requestParams = $this->request->getParams();
            if (!empty($requestParams['from']) && !empty($requestParams['to'])) {
                $date_from = $requestParams['from'];
                $date_to = $requestParams['to'];
                $date_from      = strtotime($date_from);
                $date_to = strtotime($date_to);
                $mysql_date_from = date('Y-m-d', $date_from);
                $mysql_date_to = date('Y-m-d', $date_to);

                if ($date_from > $date_to) {
                    return;
                } else {
                    $conditions .= " AND `refunded_date` BETWEEN '".$mysql_date_from."' AND '".$mysql_date_to."'";
                }
            }
            if (isset($requestParams['voucher_code']) && $requestParams['voucher_code']!="") {
                $conditions .= " AND `coupon_no` = '".$requestParams['voucher_code']."' ";
            }
            if (isset($requestParams['order_id']) && $requestParams['order_id']!="") {
                $conditions .= " AND `order_number` = '".$requestParams['order_id']."' ";
            }

            if (isset($requestParams['email_id']) && $requestParams['email_id']!="") {
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
                    
                    $conditions .= " AND `customer_id` = '".$customerId."' ";
                }
            }
            
            if (isset($requestParams['channel']) && $requestParams['channel']!="") {
                $conditions .= " AND `giftcard_type` = '".$requestParams['channel']."' ";
            }
            // phpcs:ignore
            $totalEmpSQL = "SELECT * FROM `wk_ws_wallet_transaction` WHERE `payment_status` = 'success' ".$conditions." ORDER BY `entity_id` DESC limit 50000";
            $resultstate =$this->_connection->fetchAll($totalEmpSQL);
            return $resultstate;
        } else {
            $data=[];
            $todayDate=date('Y-m-d');

            $date_from = date('Y-m-01', strtotime($todayDate));
            $date_to = date('Y-m-t', strtotime($todayDate));

            // phpcs:ignore
            $totalEmpSQL = " SELECT * FROM `wk_ws_wallet_transaction` WHERE `payment_status` = 'success' ORDER BY `entity_id` DESC LIMIT 10 ";

            $resultstate =$this->_connection->fetchAll($totalEmpSQL);
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
}
