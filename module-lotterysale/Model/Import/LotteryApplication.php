<?php
namespace Casio\LotterySale\Model\Import;

use Casio\CasioIdAuth\Model\CasioId\Client;
use Casio\LotterySale\Model\LotteryManagement;
use Casio\LotterySale\Helper\Data as HelperData;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Casio\LotterySale\Model\Config\Source\Website as SourceWebsite;
use Magento\Framework\Model\ResourceModel\Iterator as IteratorResourceModel;

/**
 * Class LotteryApplication
 * Casio\LotterySale\Model\Import
 */
class LotteryApplication extends AbstractEntity
{
    const ENTITY_TYPE_CODE = 'casio_lotterysale_application_import';
    const COL_WEBSITE_ID = 'website_id';
    const COL_LOTTERY_ID = 'lottery_id';
    const COL_SKU = 'sku';
    const COL_APPLICATION_FROM = 'application_from';
    const COL_APPLICATION_TO = 'application_to';
    const COL_LOTTERY_DATE = 'lottery_date';
    const COL_PURCHASE_DEADLINE = 'purchase_deadline';
    const COL_LOTTERY_NUMBER = 'lottery_number';
    const COL_USER_ID = 'user_id';
    const COL_FIRSTNAME_KANA = 'firstnamekana';
    const COL_LASTNAME_KANA = 'lastnamekana';
    const COL_FIRSTNAME = 'firstname';
    const COL_LASTNAME = 'lastname';
    const COL_EMAIL = 'email';
    const COL_TELEPHONE = 'telephone';
    const COL_POSTCODE = 'postcode';
    const COL_PREF = 'region_id';
    const COL_STATE = 'region';
    const COL_CITY = 'city';
    const COL_STREET = 'street';
    const COL_BUILDING = 'building';
    const COL_CREATED_AT = 'created_at';
    const COL_STATUS = 'status';
    const LOTTERY_SALE_ID = 'lottery_sales_id';
    const COL_CASIO_LOTTERY_APPLICATED_COUNT = 'casio_lottery_applicated_count';
    const COL_CASIO_LOTTERY_WIN_COUNT = 'casio_lottery_win_count';
    const ERROR_CODE_NUMBER_OF_WINNER_NOT_FOUND = 'Number of winner does not exit';
    const ERROR_CODE_NUMBER_OF_WINNER_GREATER = 'Total winner is greater than Number of Winner';
    const ERROR_CODE_NUMBER_OF_WINNER_LESS = 'The number of winners has not reached the upper limit';
    const ERROR_CODE_USER_ID_EMPTY = 'The user_id column is empty';
    const ERROR_CODE_CUSTOMER_REJECT = 'Contains non-existent users';
    const ERROR_CODE_DATA_NOT_EXIST = 'The data does not exist in the database';
    const ERROR_CODE_WEBSITE_NOT_EXIST = 'Contains non-existent website';
    const ERROR_CODE_LOTTERY_NUMBER_NOT_EXIST = 'Contains non-existent lottery number';
    const ERROR_CODE_COLUMN_REQUIRED_EMPTY = 'Missing required data in column: ';

    protected $validColumnNames = [
        self::COL_WEBSITE_ID,
        self::COL_LOTTERY_ID,
        self::COL_SKU,
        self::COL_APPLICATION_FROM,
        self::COL_APPLICATION_TO,
        self::COL_LOTTERY_DATE,
        self::COL_PURCHASE_DEADLINE,
        self::COL_LOTTERY_NUMBER,
        self::COL_USER_ID,
        self::COL_FIRSTNAME_KANA,
        self::COL_LASTNAME_KANA,
        self::COL_FIRSTNAME,
        self::COL_LASTNAME,
        self::COL_EMAIL,
        self::COL_TELEPHONE,
        self::COL_POSTCODE,
        self::COL_PREF,
        self::COL_STATE,
        self::COL_CITY,
        self::COL_STREET,
        self::COL_BUILDING,
        self::COL_CREATED_AT,
        self::COL_STATUS,
        self::COL_CASIO_LOTTERY_APPLICATED_COUNT,
        self::COL_CASIO_LOTTERY_WIN_COUNT
    ];

