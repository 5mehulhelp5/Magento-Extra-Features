<?php


namespace Prajwal\Assignmentnew4\Plugin;

use Magento\Customer\Model\AccountManagement as Subject;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;

class AccountManagement extends Subject
{
    /**
     * Configuration paths for create account email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    /**
     * Configuration paths for register no password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    /**
     * Configuration paths for remind email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    /**
     * Configuration paths for remind email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    /**
     * Configuration paths for forgot email email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    /**
     * Configuration paths for forgot email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    /**
     * Configuration paths for account confirmation required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see AccountConfirmation::XML_PATH_IS_CONFIRM
     */
    public const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

    /**
     * Configuration paths for account confirmation email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    /**
     * Configuration paths for confirmation confirmed email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    public const EMAIL_REMINDER = 'email_reminder';

    public const EMAIL_RESET = 'email_reset';

    /**
     * Configuration path to customer password minimum length
     */
    public const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    public const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * Configuration path to customer reset password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see Magento/Customer/Model/EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    /**
     * Minimum password length
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see \Magento\Customer\Model\AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH
     */
    public const MIN_PASSWORD_LENGTH = 6;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CredentialsValidator|null $credentialsValidator
     * @param DateTimeFactory|null $dateTimeFactory
     * @param AccountConfirmation|null $accountConfirmation
     * @param SessionManagerInterface|null $sessionManager
     * @param SaveHandlerInterface|null $saveHandler
     * @param CollectionFactory|null $visitorCollectionFactory
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param AddressRegistry|null $addressRegistry
     * @param GetCustomerByToken|null $getByToken
     * @param AllowedCountries|null $allowedCountriesReader
     * @param SessionCleanerInterface|null $sessionCleaner
     * @param AuthorizationInterface|null $authorization
     * @param AuthenticationInterface|null $authentication
     * @param Backend|null $eavValidator
     * @param Subject $subject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        AddressRegistry $addressRegistry = null,
        GetCustomerByToken $getByToken = null,
        AllowedCountries $allowedCountriesReader = null,
        SessionCleanerInterface $sessionCleaner = null,
        AuthorizationInterface $authorization = null,
        AuthenticationInterface $authentication = null,
        Backend $eavValidator = null,
        Subject $subject
    ) {
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $objectManager = ObjectManager::getInstance();
        $this->credentialsValidator =
            $credentialsValidator ?: $objectManager->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: $objectManager
            ->get(AccountConfirmation::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: $objectManager->get(SearchCriteriaBuilder::class);
        $this->addressRegistry = $addressRegistry
            ?: $objectManager->get(AddressRegistry::class);
        $this->getByToken = $getByToken
            ?: $objectManager->get(GetCustomerByToken::class);
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: $objectManager->get(AllowedCountries::class);
        $this->sessionCleaner = $sessionCleaner ?? $objectManager->get(SessionCleanerInterface::class);
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
        $this->authentication = $authentication ?? $objectManager->get(AuthenticationInterface::class);
        $this->eavValidator = $eavValidator ?? $objectManager->get(Backend::class);
        $this->subject=$subject;
        parent::__construct(
            $customerFactory,
            $eventManager,
            $storeManager,
            $mathRandom,
            $validator,
            $validationResultsDataFactory,
            $addressRepository,
            $customerMetadataService,
            $customerRegistry,
            $logger,
            $encryptor,
            $configShare,
            $stringHelper,
            $customerRepository,
            $scopeConfig,
            $transportBuilder,
            $dataProcessor,
            $registry,
            $customerViewHelper,
            $dateTime,
            $customerModel,
            $objectFactory,
            $extensibleDataObjectConverter,
            $credentialsValidator = null,
            $dateTimeFactory = null,
            $accountConfirmation = null,
            $sessionManager = null,
            $saveHandler = null,
            $visitorCollectionFactory = null,
            $searchCriteriaBuilder = null,
            $addressRegistry = null,
            $getByToken = null,
            $allowedCountriesReader = null,
            $sessionCleaner = null,
            $authorization = null,
            $authentication = null,
            $eavValidator = null
        );
    }
    /**
     * Disable the Welcome Email
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string $hash
     * @param string $redirectUrl
     * @throws InputMismatchException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundCreateAccountWithPasswordHash(Subject $subject, callable $proceed, CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        $this->validateCustomerStoreIdByWebsiteId($customer);

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email address already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                if (!$this->isAddressAllowedForWebsite($address, $customer->getStoreId())) {
                    continue;
                }
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->customerRegistry->remove($customer->getId());
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
//        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }
    /**
     * Check is address allowed for store
     *
     * @param AddressInterface $address
     * @param int|null $storeId
     * @return bool
     */
    private function isAddressAllowedForWebsite(AddressInterface $address, $storeId): bool
    {
        $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);

        return in_array($address->getCountryId(), $allowedCountries);
    }
    /**
     * Validate customer store id by customer website id.
     *
     * @param CustomerInterface $customer
     * @return bool
     * @throws LocalizedException
     */
    public function validateCustomerStoreIdByWebsiteId(CustomerInterface $customer)
    {
        if (!$this->isCustomerInStore($this->configShare->getWebsiteId(), $customer->getStoreId())) {
            throw new LocalizedException(__('The store view is not in the associated website.'));
        }

        return true;
    }
}
