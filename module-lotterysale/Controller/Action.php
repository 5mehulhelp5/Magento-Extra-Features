<?php

namespace Casio\LotterySale\Controller;

use Casio\CasioIdAuth\Model\CasioId\Client;
use Casio\LotterySale\Helper\Data;
use Casio\LotterySale\Model\SalesType\Validator\Lottery as ValidationLottery;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    protected $product = null;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var ValidationLottery
     */
    protected ValidationLottery $validationLottery;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManager;

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
    protected Data $data;

    /**
     * DrawNotice constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ValidationLottery $validationLottery
     * @param Session $customerSession
     * @param SessionManagerInterface $sessionManager
     * @param Client $clientCasioId
     * @param Data $data
     * @param \Casio\CasioIdAuth\Model\Session $casioSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        ValidationLottery $validationLottery,
        Session $customerSession,
        SessionManagerInterface $sessionManager,
        Client $clientCasioId,
        Data $data,
        \Casio\CasioIdAuth\Model\Session $casioSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->validationLottery = $validationLottery;
        $this->customerSession = $customerSession;
        $this->sessionManager = $sessionManager;
        $this->clientCasioId = $clientCasioId;
        $this->data = $data;
        $this->casioSession = $casioSession;
    }

    /**
     * @return false|\Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        if ($this->product == null) {
            $sku = (string)$this->getRequest()->getParam('sku');

            try {
                $this->product = $this->productRepository->get($sku);
            } catch (\Exception $ex) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($ex);
                return false;
            }
        }

        return $this->product;
    }

    /**
     * Validate available lottery sales
     */
    public function validateAvailable()
    {
        $result = true;
        if (!$this->data->isEnabled()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            $result = false;
        }

        return $result;
    }
    /**
     * Validation is lottery sales product
     */
    public function validateLottery(): int
    {
        return $this->validationLottery->validate($this->getProduct());
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->customerSession;
    }

    /**
     * @return Forward
     */
    protected function goNoRoutePage(): Forward
    {
        return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
    }

    /**
     * Go to lottery sale page is close
     *
     * @return ResponseInterface
     */
    protected function goDrawClosePage(): ResponseInterface
    {
        return $this->_redirect('*/*/drawclose', ['sku' => $this->getRequest()->getParam('sku')]);
    }
}