    protected $validationColumnNotEmpty = [
        self::COL_WEBSITE_ID,
        self::COL_LOTTERY_ID,
        self::COL_SKU,
        self::COL_PURCHASE_DEADLINE,
        self::COL_LOTTERY_NUMBER,
        self::COL_USER_ID
    ];

    /**
     * @var array
     */
    protected $errorMessageTemplates = [
        self::ERROR_CODE_SYSTEM_EXCEPTION => 'General system exception happened',
        self::ERROR_CODE_COLUMN_NOT_FOUND => 'We can\'t find required columns: %s.',
        self::ERROR_CODE_COLUMN_EMPTY_HEADER => 'Columns number: "%s" have empty headers',
        self::ERROR_CODE_COLUMN_NAME_INVALID => 'Column names: "%s" are invalid',
        self::ERROR_CODE_ATTRIBUTE_NOT_VALID => "Please correct the value for '%s'.",
        self::ERROR_CODE_DUPLICATE_UNIQUE_ATTRIBUTE => "Duplicate Unique Attribute for '%s'",
        self::ERROR_CODE_ILLEGAL_CHARACTERS => "Illegal character used for attribute %s",
        self::ERROR_CODE_INVALID_ATTRIBUTE => 'Header contains invalid attribute(s): "%s"',
        self::ERROR_CODE_WRONG_QUOTES => "Curly quotes used instead of straight quotes",
        self::ERROR_CODE_COLUMNS_NUMBER => "Number of columns does not correspond to the number of rows in the header",
    ];

    /** @var RequestInterface  */
    protected $_request;

    /** @var SkuProcessor  */
    protected $skuProcessor;

    /** @var PsrLoggerInterface  */
    protected $_logger;

    /** @var ResourceConnection  */
    protected $_resource;

    /** @var string[]  */
    protected $_productTypeModels = [
        \Magento\CatalogImportExport\Model\Import\Product\Type\Simple::class
    ];

    protected $_oldSku = null;

    protected $_lotteryApplicationTbl = null;

    protected $_mailConfirmList = [];

    protected $_customerCasioList = [];

    /** @var LotteryManagement  */
    protected $_lotteryManagement;

    /**
     * @var Client
     */
    private Client $client;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $customerCollectionFactory;

    protected $sourceWebsite;

    protected $websiteOption = [];

    /** @var IteratorResourceModel  */
    protected $resourceIterator;

    /** @var array  */
    protected $lotteryApplication = [];

    protected $applicationWinData = [];

