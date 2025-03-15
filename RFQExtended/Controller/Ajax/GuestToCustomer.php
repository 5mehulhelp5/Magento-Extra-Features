<?php

namespace Codilar\RFQExtended\Controller\Ajax;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerFactory as CustomerModelFactory;

class GuestToCustomer implements HttpPostActionInterface
{

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    protected CustomerInterfaceFactory $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Encryptor
     */
    protected Encryptor $encryptor;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var CustomerModelFactory
     */
    protected CustomerModelFactory $customerModelFactory;

    /**
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param CustomerModelFactory $customerModelFactory
     * @param Encryptor $encryptor
     */
    public function __construct(
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        Session $customerSession,
        CustomerModelFactory $customerModelFactory,
        Encryptor $encryptor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->customerModelFactory = $customerModelFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException|LocalizedException
     */
    public function execute()
    {

        $is_customer_exist = false;
        $is_new_created = false;
        $customer_id = '';
        $params = $this->request->getParams();
        $email = $params['customer_email'];
        $firstName = $params['customer_first_name'];
        $lastName = $params['customer_last_name'];
        $password = $params['password'];
        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        try {
            $checkCustomer = $this->customerRepository->get($email,$websiteId);
            $customer_id = $checkCustomer->getId();
        } catch (\Exception $e) {

        }
        if (!$customer_id) {
            $customer = $this->customerFactory->create();
            $passwordHash = $this->encryptor->getHash($password,true);
            $customer->setWebsiteId($websiteId)
                ->setStoreId($storeId)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email);
            $customer = $this->customerRepository->save($customer,$passwordHash);

            $customer = $this->customerModelFactory->create();
            $customer->setWebsiteId($websiteId);
            $loadCustomer = $customer->loadByEmail($email);
            $this->customerSession->setCustomerAsLoggedIn($loadCustomer);
            $customer_id = $customer->getId();
            $this->customerSession->setCustomerAsLoggedIn($customer);
            $is_new_created = true;
        } else {
            $is_customer_exist = true;
        }

        $data = ['customer_id' => $customer_id,'is_exist' => $is_customer_exist,'is_new_created' => $is_new_created];
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($data);
        return $resultJson;
    }
}
