<?php

namespace KalyanUs\Scheme\Controller\Sync;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use KalyanUs\Scheme\Model\Api\GetCustomerLedgerApi;
use KalyanUs\Scheme\Model\SchemeProcessForApi;

class Enrollment implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var GetCustomerLedgerApi
     */
    private GetCustomerLedgerApi $getCustomerLedgerApi;

    /**
     * @var SchemeProcessForApi
     */
    private SchemeProcessForApi $schemeProcessForApi;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @param RequestInterface $request
     * @param GetCustomerLedgerApi $getCustomerLedgerApi
     * @param SchemeProcessForApi $schemeProcessForApi
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        RequestInterface $request,
        GetCustomerLedgerApi $getCustomerLedgerApi,
        SchemeProcessForApi $schemeProcessForApi,
        JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->getCustomerLedgerApi = $getCustomerLedgerApi;
        $this->schemeProcessForApi = $schemeProcessForApi;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $params = $this->request->getParams();
        $resultJson = $this->resultJsonFactory->create();
        if (isset($params['enrollment_no']) && $params['enrollment_no']) {
            $response =  $this->getCustomerLedgerApi->getCustomerLedgerData($params['enrollment_no']);
            if (isset($response['status']) && $response['status'] && isset($response['data'])) {
                $schemeData =  $this->schemeProcessForApi->saveScheme($response['data'],$params['enrollment_no']);
                if (isset($schemeData['enrollment_id']) && $schemeData['enrollment_id']) {
                    $response =  [
                        'status' => true,
                        'message' => 'Scheme Data Fetched Successfully'
                    ];
                }
            } else {
                $response =  [
                    'status' => false,
                    'message' => $response['message']
                ];
            }
        } else {
            $response =  [
                'status' => false,
                'message' => 'Enrollment Id is required'
            ];
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
