<?php

namespace KalyanUs\Scheme\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use KalyanUs\Scheme\Model\Api\SchemeLoginApi;
use Psr\Log\LoggerInterface;
class GetCustomerLedgerApi
{
    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var \KalyanUs\Scheme\Model\Api\SchemeLoginApi
     */
    private SchemeLoginApi $schemeLoginApi;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Curl $curl
     * @param SchemeLoginApi $schemeLoginApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        SchemeLoginApi $schemeLoginApi,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->schemeLoginApi = $schemeLoginApi;
        $this->logger = $logger;
    }

    public function getCustomerLedgerData($enrollmentNo)
    {
        if ($this->schemeLoginApi->isSchemeApiEnabled()) {
            try {
                $baseApiUrl = $this->schemeLoginApi->getConfigData(SchemeLoginApi::HOST_URL_PATH) . '/thirdparty/api/externals/getCustomerLedgerReport';
                $schemeToken = $this->schemeLoginApi->getSchemeToken() ?? null;
                if (!$schemeToken) {
                    throw new \Exception('Login Token is null');
                }
                $queryParams = http_build_query([
                    'access_token' => $schemeToken,
                    'EnrollmentNo' => $enrollmentNo
                ]);
                $urlWithParams = $baseApiUrl . '?' . $queryParams;
                $this->curl->setHeaders([
                    'Content-Type' => 'application/json'
                ]);
                $this->curl->get($urlWithParams);
                if ($this->curl->getStatus() != 200) {
                    throw new \Exception('Scheme Login returning forbidden');
                }
                $response = json_decode($this->curl->getBody(), true);
                if ($response && isset($response['error']['status']) && $response['error']['status'] != 200) {
                    throw new \Exception($response['error']['message']);
                }
                return [
                    'status' => true,
                    'data' => $response['data']['Response']
                ];
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return [
                    'status' => false,
                    'message' => $e->getMessage(). 'Please Try again after some time'
                ];
            }
        }
    }
}
