<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ProductReCommendations\Model;

use Magento\Company\Model\CompanyContext;
use Codilar\ProductReCommendations\Model\ProductRecommendations as RecommendationModel;
use Codilar\ProductReCommendations\Model\ProductRecommendationsFactory as RecommendationModelFactory;
use Codilar\ProductReCommendations\Model\ResourceModel\ProductRecommendations as RecommendationResourceModel;
use Codilar\ProductReCommendations\Model\ResourceModel\ProductRecommendations\Collection as RecommendationCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;

class ProductRecommendationData
{
    /**
     * @var CompanyContext
     */
    private CompanyContext $companyContext;
    /**
     * @var ProductRecommendationsFactory
     */
    private ProductRecommendationsFactory $productRecommendationsFactory;
    /**
     * @var RecommendationResourceModel
     */
    private RecommendationResourceModel $recommendationResourceModel;
    /**
     * @var RecommendationCollection
     */
    private RecommendationCollection $recommendationCollection;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var ProductRecommendations
     */
    private ProductRecommendations $productRecommendations;
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * ProductRecommendationData constructor.
     * @param CompanyContext $companyContext
     * @param ProductRecommendationsFactory $productRecommendationsFactory
     * @param RecommendationResourceModel $recommendationResourceModel
     * @param RecommendationCollection $recommendationCollection
     * @param ProductRecommendations $productRecommendations
     * @param ResourceConnection $resourceConnection
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        CompanyContext $companyContext,
        RecommendationModelFactory $productRecommendationsFactory,
        RecommendationResourceModel $recommendationResourceModel,
        RecommendationCollection $recommendationCollection,
        RecommendationModel $productRecommendations,
        ResourceConnection $resourceConnection,
        JsonFactory $resultJsonFactory
    )
    {
        $this->companyContext = $companyContext;
        $this->productRecommendationsFactory = $productRecommendationsFactory;
        $this->recommendationResourceModel = $recommendationResourceModel;
        $this->recommendationCollection = $recommendationCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRecommendations = $productRecommendations;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Save the product recommendations data to custom table
     * @param $productId
     * @return Json
     * @throws AlreadyExistsException
     */
    public function ProductRecommendationsSave($productId)
    {
        $result = $this->resultJsonFactory->create();
        $customerId = $this->companyContext->getCustomerId();
        $recommendationViewedModel = $this->productRecommendationsFactory->create();
        $recommendationCollection = $this->recommendationCollection
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('customer_id', $customerId);
        if (!count($recommendationCollection)) {
            if ($customerId) {
                $recommendationViewedModel->setData([
                    'product_id' => $productId,
                    'customer_id' => $this->companyContext->getCustomerId()
                ]);
                $this->recommendationResourceModel->save($recommendationViewedModel);
                $result->setData(['response' => true]);
            }
        }
        return $result;
    }

    /**
     * delete the Product Recommendations data from custom table
     *
     * @return Json
     */
    public function ProductRecommendationDelete()
    {
        $result = $this->resultJsonFactory->create();
        $customerId = $this->companyContext->getCustomerId();
        $table = $this->resourceConnection->getTableName('product_recommendations');
        $this->resourceConnection->getConnection()->delete($table,["customer_id = $customerId"]);
        $result->setData(['response' => true]);
        return $result;
    }

}