    /**
     * LotteryApplication constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param RequestInterface $request
     * @param SkuProcessor $skuProcessor
     * @param PsrLoggerInterface $logger
     * @param LotteryManagement $lotteryManagement
     * @param CollectionFactory $customerCollectionFactory
     * @param Client $client
     * @param SourceWebsite $sourceWebsite
     * @param IteratorResourceModel $resourceIterator
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        RequestInterface $request,
        SkuProcessor $skuProcessor,
        PsrLoggerInterface $logger,
        LotteryManagement $lotteryManagement,
        CollectionFactory $customerCollectionFactory,
        Client $client,
        SourceWebsite $sourceWebsite,
        IteratorResourceModel $resourceIterator
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->string = $string;
        $this->errorAggregator = $errorAggregator;
        $this->_request = $request;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection();
        $this->skuProcessor = $skuProcessor;
        $this->_logger = $logger;
        $this->_lotteryManagement = $lotteryManagement;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->client = $client;
        $this->sourceWebsite = $sourceWebsite;
        $this->resourceIterator = $resourceIterator;
        $this->_initSkus();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function _importData()
    {
        $this->_validatedRows = null;
        if (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->updateDate();
        }
        return true;
    }

    private function updateDate()
    {
        try {
            $lotteryApplication = $this->getLotteryApplication();
            while ($bunch = $this->_dataSourceModel->getNextBunch()) {
                $mailConfirmList = [];
                $applicationIds = [];
                $lotteryNumberList = [];
                $skuList = [];
                foreach ($bunch as $rowNum => $row) {
                    if (!$this->validateRow($row, $rowNum)) {
                        continue;
                    }
                    if ($this->getErrorAggregator()->hasToBeTerminated()) {
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                    $sku = $row[self::COL_SKU];
                    $websiteId = $this->getWebsiteId($row[self::COL_WEBSITE_ID]);
                    $skuList[] = $sku;
                    $userId = $this->fetchCustomerFromCasioId($row[self::COL_USER_ID]);
                    $lotteryNumber = $row[self::COL_LOTTERY_NUMBER] ?? null;
                    if($userId != null && $websiteId != null && $lotteryNumber != null) {
                        $applicationIds[] = $row[self::COL_LOTTERY_ID] ?? null;
                        $lotteryData = $lotteryApplication[$websiteId][$userId][$lotteryNumber];
                        if (isset($lotteryData[self::COL_STATUS]) && $lotteryData[self::COL_STATUS] == \Casio\LotterySale\Helper\Data::STATUS_APPLYING) {
                            $lotteryNumberList[] = $lotteryNumber;
                            $mailConfirmList[$userId][$lotteryNumber] = $lotteryData;
                        }
                    }
                }
                $this->updateLotteryApplication($lotteryNumberList);
                $applicationWinIds = $this->applicationWinData;
                if (!empty($applicationWinIds)) {
                    $this->updateCustomerNoWinning($applicationWinIds);
                }
                $this->sendEmailConfirm($mailConfirmList);
            }
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param $lotteryApplication
     * @param $websiteId
     * @param $userId
     * @param $lotteryNumber
     * @return false|string
     */
    protected function checkDataExist($lotteryApplication, $websiteId, $userId, $lotteryNumber)
    {
        if (!isset($lotteryApplication[$websiteId])) {
            return self::ERROR_CODE_WEBSITE_NOT_EXIST;
        }
        if (!isset($lotteryApplication[$websiteId][$userId])) {
            return self::ERROR_CODE_CUSTOMER_REJECT;
        }
        if (!isset($lotteryApplication[$websiteId][$userId][$lotteryNumber])) {
            return self::ERROR_CODE_LOTTERY_NUMBER_NOT_EXIST;
        }
        return false;
    }

