<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ProductReCommendations\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Codilar\ProductReCommendations\Model\ProductRecommendationData;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultInterface;

class Recommendations implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ProductRecommendationData
     */
    private ProductRecommendationData $productRecommendationSave;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * Recommendations constructor.
     * @param ProductRecommendationData $productRecommendationSave
     * @param RequestInterface $request
     * @param ResourceConnection $resourceConnection
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        ProductRecommendationData $productRecommendationSave,
        RequestInterface $request,
        ResourceConnection $resourceConnection,
        JsonFactory $resultJsonFactory
    )
    {
        $this->request = $request;
        $this->productRecommendationSave = $productRecommendationSave;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Save the Product Recommendations Products
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $productIds = $this->request->getParam('productIds');
        $result = $this->resultJsonFactory->create();
        foreach ($productIds as $productId) {
            $this->productRecommendationSave->ProductRecommendationsSave($productId);
            $result->setData(['response' => true]);
        }
        return $result;
    }
}
