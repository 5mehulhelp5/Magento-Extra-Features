<?php

namespace Casio\LotterySale\Block\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Image;
use Casio\LotterySale\Api\Data\LotteryApplicationInterface;
use Casio\LotterySale\Api\LotteryApplicationRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

class ApplicationHistory extends Template
{
    /**
     * @var Session
     */
    private Session $session;
    /**
     * @var LotteryApplicationRepositoryInterface
     */
    private LotteryApplicationRepositoryInterface $lotteryApplicationRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var Image
     */
    private Image $imageHelper;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    private \Psr\Log\LoggerInterface $logger;
    /**
     * @var ListProduct
     */
    private ListProduct $listProduct;
    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * ApplicationHistory constructor.
     * @param Template\Context $context
     * @param Session $session
     * @param LotteryApplicationRepositoryInterface $lotteryApplicationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Image $image
     * @param ProductRepositoryInterface $productRepository
     * @param ListProduct $listProduct
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        LotteryApplicationRepositoryInterface $lotteryApplicationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Image $image,
        ProductRepositoryInterface $productRepository,
        ListProduct $listProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->session = $session;
        $this->lotteryApplicationRepository = $lotteryApplicationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->imageHelper = $image;
        $this->productRepository = $productRepository;
        $this->listProduct = $listProduct;
        $this->logger = $context->getLogger();
    }

    /**
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerLotteryApplication()
    {
        $customerId = $this->session->getCustomerId();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $sortOrder = $this->sortOrderBuilder
            ->setField('id')
            ->setDirection('DESC')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(LotteryApplicationInterface::USER_ID, $customerId)
            ->addFilter(LotteryApplicationInterface::WEBSITE_ID, $websiteId)
            ->addSortOrder($sortOrder)
            ->create();
        return $this->lotteryApplicationRepository->getList($searchCriteria);
    }

    /**
     * @param $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        return $this->listProduct->getAddToCartUrl($product);
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    public function getCartUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/cart', ['_secure' => true]);
    }

    /**
     * @param $productId
     * @return false|\Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getLogMessage());
            return false;
        }
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductImageUrl($product)
    {
        return $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
    }

    /**
     * @param $date
     * @return \DateTime
     * @throws \Exception
     */
    public function convertDateTime($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getCurrentTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @param $item
     * @param $now
     * @return bool
     */
    public function isExpiredDeadline($item, $now)
    {
        if (strtotime($item->getPurchaseDeadline()) > $now->getTimestamp()) {
            return false;
        }
        return true;
    }
}
