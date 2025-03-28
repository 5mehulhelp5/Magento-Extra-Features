<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Enrollment;

use Magento\Framework\Controller\Result\JsonFactory;

class Pincodeapi extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helperConfigScheme =$helperConfigScheme;
        $this->helperDataScheme =$helperDataScheme;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $response = [];
            $pincode = $this->getRequest()->getPost('pincode');
            if ($pincode) {

                $postalPincodeApiUrl = 'https://api.postalpincode.in/pincode/'.$pincode;
                // @codingStandardsIgnoreStart
                $ch = curl_init($postalPincodeApiUrl);
                curl_setopt($ch, CURLOPT_URL, $postalPincodeApiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_ENCODING, "");
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);

                $response1 = curl_exec($ch);
                curl_close($ch);
                $jresponse = json_decode($response1, true);
                // @codingStandardsIgnoreEnd
                if ($jresponse[0]['Status']=='Success') {
                    $postDetailsArr = $jresponse[0]['PostOffice'];
                    if (count($postDetailsArr) > 0) {

                        $response['status'] = true;
                        $response['data']['city'] = $postDetailsArr[0]['District'];
                        $response['data']['state'] = $postDetailsArr[0]['State'];
                        $response['data']['region_id'] = $this->helperDataScheme->getRegionId(
                            $postDetailsArr[0]['State']
                        );
                    } else {
                        $response['status'] = false;
                        $response['message'] = '';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = '';
                }
            } else {
                $response['status'] = false;
            }
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = 'There is error in processing your request. Please try later';
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
