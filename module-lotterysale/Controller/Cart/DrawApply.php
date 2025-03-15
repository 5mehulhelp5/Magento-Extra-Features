<?php

namespace Casio\LotterySale\Controller\Cart;

use Casio\CasioIdAuth\Model\CasioId\Client;
use Casio\LotterySale\Controller\Action;
use Casio\LotterySale\Helper\Data;
use Casio\LotterySale\Model\LotteryApplication;
use Casio\LotterySale\Model\LotteryManagement;
use Casio\LotterySale\Model\SalesType\Validator\Address;
use Casio\LotterySale\Model\SalesType\Validator\Lottery as ValidationLottery;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class DrawApply extends Action implements HttpPostActionInterface
{
    /**
     * @var FormFactory
     */
    private FormFactory $formFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private RegionInterfaceFactory $regionDataFactory;

    /**
     * @var RegionFactory
     */
    private RegionFactory $regionFactory;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressDataFactory;

    /**
     * @var Address
     */
    private Address $addressValidator;

    /**
     * @var LotteryManagement
     */
    private LotteryManagement $lotteryManagement;

    /**
     * DrawApply constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ValidationLottery $validationLottery
     * @param Session $customerSession
     * @param SessionManagerInterface $sessionManager
     * @param Client $clientCasioId
     * @param \Casio\CasioIdAuth\Model\Session $casioSession
     * @param FormFactory $formFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param RegionFactory $regionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param AddressInterfaceFactory $addressDataFactory
     * @param Address $addressValidator
     * @param LotteryManagement $lotteryManagement
     * @param Data $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        ValidationLottery $validationLottery,
        Session $customerSession,
        SessionManagerInterface $sessionManager,
        Client $clientCasioId,
        \Casio\CasioIdAuth\Model\Session $casioSession,
        FormFactory $formFactory,
        RegionInterfaceFactory $regionDataFactory,
        RegionFactory $regionFactory,
        DataObjectHelper $dataObjectHelper,
        AddressInterfaceFactory $addressDataFactory,
        Address $addressValidator,
        LotteryManagement $lotteryManagement,
        Data $data
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $productRepository,
            $validationLottery,
            $customerSession,
            $sessionManager,
            $clientCasioId,
            $data,
            $casioSession
        );
        $this->formFactory = $formFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->regionFactory = $regionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressDataFactory = $addressDataFactory;
        $this->addressValidator = $addressValidator;
        $this->lotteryManagement = $lotteryManagement;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $status = $this->validateLottery();
        switch ($status) {
            case LotteryApplication::STATUS_CLOSE:
            case LotteryApplication::STATUS_APPLIED:
                return $this->goDrawClosePage();
            case LotteryApplication::STATUS_NOT_LOTTERY:
                return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        try {
            $address = $this->extractAddress();
            $this->addressValidator->validate($address);

            $data = $this->mappingDataRequest($this->getRequest()->getPostValue());
            $data['region'] = $address->getRegion()->getRegion();
            $this->lotteryManagement->applyLottery($data);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/drawconfirm', ['sku' => $this->getRequest()->getParam('sku')]);
            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addExceptionMessage($e, __('We can\'t unable to apply this lottery product.'));
        }

        return $this->_redirect('*/*/draw', ['sku' => $this->getRequest()->getParam('sku')]);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @throws \Magento\Framework\Exception\NotFoundException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->validateAvailable()) {
            return $this->goNoRoutePage();
        }
        if (!$this->getSession()->isLoggedIn()) {
            $this->casioSession->setUrl($this->_url->getUrl('*/*/draw', ['sku' => $this->getRequest()->getParam('sku')]));
            return $this->_redirect($this->clientCasioId->createAuthUrl());
        }

        return parent::dispatch($request);
    }

    /**
     * @return AddressInterface
     */
    protected function extractAddress()
    {
        $addressForm = $this->formFactory->create(
            'customer_address',
            'customer_address_edit',
            []
        );

        $addressData = $addressForm->extractData($this->getRequest());
        $attributeValues = $addressForm->compactData($addressData);

        $this->updateRegionData($attributeValues);
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $attributeValues,
            AddressInterface::class
        );

        $addressDataObject->setCustomerId($this->getSession()->getCustomerId())
            ->setIsDefaultBilling(false)
            ->setIsDefaultShipping(false);

        return $addressDataObject;
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return void
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null,
        ];

        $region = $this->regionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $region,
            $regionData,
            RegionInterface::class
        );
        $attributeValues['region'] = $region;
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function mappingDataRequest($data)
    {
        $casioLotterySales = $this->getProduct()->getExtensionAttributes()->getCasioLotterySales();
        $street = $data['street'];
        $data['street'] = $street[0] ?? '';
        $data['building'] = $street[1] ?? '';
        $data['user_id'] = $this->getSession()->getCustomerId();
        $data['email'] = $this->getSession()->getCustomer()->getEmail();
        $data['lottery_date'] = $casioLotterySales ? $casioLotterySales->getLotteryDate() : '';
        $data['lottery_sales_id'] = $casioLotterySales ? $casioLotterySales->getId() : '';

        return $data;
    }
}
