<?php

namespace KalyanUs\Scheme\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class SchemeLoginApi
{
    const SCHEME_Data = "scheme_data";
    const SCHEME_API_ENABLE= "scheme/api/enable";
    const SCHEME_API_USERNAME= "scheme/api/username";
    const SCHEME_API_PASSWORD= "scheme/api/password";
    const HOST_URL_PATH = "scheme/api/hosturl";

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Curl $curl
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Session $customerSession,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function generateSchemeToken()
    {
        if ($this->isSchemeApiEnabled()) {
            $baseApiUrl = $this->getConfigData(self::HOST_URL_PATH) . '/thirdparty/api/Users/login';
            try {
                $payload = json_encode([
                    'username' => $this->getConfigData(self::SCHEME_API_USERNAME),
                    'password' => $this->getConfigData(self::SCHEME_API_PASSWORD)
                ]);
                $this->curl->setHeaders([
                    'Content-Type' => 'application/json'
                ]);
                $this->curl->post($baseApiUrl, $payload);
                if ($this->curl->getStatus() != 200) {
                    throw new \Exception('Scheme Login returning forbidden');
                }
                $response = json_decode($this->curl->getBody(), true);
                if ($response && isset($response['error']['status']) && $response['error']['status'] != 200) {
                    throw new \Exception('Scheme Login api failed');
                }
                if (isset($response['data']['ttl']) && $response['data']['id']) {
                    return [
                        'token' => $response['data']['id'],
                        'expiry_time' => $response['data']['ttl']
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->error('Scheme Login Api eroor' .$e->getMessage());
            }
        }
        return null;
    }


    public function getSchemeToken()
    {
        try {
            $schemeData = $this->customerSession->getData(self::SCHEME_Data);
            $currentTime = $this->dateTime->gmtTimestamp();

            if (!isset($schemeData['token']) || !isset($schemeData['expiry_time'])) {
                $response = $this->generateSchemeToken();
                if ($response) {
                    $this->customerSession->setData(self::SCHEME_Data, [
                        'token' => $response['token'],
                        'expiry_time' => 900 + $currentTime // 15min expiration
                    ]);
                }
                $schemeData = $this->customerSession->getData(self::SCHEME_Data);
            } elseif ($currentTime >= $schemeData['expiry_time']) {
                $response = $this->generateSchemeToken();
                if ($response) {
                    $this->customerSession->setData(self::SCHEME_Data, [
                        'token' => $response['token'],
                        'expiry_time' => 900 + $currentTime // 15min expiration
                    ]);
                }
                $schemeData = $this->customerSession->getData(self::SCHEME_Data);
            }

            $schemeToken = $schemeData['token'] ?? null;
            return $schemeToken;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return null;
        }
    }


    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSchemeApiEnabled()
    {
        $storeId = $this->storeManager->getStore()->getId() ?? 0;
        return (bool) $this->scopeConfig->getValue(self::SCHEME_API_ENABLE,ScopeInterface::SCOPE_STORE,$storeId);
    }


    /**
     * @param $path
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getConfigData($path)
    {
        $storeId = $this->storeManager->getStore()->getId() ?? 0;
        return $this->scopeConfig->getValue($path,ScopeInterface::SCOPE_STORE,$storeId) ?? '';
    }
}
