<?php

namespace Casio\LotterySale\Model;

use Casio\LotterySale\Api\Data\LotteryApplicationInterface;
use Casio\LotterySale\Api\Data\LotteryApplicationInterfaceFactory;
use Casio\LotterySale\Api\LotteryApplicationRepositoryInterface;
use Casio\LotterySale\Model\ResourceModel\LotteryApplication\CollectionFactory as LotteryApplicationCollectionFactory;
use Casio\LotterySale\Model\ResourceModel\LotterySales\CollectionFactory as LotterySalesCollectionFactory;
use Casio\LotterySale\Helper\Data as LotterySaleHelper;
use Casio\LotterySale\Model\Mail\TransportBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Helper\Image as ProductImageHelper;

/**
 * Class LotteryManagement
 * Casio\LotterySale\Model
 */
class LotteryManagement
{
    /**
     * Default pattern for Sequence
     */
    const DEFAULT_PATTERN = "%s%'.09d";

    /**
     * @var LotteryApplicationInterfaceFactory
     */
    private LotteryApplicationInterfaceFactory $lotteryApplicationFactory;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var LotteryApplicationRepositoryInterface
     */
    private LotteryApplicationRepositoryInterface $lotteryApplicationRepository;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManager;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ResourceModel\LotteryApplication
     */
    private ResourceModel\LotteryApplication $lotteryApplicationResource;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var LotterySaleHelper
     */
    private LotterySaleHelper $lotterySaleHelper;

    /**
     * @var View
     */
    private View $customerViewHelper;

    /**
     * @var CurrentCustomer
     */
    protected CurrentCustomer $currentCustomer;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /** @var CustomerFactory */
    protected $_customerFactory;

    /** @var ProductRepositoryInterface */
    protected $_productRepo;

    /** @var ProductImageHelper */
    protected $_imageHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /** @var PriceCurrencyInterface */
    protected $priceCurrencyInterface;

    /** @var LotteryApplicationCollectionFactory */
    protected $lotteryApplicationCollection;

