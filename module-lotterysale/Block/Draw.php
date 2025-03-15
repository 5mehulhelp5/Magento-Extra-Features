<?php

namespace Casio\LotterySale\Block;

use Casio\CasioIdAuth\Model\CasioId\Client;
use Casio\LotterySale\Model\SalesType\Validator\Lottery;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Draw extends Template
{
    /**
     * @var ProductInterface|null
     */
    protected ?ProductInterface $product = null;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var \Casio\CasioIdAuth\Model\CasioId\Client
     */
    protected Client $clientCasioId;

    /**
     * @var \Casio\CasioIdAuth\Model\Session
     */
    protected \Casio\CasioIdAuth\Model\Session $casioSession;

    /**
     * @var Data
     */
    protected Data $pricingHelper;

    /**
     * @var \Casio\AEMLinkage\Helper\Data
     */
    protected \Casio\AEMLinkage\Helper\Data $dataHelper;

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Lottery
     */
    protected Lottery $lotteryValidator;

    /**
     * DrawNotice constructor.
     * @param Template\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param Session $customerSession
     * @param Client $clientCasioId
     * @param \Casio\CasioIdAuth\Model\Session $casioSession
     * @param Data $pricingHelper
     * @param \Casio\AEMLinkage\Helper\Data $dataHelper
     * @param SessionManagerInterface $sessionManager
     * @param StoreManagerInterface $storeManager
     * @param Lottery $lotteryValidator
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        Session $customerSession,
        Client $clientCasioId,
        \Casio\CasioIdAuth\Model\Session $casioSession,
        Data $pricingHelper,
        \Casio\AEMLinkage\Helper\Data $dataHelper,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        Lottery $lotteryValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->clientCasioId = $clientCasioId;
        $this->casioSession = $casioSession;
        $this->pricingHelper = $pricingHelper;
        $this->dataHelper = $dataHelper;
        $this->sessionManager = $sessionManager;
        $this->storeManager = $storeManager;
        $this->lotteryValidator = $lotteryValidator;
    }

    /**
     * Check customer is login
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isLogin(): bool
    {
        if ($this->customerSession->isLoggedIn()) {
            return true;
        }
        $this->casioSession->setUrl($this->getApplyUrl());

        return false;
    }

    /**
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct(): ProductInterface
    {
        if ($this->product == null) {
            $this->product = $this->productRepository->get($this->getRequest()->getParam('sku'));
        }
        return $this->product;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoginUrl()
    {
        return $this->clientCasioId->createAuthUrl();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegisterUrl()
    {
        return $this->clientCasioId->createAuthUrl('create');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getApplyUrl()
    {
        return $this->getUrl('checkout/cart/draw', ['sku' => $this->getProduct()->getSku()]);
    }

    /**
     * Returns product price
     *
     * @return float|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPrice()
    {
        return $this->pricingHelper->currency($this->getProduct()->getFinalPrice(), true, true);
    }

    /**
     * Get top page url
     */
    public function getTopPageUrl()
    {
        return $this->getUrl($this->dataHelper->getTopPage());
    }

    /**
     * Get draw history url
     *
     * @return string
     */
    public function getDrawHistoryUrl()
    {
        return $this->getUrl('sales/draw/index');
    }

    /**
     * @return array
     */
    public function getCasioLotterySales(): array
    {
        if ($lotterySaleSession = $this->sessionManager->getCasioLotterySales()) {
            $this->sessionManager->unsCasioLotterySales();

            return $lotterySaleSession;
        }

        return [];
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStatusLotterySale()
    {
        return $this->lotteryValidator->validate($this->getProduct());
    }
}
