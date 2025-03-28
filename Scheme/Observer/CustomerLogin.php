<?php

namespace KalyanUs\Scheme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use KalyanUs\Scheme\Model\Api\SchemeLoginApi;
use Psr\Log\LoggerInterface;
class CustomerLogin implements ObserverInterface
{
    /**
     * @var SchemeLoginApi
     */
    private SchemeLoginApi $schemeLoginApi;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param SchemeLoginApi $schemeLoginApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        SchemeLoginApi $schemeLoginApi,
        LoggerInterface $logger
    ) {
        $this->schemeLoginApi = $schemeLoginApi;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer || !$customer->getId()) {
            $this->logger->info('CustomerLogin: No valid customer found, skipping API call.');
            return;
        }
        if ($this->schemeLoginApi->isSchemeApiEnabled()) {
            $this->schemeLoginApi->getSchemeToken();
        }
    }
}