    /** @var LotterySalesCollectionFactory */
    protected $lotterySalesCollection;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * LotteryManagement constructor.
     * @param DataObjectHelper $dataObjectHelper
     * @param LotteryApplicationInterfaceFactory $lotteryApplicationFactory
     * @param LotteryApplicationRepositoryInterface $lotteryApplicationRepository
     * @param SessionManagerInterface $sessionManager
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ResourceModel\LotteryApplication $lotteryApplicationResource
     * @param TransportBuilder $transportBuilder
     * @param LotterySaleHelper $lotterySaleHelper
     * @param View $customerViewHelper
     * @param CurrentCustomer $currentCustomer
     * @param ResourceConnection $resourceConnection
     * @param CustomerFactory $customerFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductImageHelper $imageHelper
     * @param LotteryApplicationCollectionFactory $lotteryApplicationCollection
     * @param LotterySalesCollectionFactory $lotterySalesCollection
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        LotteryApplicationInterfaceFactory $lotteryApplicationFactory,
        LotteryApplicationRepositoryInterface $lotteryApplicationRepository,
        SessionManagerInterface $sessionManager,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        \Casio\LotterySale\Model\ResourceModel\LotteryApplication $lotteryApplicationResource,
        TransportBuilder $transportBuilder,
        LotterySaleHelper $lotterySaleHelper,
        View $customerViewHelper,
        CurrentCustomer $currentCustomer,
        ResourceConnection $resourceConnection,
        CustomerFactory $customerFactory,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        PriceCurrencyInterface $priceCurrency,
        ProductImageHelper $imageHelper,
        LotteryApplicationCollectionFactory $lotteryApplicationCollection,
        LotterySalesCollectionFactory $lotterySalesCollection,
        UrlInterface $urlBuilder
    )
    {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->lotteryApplicationFactory = $lotteryApplicationFactory;
        $this->lotteryApplicationRepository = $lotteryApplicationRepository;
        $this->sessionManager = $sessionManager;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->lotteryApplicationResource = $lotteryApplicationResource;
        $this->transportBuilder = $transportBuilder;
        $this->lotterySaleHelper = $lotterySaleHelper;
        $this->customerViewHelper = $customerViewHelper;
        $this->currentCustomer = $currentCustomer;
        $this->resourceConnection = $resourceConnection;
        $this->_customerFactory = $customerFactory;
        $this->_productRepo = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->priceCurrencyInterface = $priceCurrency;
        $this->_imageHelper = $imageHelper;
        $this->lotteryApplicationCollection = $lotteryApplicationCollection;
        $this->lotterySalesCollection = $lotterySalesCollection;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function applyLottery(array $data)
    {
        $lotteryApplicationDataObject = $this->lotteryApplicationFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $lotteryApplicationDataObject,
            $data,
            LotteryApplicationInterface::class
        );

        $lotteryCode = $this->generateLotteryCode();
        $lotteryApplicationDataObject->setLotterySalesCode($lotteryCode);
        $lotteryApplicationDataObject->setWebsiteId($this->storeManager->getWebsite()->getId());
        $lotteryApplicationDataObject->setOrdered(LotteryApplication::STATUS_NOT_ORDERED);
        $lotteryApplicationDataObject->setStatus(LotteryApplication::STATUS_APPLYING);
        $data['lottery_code'] = $lotteryCode;

        $connection = $this->resourceConnection->getConnection();
        try {
            $connection->beginTransaction();
            $this->lotteryApplicationRepository->save($lotteryApplicationDataObject);

            $customer = $this->customerSession->getCustomer();
            $casioLotteryApplicatedCount = $this->customerRepository->getById($this->customerSession->getCustomerId())
                ->getCustomAttribute('casio_lottery_applicated_count');
            $casioLotteryApplicatedCountValue = $casioLotteryApplicatedCount ? $casioLotteryApplicatedCount->getValue() : 0;
            $customer->setCasioLotteryApplicatedCount(
                (int)$casioLotteryApplicatedCountValue + 1
            );
            $customer->getResource()->saveAttribute($customer, 'casio_lottery_applicated_count');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        // Send mail notify
        $this->sendMailApply($data);

        $this->sessionManager->setCasioLotterySales($lotteryApplicationDataObject->__toArray());
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendMailApply(array $data)
    {
        $customer = $this->customerSession->getCustomer();
        $templateIdentifier = $this->lotterySaleHelper->getReceptionIdentifierEmail(
            $this->storeManager->getWebsite()->getId()
        );
        $templateOptions = [
            'area' => Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()
        ];
        $currentCustomer = $this->currentCustomer->getCustomer();
        if (isset($data['lastname'])) {
            $currentCustomer->setLastname($data['lastname']);
        }
        if (isset($data['firstname'])) {
            $currentCustomer->setFirstname($data['firstname']);
        }
        $templateVars = [
            'sku' => $data['sku'],
            'lottery_code' => $data['lottery_code'],
            'lottery_date' => $data['lottery_date'],
            'full_name' => $this->customerViewHelper->getCustomerName($currentCustomer),
            'draw_history_url' => $this->urlBuilder->getUrl('sales/draw/index')
        ];
        $from = $this->lotterySaleHelper->getIdentity($this->storeManager->getWebsite()->getId());
        $to = $customer->getEmail();

        $this->transportBuilder->sendMail($templateIdentifier, $templateOptions, $templateVars, $from, $to);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateLotteryCode()
    {
        $nextAutoincrement = $this->lotteryApplicationResource->getNextAutoincrement();

        return sprintf(
            self::DEFAULT_PATTERN,
            $this->storeManager->getStore()->getId(),
            $nextAutoincrement
        );
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function sendEmailWinLotterySales($data)
    {
        try {
            $sku = $data['sku'] ?? null;
            $websiteId = $data['website_id'] ?? null;
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->getStoreDefaultByWebsiteId($websiteId);
            $storeId = $store->getId();
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->_productRepo->get($sku, false , $storeId);
            /** @var \Magento\Customer\Model\Customer $currentCustomer */
            $currentCustomer = $data['customer'];
            $purchaseDeadline = $data['purchase_deadline'] ?? null;
            $purchaseDeadline = $this->lotterySaleHelper->getWebsiteFormatDateTime($purchaseDeadline, $websiteId, 'Y/m/d H:i');
            $price = $this->convertAndFormatPrice($product->getPrice(), $storeId);
            $productImage = $this->_imageHelper->init($product, 'product_thumbnail_image')->getUrl();
            $productHtml = '';
            $templateIdentifier = $this->lotterySaleHelper->getWinIdentifierEmail(
                $websiteId
            );
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $customerFullName = $currentCustomer->getFirstname() . ' ' . $currentCustomer->getLastname();
            $lotteryNumber = $data['lottery_sales_code'] ?? null;
            $productUrl = $product->getProductUrl();
            $baseUrl = $store->getBaseUrl();
            $drawHistoryUrl = $baseUrl.'sales/draw/index';
            $templateVars = [
                'sku' => $sku,
                'purchase_deadline' => $purchaseDeadline,
                'full_name' => $customerFullName,
                'product_item' => $productHtml,
                'price' => $price,
                'product_image' => $productImage,
                'lottery_number' => $lotteryNumber,
                'product_url' => $productUrl,
                'draw_history_url' => $drawHistoryUrl
            ];
            $from = $this->lotterySaleHelper->getWinIdentity($websiteId);
            $to = isset($data['email']) && $data['email'] != '' ? $data['email'] : $currentCustomer->getEmail();
            $this->transportBuilder->sendMail($templateIdentifier, $templateOptions, $templateVars, $from, $to, $storeId);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $price
     * @param $storeId
     * @param false $includeContainer
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function convertAndFormatPrice($price, $storeId, $includeContainer = false)
    {
        $currencyCode = $this->storeManager->getStore($storeId)->getCurrentCurrencyCode();
        return $this->priceCurrencyInterface->convertAndFormat($price, $includeContainer, PriceCurrencyInterface::DEFAULT_PRECISION, $storeId, $currencyCode);
    }

    /**
     * @param $websiteId
     * @return \Magento\Store\Model\Store
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStoreDefaultByWebsiteId($websiteId)
    {
        return $this->storeManager->getWebsite($websiteId)->getDefaultStore();
    }
}