    /**
     * @param array $lotteryNumberList
     * @throws \Exception
     */
    private function updateLotteryApplication(array $lotteryNumberList)
    {
        try {
            if (!empty($lotteryNumberList)) {
                $this->applicationWinData = [];
                $connection = $this->_resource->getConnection();
                $connection->beginTransaction();
                if ($this->_lotteryApplicationTbl == null) {
                    $this->_lotteryApplicationTbl = $this->_resource->getTableName('casio_lottery_application');
                }
                $whereCondition = $this->_getConditionSql(
                    $connection,
                    $connection->quoteIdentifier('lottery_sales_code'),
                    ['in' => $lotteryNumberList]
                );
                $connection->update($this->_lotteryApplicationTbl, ['status' => HelperData::STATUS_WINNING], $whereCondition);
                $connection->commit();
                $sql = $connection->select()->from(
                    $this->_lotteryApplicationTbl,
                    ['id', 'lottery_sales_id']
                )->where(
                    $whereCondition
                );
                $this->resourceIterator->walk(
                    $sql,
                    [[$this, 'callBackGetApplicationWinData']],
                    []
                );
            }
        } catch (\Exception $exception) {
            $connection->rollBack();
            $this->_logger->critical($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param $rowData
     */
    public function callBackGetApplicationWinData($rowData)
    {
        if (isset($rowData['row'])) {
            $row = $rowData['row'];
            $applicationId = $row['id'] ?? 0;
            $lotterySalesId = $row['lottery_sales_id'] ?? 0;
            $this->applicationWinData[$lotterySalesId][] = $applicationId;
        }
    }

    /**
     * @param array $skuList
     * @param $applicationWinIds
     * @throws \Exception
     */
    private function updateCustomerNoWinning($applicationWinIds)
    {
        try {
            foreach ($applicationWinIds as $lotterySalesId => $applicationId) {
                $connection = $this->_resource->getConnection();
                $connection->beginTransaction();
                if ($this->_lotteryApplicationTbl == null) {
                    $this->_lotteryApplicationTbl = $this->_resource->getTableName('casio_lottery_application');
                }
                $whereCondition = $this->_getConditionSql(
                    $connection,
                    $connection->quoteIdentifier('status'),
                    ['nin' => [HelperData::STATUS_WINNING]]
                );

                $whereCondition .= " AND ". $this->_getConditionSql(
                    $connection,
                    $connection->quoteIdentifier('id'),
                    ['nin' => $applicationId]
                );

                $whereCondition .= " AND ". $this->_getConditionSql(
                    $connection,
                    $connection->quoteIdentifier('lottery_sales_id'),
                    ['in' => [$lotterySalesId]]
                );
                $connection->update($this->_lotteryApplicationTbl, ['status' => HelperData::STATUS_LOST], $whereCondition);
                $connection->commit();
            }
        } catch (\Exception $exception) {
            $connection->rollBack();
            $this->_logger->critical($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @param $connection
     * @param $fieldName
     * @param $condition
     * @return mixed
     */
    protected function _getConditionSql($connection, $fieldName, $condition)
    {
        return $connection->prepareSqlCondition($fieldName, $condition);
    }

    /**
     * @param $mailConfirmList
     * @throws \Exception
     */
    private function sendEmailConfirm($mailConfirmList)
    {
        if (is_array($mailConfirmList) && !empty($mailConfirmList) && !empty($this->_mailConfirmList)) {
            foreach ($mailConfirmList as $userId => $items) {
                if (isset($this->_mailConfirmList[$userId])) {
                    foreach ($items as $lotteryNumber => $data) {
                        $mailConfirmData = $this->_mailConfirmList[$userId];
                        $data['email'] = $mailConfirmData['email_casio'];
                        $data['customer'] = $mailConfirmData['customer'];
                        $this->updateCustomerAttribute($data['customer']);
                        $this->_lotteryManagement->sendEmailWinLotterySales($data);
                    }
                }
            }
        }
    }
    /**
     * @return string[]
     */
    private function getUpdateColumns()
    {
        return [
            self::LOTTERY_SALE_ID,
            self::COL_USER_ID,
            self::COL_STATUS
        ];
    }

    /**
     * @param $sku
     * @return false
     */
    protected function validationSKU($sku)
    {
        if (isset($this->_oldSku[strtolower($sku)])) {
            return $sku;
        }
        return false;
    }

    /**
     * Initialize existent product SKUs.
     *
     * @return $this
     */
    protected function _initSkus()
    {
        if (!$this->_oldSku) {
            $this->skuProcessor->setTypeModels($this->_productTypeModels);
            $this->_oldSku = $this->skuProcessor->reloadOldSkus()->getOldSkus();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return self::ENTITY_TYPE_CODE;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        if ($this->validationEmptyData($rowData, $rowNum)) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $errors = [];
        if ($this->validationSKU($rowData[self::COL_SKU])) {
            $lotteryApplication = $this->getLotteryApplication();
            if (!empty($lotteryApplication)) {
                $userId = $rowData[self::COL_USER_ID] ?? null;
                $websiteId = $rowData[self::COL_WEBSITE_ID] ?? null;
                $lotteryNumber = $rowData[self::COL_LOTTERY_NUMBER] ?? null;
                if ($userId && $websiteId && $lotteryNumber) {
                    $websiteId = $this->getWebsiteId($websiteId);
                    $userId = $this->fetchCustomerFromCasioId((int)$userId);
                    if ($userId) {
                        $isError = $this->checkDataExist($lotteryApplication, $websiteId, $userId, $lotteryNumber);
                        if ($isError) {
                            $errors[] = $isError;
                        }
                    } else {
                        $errors[] = self::ERROR_CODE_CUSTOMER_REJECT;
                    }
                } else {
                    if ($userId == null) {
                        $errors[] = self::ERROR_CODE_USER_ID_EMPTY;
                    }
                    if ($websiteId == null) {
                        $errors[] = self::ERROR_CODE_COLUMN_NAME_INVALID;
                    }
                }
            }
            if (count($errors)) {
                foreach ($errors as $error) {
                    $message = (string)new \Magento\Framework\Phrase($error);
                    $this->addRowError($message, $rowNum, null, $message);
                }
            }
            $this->_validatedRows[$rowNum] = true;
        } else {
            $message = (string)new \Magento\Framework\Phrase("SKU of product does not exit.");
            $this->addRowError($message, $rowNum, null, $message);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param array $rowData
     * @param $rowNum
     * @return bool
     */
    protected function validationEmptyData(array $rowData, $rowNum)
    {
        $error = false;
        foreach ($this->validationColumnNotEmpty as $column) {
            if (!isset($rowData[$column]) || empty($rowData[$column])) {
                $message = (string)new \Magento\Framework\Phrase(self::ERROR_CODE_COLUMN_REQUIRED_EMPTY ." ".$column);
                $this->addRowError($message, $rowNum, $column, $message);
                $error = true;
            }
        }
        return $error;
    }

    /**
     * Validate data.
     *
     * @return ProcessingErrorAggregatorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateData()
    {
        if (!$this->_dataValidated) {
            $this->getErrorAggregator()->clear();
            // do all permanent columns exist?
            $absentColumns = array_diff($this->_permanentAttributes, $this->getSource()->getColNames());
            $this->addErrors(self::ERROR_CODE_COLUMN_NOT_FOUND, $absentColumns);

            if (ImportExport::BEHAVIOR_DELETE != $this->getBehavior()) {
                // check attribute columns names validity
                $columnNumber = 0;
                $emptyHeaderColumns = [];
                $invalidColumns = [];
                $invalidAttributes = [];
                foreach ($this->getSource()->getColNames() as $columnName) {
                    $columnNumber++;
                    if (!$this->isAttributeParticular($columnName)) {
                        if (trim($columnName) == '') {
                            $emptyHeaderColumns[] = $columnNumber;
                        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                            $invalidColumns[] = $columnName;
                        } elseif ($this->needColumnCheck && !in_array($columnName, $this->getValidColumnNames())) {
                            $invalidAttributes[] = $columnName;
                        }
                    }
                }
                $this->addErrors(self::ERROR_CODE_INVALID_ATTRIBUTE, $invalidAttributes);
                $this->addErrors(self::ERROR_CODE_COLUMN_EMPTY_HEADER, $emptyHeaderColumns);
                $this->addErrors(self::ERROR_CODE_COLUMN_NAME_INVALID, $invalidColumns);
            }

            if (!$this->getErrorAggregator()->getErrorsCount()) {
                $this->_saveValidatedBunches();
                $this->validationNumberOfWinner();
                $this->_dataValidated = true;
            }
        }
        return $this->getErrorAggregator();
    }

    public function validationNumberOfWinner()
    {
        $numberOfWinner = (int)$this->_request->getParam('lottery_application_number_winner');
        $message = null;
        if ($numberOfWinner) {
            $totalRow = $this->_processedRowsCount;
            if ($numberOfWinner > $totalRow) {
                $message = self::ERROR_CODE_NUMBER_OF_WINNER_LESS;
            } elseif ($numberOfWinner < $totalRow) {
                $message = self::ERROR_CODE_NUMBER_OF_WINNER_GREATER;
            }
        } else {
            $message = self::ERROR_CODE_NUMBER_OF_WINNER_NOT_FOUND;
        }
        if ($message != null) {
            $message = (string)new \Magento\Framework\Phrase($message);
            $this->addRowError($message, null, null, $message);
        }
    }

    /**
     * @return string[]
     */
    private function getAvailableColumns()
    {
        return $this->validColumnNames;
    }

    /**
     * @param $userId
     * @param int|null $rowNumber
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    private function fetchCustomerFromCasioId($userId, $rowNumber = null)
    {
        try {
            $customer = $this->customerCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', $userId)
                ->setPageSize(1)
                ->getFirstItem();
            if ($customer && $customer->getId() && !empty($customer->getCasioSub()) && $customer->getCasioIsCanceledMembership() == 0) {
                $this->_mailConfirmList[$userId] = [
                    'customer' => $customer,
                    'email_casio' => $customer->getEmail()
                ];
                return $userId;
            }
        } catch (\Exception $exception) {
            $message = (string)new \Magento\Framework\Phrase(self::ERROR_CODE_CUSTOMER_REJECT);
            $this->addRowError($message, $rowNumber, 'user_id', $message);
            $this->_logger->critical($exception->getMessage());
            throw $exception;
        }
        return null;
    }

    /**
     * @param $name
     * @return false|int|string
     */
    private function getWebsiteId($name)
    {
        $websiteOptions = $this->getWebsiteOption();
        return array_search($name, $websiteOptions);
    }

    /**
     * @param $customerModel
     * @throws \Exception
     */
    private function updateCustomerAttribute($customerModel)
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        if ($customerModel) {
            $winCount = $customerModel->getData(HelperData::CUSTOMER_LOTTERY_WIN_COUNT) ?? 0;
            $winCount++;
            $customerModel->setData('casio_lottery_win_count', $winCount);
            $customerModel->save();
        }
    }

    /**
     * @return array
     */
    protected function getWebsiteOption()
    {
        if (empty($this->websiteOption)) {
            $this->websiteOption = $this->sourceWebsite->getAllWebsiteCode();
        }
        return $this->websiteOption;
    }

    /**
     * @return array
     */
    protected function getLotteryApplication()
    {
        if (empty($this->lotteryApplication)) {
            $lotteryApplicationTbl = $this->_resource->getTableName('casio_lottery_application');
            $lotterySalesTbl = $this->_resource->getTableName('casio_lottery_sales');
            $select = $this->_connection->select()
                ->from(['main_table' => $lotteryApplicationTbl])
                ->joinLeft(
                    ['cls' => $lotterySalesTbl],
                    'main_table.lottery_sales_id = cls.id',
                    [
                    'product_id' => 'cls.product_id',
                    'sku' => 'cls.sku',
                    'purchase_deadline' => 'cls.purchase_deadline',
                    'website_id' => 'cls.website_id'
                    ]
                )
                ->group(
                    'main_table.id'
                );
            $this->resourceIterator->walk(
                $select,
                [[$this, 'callBackLotteryApplication']],
                []
            );
        }
        return $this->lotteryApplication;
    }

    /**
     * @param $rowData
     */
    public function callBackLotteryApplication($rowData)
    {
        if (isset($rowData['row'])) {
            $row = $rowData['row'];
            $websiteId = $row['website_id'] ?? 0;
            $userId = $row['user_id'] ?? 0;
            $lotteryNumber = $row['lottery_sales_code'] ?? 0;
            $this->lotteryApplication[$websiteId][$userId][$lotteryNumber] = $row;
        }
    }
}
