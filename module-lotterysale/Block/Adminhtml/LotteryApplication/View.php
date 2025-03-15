<?php

namespace Casio\LotterySale\Block\Adminhtml\LotteryApplication;

use Casio\LotterySale\Api\LotteryApplicationRepositoryInterface;
use Casio\LotterySale\Api\LotterySalesRepositoryInterface;
use Casio\LotterySale\Model\Config\Source\Status as LotterySaleStatus;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View as CustomerHelperView;

/**
 * Class View
 * Casio\LotterySale\Block\Adminhtml\LotteryApplication
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var LotterySalesRepositoryInterface
     */
    private LotterySalesRepositoryInterface $lotterySalesRepository;
    /**
     * @var LotteryApplicationRepositoryInterface
     */
    private LotteryApplicationRepositoryInterface $lotteryApplicationRepository;
    /**
     * @var LotterySaleStatus
     */
    private LotterySaleStatus $lotterySaleStatus;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var CustomerHelperView  */
    protected $viewHelper;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     * @param LotteryApplicationRepositoryInterface $lotteryApplicationRepository
     * @param LotterySalesRepositoryInterface $lotterySalesRepository
     * @param LotterySaleStatus $lotterySaleStatus
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerHelperView $viewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        LotteryApplicationRepositoryInterface $lotteryApplicationRepository,
        LotterySalesRepositoryInterface $lotterySalesRepository,
        LotterySaleStatus $lotterySaleStatus,
        CustomerRepositoryInterface $customerRepository,
        CustomerHelperView $viewHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->lotteryApplicationRepository = $lotteryApplicationRepository;
        $this->lotterySalesRepository = $lotterySalesRepository;
        $this->lotterySaleStatus = $lotterySaleStatus;
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->viewHelper = $viewHelper;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        $lotterySales = $this->getLotterySales();
        return $this->productRepository->getById($lotterySales->getProductId());
    }

    /**
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLotteryApplication()
    {
        $id = $this->_request->getParam('id');
        return $this->lotteryApplicationRepository->get($id);
    }

    /**
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLotterySales()
    {
        $lotteryApplication = $this->getLotteryApplication();
        return $this->lotterySalesRepository->get($lotteryApplication->getLotterySalesId());
    }

    /**
     * @param $createdAt
     * @return \DateTime
     * @throws \Exception
     */
    public function getCreatedAt($createdAt)
    {
        $lotterySales = $this->getLotterySales();
        $timezone = $this->_localeDate->getConfigTimezone(
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $lotterySales->getWebsiteId()
        );
        $createdAt = $this->_localeDate->formatDateTime(
            $createdAt,
            $dateType = \IntlDateFormatter::SHORT,
            $dateType = \IntlDateFormatter::SHORT,
            'en_US',
            $timezone
        );
        return new \DateTime($createdAt);
    }

    /**
     * @param $status
     * @return int|mixed
     */
    public function getStatus($status)
    {
        $statusOptions = $this->lotterySaleStatus->getOptionArray();
        return $statusOptions[$status] ?? '0';
    }

    /**
     * @param $id
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerName($id)
    {
        $customer = $this->customerRepository->getById($id);
        if($customer->getId()) {
            return $this->viewHelper->getCustomerName($customer);
        }
        return '';
    }
}
