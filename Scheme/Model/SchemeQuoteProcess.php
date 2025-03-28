<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Model;

use Magento\Framework\App\ResourceConnection;

class SchemeQuoteProcess
{
    public const SCHEME_QUOTE_TABLE = 'kj_scheme_quote';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customersession;

    /**
     * @var \KalyanUs\Scheme\Helper\Data
     */
    protected $helperDataScheme;

    /**
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customersession
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customersession,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->customersession =$customersession;
        $this->helperDataScheme =$helperDataScheme;
    }

    /**
     * Insert Sql Query
     *
     * @param array $data
     * @return int
     */
    private function insert($data)
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::SCHEME_QUOTE_TABLE);
        $connection->insert(self::SCHEME_QUOTE_TABLE, $data);
        return $connection->lastInsertId();
    }

    /**
     * Update
     *
     * @param int $id
     * @param array $data
     */
    private function update($id, $data)
    {
        if ($id!='' && count($data) > 0) {
            $connection  = $this->resourceConnection->getConnection();
            $where = ['id = ?' => (int)$id];
            $tableName = self::SCHEME_QUOTE_TABLE;
            $connection->update($tableName, $data, $where);
        }
    }

    /**
     * Get quote
     *
     * @return object|bool
     */
    public function getQuote()
    {
        if ($this->customersession->isLoggedIn()) {
            $customerId=$this->customersession->getCustomer()->getId();
            if ($this->isCustomerExistInQuote($customerId)) {
                return $this->getCustomerQuote($customerId);
            }
        }
        return false;
    }

    /**
     * Remove Quote by customer id
     *
     * @param int $customerId
     * @return bool
     */
    public function removeQuoteByCustomerId($customerId)
    {
        if ($this->customersession->isLoggedIn()) {
            try {
                if ($customerId!='') {
                    $customerId=$customerId;
                } else {
                    $customerId=$this->customersession->getCustomer()->getId();
                }
                if ($this->isCustomerExistInQuote($customerId)) {
                    $connection  = $this->resourceConnection->getConnection();
                    // phpcs:ignore
                    $delete_sql = "Delete FROM kj_scheme_quote Where customer_id = '".$customerId."'";
                    $connection->query($delete_sql);
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Get Customer Quote
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerQuote($customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        if (isset($customerId) && $customerId!='') {
            $select = $connection->select()->from(
                ['sq' => self::SCHEME_QUOTE_TABLE],
                ['*']
            )
            ->where(
                "sq.customer_id = ?",
                $customerId
            );
            $records = $connection->fetchRow($select);
            if (is_array($records)) {
                if (count($records)>0) {
                    return $records;
                }
            }
        }
        return [];
    }

    /**
     * Check customer exist is quote
     *
     * @param int $customerId
     * @return boolean
     */
    public function isCustomerExistInQuote($customerId)
    {
        if (isset($customerId) && $customerId!='') {
            $records = $this->getCustomerQuote($customerId);
            if (count($records)>0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save quote
     *
     * @param array $data
     */
    public function saveQuote($data)
    {
        $quote=[];
        if ($this->customersession->isLoggedIn()) {
            $customerId=$this->customersession->getCustomer()->getId();
            $customer=$this->customersession->getCustomer();
            if ($this->isCustomerExistInQuote($customerId)) {
                $quoteData=$this->getCustomerQuote($customerId);
                if ($quoteData['id']!='') {
                    $data['email_id']=$customer->getEmail();
                    $quote=$this->prepareUpdateQuote($data);
                    $this->update($quoteData['id'], $quote);
                }
            } else {
                $quote['customer_id']=$customerId;
                $quote['emi_amount']=isset($data['emi_amount']) ? $data['emi_amount'] : null;
                $quote['duration']=isset($data['duration']) ? $data['duration'] : null;
                $quote['scheme_name']=isset($data['scheme_name']) ? $data['scheme_name'] : null;
                $quote['email_id']=$customer->getEmail();
                $quote['customer_name']=isset($data['customer_name']) ? $data['customer_name'] : null;
                $quote['scheme_mobile_number']=isset($data['scheme_mobile_number'])
                    ? $this->helperDataScheme->getMobileNumberWithCountrycode($data['scheme_mobile_number'])
                    : null;
                $quote['is_mobile_verified']=isset($data['is_mobile_verified']) ? $data['is_mobile_verified'] : null;
                $quote['auto_monthly_payment']=isset($data['auto_monthly_payment']) ? $data['auto_monthly_payment'] : 0;
                $quote['address']=isset($data['address']) ? $data['address']  : null;
                $quote['pincode']=isset($data['pincode']) ? $data['pincode']  : null;
                $quote['state']=isset($data['state']) ? $data['state'] : null;
                $quote['city']=isset($data['city']) ? $data['city']  : null;
                $nominee=[];
                if (isset($data['nominee'])) {
                    $nominee['name']=isset($data['nominee']['nominee_name'])
                        ? $data['nominee']['nominee_name'] : null;
                    $nominee['relationship']=isset($data['nominee']['nominee_relationship'])
                        ? $data['nominee']['nominee_relationship'] : null;
                    $nominee['mobilenumber']=isset($data['nominee']['nominee_mobilenumber'])
                        ? $data['nominee']['nominee_mobilenumber'] : null;
                    $nominee['nationality']=isset($data['nominee']['nominee_nationality'])
                        ? $data['nominee']['nominee_nationality'] : null;
                }
                $quote['nominee_info']=json_encode($nominee);
                $this->insert($quote);
            }
        }
    }

    /**
     * Prepare update quote
     *
     * @param array $data
     * @return array
     */
    public function prepareUpdateQuote($data)
    {
        $quote=[];
        if (isset($data['emi_amount']) && !empty($data['emi_amount'])) {
            $quote['emi_amount']=$data['emi_amount'];
        }
        if (isset($data['duration']) && !empty($data['duration'])) {
            $quote['duration']=$data['duration'];
        }
        if (isset($data['scheme_name']) && !empty($data['scheme_name'])) {
            $quote['scheme_name']=$data['scheme_name'];
        }
        if (isset($data['email_id']) && !empty($data['email_id'])) {
            $quote['email_id']=$data['email_id'];
        }
        if (isset($data['customer_name']) && !empty($data['customer_name'])) {
            $quote['customer_name']=$data['customer_name'];
        }
        if (isset($data['scheme_mobile_number']) && !empty($data['scheme_mobile_number'])) {
            $quote['scheme_mobile_number']=$this->helperDataScheme->getMobileNumberWithCountrycode(
                $data['scheme_mobile_number']
            );
        }
        if (isset($data['is_mobile_verified']) && !empty($data['is_mobile_verified'])) {
            $quote['is_mobile_verified']=$data['is_mobile_verified'];
        }
        if (isset($data['address']) && !empty($data['address'])) {
            $quote['address']=$data['address'];
        }
        if (isset($data['pincode']) && !empty($data['pincode'])) {
            $quote['pincode']=$data['pincode'];
        }
        if (isset($data['state']) && !empty($data['state'])) {
            $quote['state']=$data['state'];
        }
        if (isset($data['city']) && !empty($data['city'])) {
            $quote['city']=$data['city'];
        }
        if (isset($data['city']) && !empty($data['city'])) {
            $quote['city']=$data['city'];
        }
        if (isset($data['city']) && !empty($data['city'])) {
            $quote['city']=$data['city'];
        }
        if (isset($data['auto_monthly_payment']) && !empty($data['auto_monthly_payment'])) {
            $quote['auto_monthly_payment']=$data['auto_monthly_payment'];
        } else {
            $quote['auto_monthly_payment']=0;
        }
        $nominee=[];
        if (isset($data['nominee'])) {
            $nominee['name']=isset($data['nominee']['nominee_name'])
                ? $data['nominee']['nominee_name'] : null;
            $nominee['relationship']=isset($data['nominee']['nominee_relationship'])
                ? $data['nominee']['nominee_relationship'] : null;
            $nominee['mobilenumber']=isset($data['nominee']['nominee_mobilenumber'])
                ? $data['nominee']['nominee_mobilenumber'] : null;
            $nominee['nationality']=isset($data['nominee']['nominee_nationality'])
                ? $data['nominee']['nominee_nationality'] : null;
        }
        $quote['nominee_info']=json_encode($nominee);
        return $quote;
    }

    /**
     * Get full quote
     *
     * @return array
     */
    public function getFullQuote()
    {
        $quote=$this->getQuote();
        if ($quote) {
            if (count($quote) > 0) {
                if ($quote['scheme_mobile_number']!='') {
                    $quote['scheme_mobile_number']=$this->helperDataScheme->getMobileNumberWithOUTCountrycode(
                        $quote['scheme_mobile_number']
                    );
                }
                if ($quote['nominee_info']) {
                    $nominee_info=json_decode($quote['nominee_info']);
                    $quote['nominee_info']=$nominee_info;
                }
                return $quote;
            }
        }
        return [];
    }
}
