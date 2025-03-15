<?php
namespace Casio\LotterySale\Model\Export;

use Casio\EncryptionCustomerData\Model\Data\CustomerInterface;
use Casio\EncryptionCustomerData\Service\EncryptorInterface;
use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Helper\Data as DataHelper;
use Casio\LotterySale\Model\Export\LotteryApplication\CollectionFactory as LotteryApplicationCollectionFactory;
use Casio\Core\Model\Export\Adapter;
use Casio\Core\Model\Export\AdapterFactory;
use Casio\LotterySale\Model\Export\LotteryApplication\AttributeCollectionProvider;
use Casio\LotterySale\Model\Export\LotteryApplication\CustomerAttributeCollection;
use Casio\LotterySale\Model\Config\Source\Website as SourceInterface;
use Casio\LotterySale\Model\Config\Source\Status as LotterySalesStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export;

/**
 * Class LotteryApplication
 * Casio\LotterySale\Model\Export
 */
class LotteryApplication extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    const ENTITY_TYPE_CODE = 'lottery_application';
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
    const COL_CASIO_LOTTERY_APPLICATED_COUNT = 'casio_lottery_applicated_count';
    const COL_CASIO_LOTTERY_WIN_COUNT = 'casio_lottery_win_count';

    /**
     * @var string[]
     */
    protected $templateExportData = [
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

    /** @var AdapterFactory  */
    protected $writerFactory;

    /** @var LotteryApplicationCollectionFactory  */
    protected $lotteryAppColFactory;

    /** @var AttributeCollectionProvider  */
    protected $attributeCollectionProvider;

    /** @var CustomerAttributeCollection  */
    protected $customerAttributeCollection;

    /** @var SourceInterface  */
    protected $eavSourceInterface;

    /** @var LotterySalesStatus  */
    protected $lotterySalesStatus;

    /** @var DataHelper  */
    protected $helperData;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * LotteryApplication constructor.
     * @param LotteryApplicationCollectionFactory $lotteryAppColFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CustomerAttributeCollection $customerAttributeCollection
     * @param SourceInterface $eavSourceInterface
     * @param LotterySalesStatus $lotterySalesStatus
     * @param DataHelper $helperData
     * @param EncryptorInterface $encryptor
     * @param AdapterFactory $adapterFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     */
    public function __construct(
        LotteryApplicationCollectionFactory $lotteryAppColFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        CustomerAttributeCollection $customerAttributeCollection,
        SourceInterface $eavSourceInterface,
        LotterySalesStatus $lotterySalesStatus,
        DataHelper $helperData,
        EncryptorInterface $encryptor,
        AdapterFactory $adapterFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->writerFactory = $adapterFactory;
        $this->lotteryAppColFactory = $lotteryAppColFactory;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->customerAttributeCollection = $customerAttributeCollection;
        $this->eavSourceInterface = $eavSourceInterface;
        $this->lotterySalesStatus = $lotterySalesStatus;
        $this->helperData = $helperData;
        $this->encryptor = $encryptor;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function exportItem($item)
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * @inheritdoc
     */
    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $collection = $this->lotteryAppColFactory->create(
            $this->getAttributeCollection(),
            $this->_parameters
        );
        $userIds = array_column($collection, 'user_id');
        $customerData = $this->customerAttributeCollection->getCustomerAttributes($userIds);
        $websiteOptions = $this->eavSourceInterface->getOptionArray(false, 'code');
        $statusOptions = $this->lotterySalesStatus->getOptionArray();
        foreach ($collection as $item) {
            $dataDecrypt = $this->encryptor->decryptCustomerData(CustomerInterface::ENTITY_TYPE_CASIO_LOTTERY_APPLICATION, $item);
            $data = $dataDecrypt + $item;
            $userId = $item[self::COL_USER_ID];
            $data = $this->getCustomerData($userId, $customerData, $data);
            $websiteId = $item[self::COL_WEBSITE_ID] ?? null;
            $data[self::COL_WEBSITE_ID] = $websiteOptions[$websiteId] ?? null;
            $statusId = $item[self::COL_STATUS] ?? null;
            $data[self::COL_STATUS] = $statusOptions[$statusId] ?? null;
            $data[self::COL_APPLICATION_FROM] = $this->helperData->getWebsiteFormatDateTime($item[LotterySalesInterface::APPLICATION_DATE_FROM] ?? null, $websiteId);
            $data[self::COL_APPLICATION_TO] = $this->helperData->getWebsiteFormatDateTime($item[LotterySalesInterface::APPLICATION_DATE_TO] ?? null, $websiteId);
            $data[self::COL_PURCHASE_DEADLINE] = $this->helperData->getWebsiteFormatDateTime($item[LotterySalesInterface::PURCHASE_DEADLINE] ?? null, $websiteId);
            $data[self::COL_LOTTERY_DATE] = $item[LotterySalesInterface::LOTTERY_DATE] ?? '';
            $data[self::COL_CREATED_AT] = $this->helperData->getWebsiteFormatDateTime($item[LotterySalesInterface::CREATED_AT] ?? null, $websiteId);
            $writer->writeRow($data);
        }
        return $writer->getContents();
    }

    /**
     * @param $userId
     * @param $customerData
     * @param $data
     * @return mixed
     */
    private function getCustomerData($userId, $customerData, $data)
    {
        if(isset($customerData[$userId])) {
            $dataDecrypt = $this->encryptor->decryptCustomerData(CustomerInterface::ENTITY_TYPE_CUSTOMER, $customerData[$userId]);
            $data[self::COL_CASIO_LOTTERY_APPLICATED_COUNT] = $dataDecrypt[self::COL_CASIO_LOTTERY_APPLICATED_COUNT] ?? 0;
            $data[self::COL_CASIO_LOTTERY_WIN_COUNT] = $dataDecrypt[self::COL_CASIO_LOTTERY_WIN_COUNT] ?? 0;
            $data[self::COL_FIRSTNAME] = $dataDecrypt[self::COL_FIRSTNAME] ?? "";
            $data[self::COL_LASTNAME] = $dataDecrypt[self::COL_LASTNAME] ?? "";
            $data[self::COL_EMAIL] = $dataDecrypt[self::COL_EMAIL] ?? "";
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return self::ENTITY_TYPE_CODE;
    }

    /**
     * @return array|string[]
     */
    protected function _getHeaderColumns()
    {
        $columns = $this->templateExportData;
        $filters = $this->_parameters;
        if (!isset($filters[Export::FILTER_ELEMENT_SKIP])) {
            return $columns;
        }

        if (count($filters[Export::FILTER_ELEMENT_SKIP]) === count($columns)) {
            throw new LocalizedException(__('There is no data for the export.'));
        }
        // remove the skipped from columns
        $skippedAttributes = array_flip($filters[Export::FILTER_ELEMENT_SKIP]);
        foreach ($columns as $key => $value) {
            if (array_key_exists($value, $skippedAttributes) === true) {
                unset($columns[$key]);
            }
        }
        return $columns;
    }

    /**
     * Inner writer object getter
     * @return Adapter|\Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     */
    public function getWriter()
    {
        return $this->writerFactory->create();
    }
}
