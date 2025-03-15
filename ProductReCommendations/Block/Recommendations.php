<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ProductReCommendations\Block;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Codilar\ProductReCommendations\Model\ResourceModel\ProductRecommendations\CollectionFactory as ProductRecommendationCollectionFactory;
use Magento\Company\Model\CompanyContext;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Codilar\EgcSupply\Helper\Product;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class Recommendations extends Template
{
    /**
     * @var CompanyContext
     */
    private CompanyContext $companyContext;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var Product
     */
    private Product $productHelper;
    /**
     * @var ListProduct
     */
    private ListProduct $listProduct;
    /**
     * @var OptionsData
     */
    private OptionsData $optionsData;
    /**
     * @var WishlistHelper
     */
    private WishlistHelper $wishlistHelper;
    /**
     * @var ProductRecommendationCollectionFactory
     */
    private ProductRecommendationCollectionFactory $recommendationCollectionFactory;

    /**
     * Recommendations constructor.
     * @param Context $context
     * @param CompanyContext $companyContext
     * @param ProductRecommendationCollectionFactory $recommendationCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param Product $productHelper
     * @param ListProduct $listProduct
     * @param OptionsData $optionsData
     * @param WishlistHelper $wishlistHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CompanyContext $companyContext,
        ProductRecommendationCollectionFactory $recommendationCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        Product $productHelper,
        ListProduct $listProduct,
        OptionsData $optionsData,
        WishlistHelper $wishlistHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->companyContext = $companyContext;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->listProduct = $listProduct;
        $this->optionsData = $optionsData;
        $this->wishlistHelper = $wishlistHelper;
        $this->recommendationCollectionFactory = $recommendationCollectionFactory;
    }

    /**
     * Return the  Product Recommendation Collection
     *
     * @return false|ProductSearchResultsInterface
     */
    public function getProductCollection()
    {
        $collection = $this->recommendationCollectionFactory->create();
        if ($this->companyContext->getCustomerId()){
            $products= $collection->addFieldToSelect('product_id')
                ->addFieldToFilter('customer_id',$this->companyContext->getCustomerId())
                ->setOrder('entity_id','DESC')->setPageSize(15)->toArray();
            $productIds = $this->getProductIds($products);
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'entity_id', $productIds, 'in'
            )->create();
            return $this->productRepository->getList($searchCriteria);
        } else {
            return false;
        }
    }

    /**
     * Return the Product Ids
     *
     * @param $products
     * @return array
     */
    public function getProductIds($products)
    {
        $productIds = [];
        for ($i=0;$i<count($products['items']);$i++){
            $productIds[$i] = $products['items'][$i]['product_id'];
        }
        return $productIds;
    }

    /**
     * Return the Product Incremental quantity
     *
     * @param $productId
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getIncrementQty($productId)
    {
        if ($this->productHelper->getMinQtyIncrements($productId)){
            return $this->productHelper->getMinQtyIncrements($productId);
        } else{
            return false;
        }
    }

    /**
     * Return the AddToCart parameters
     *
     * @param $product
     * @return array
     */
    public function getAddToCartPostParams($product)
    {
        return $this->listProduct->getAddToCartPostParams($product);
    }

    /**
     * Return the AddToWishlist Parameters
     *
     * @param $product
     * @return string
     */
    public function getAddToWishlistParams($product)
    {
        return $this->wishlistHelper->getAddParams($product);
    }

    /**
     * Return the Product OptionData
     *
     * @param $product
     * @return array
     */
    public function getOptionsData($product)
    {
        return $this->optionsData->getOptionsData($product);
    }

    /**
     * Return the Base Url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getBaseUrl();
    }

    /**
     * Return the Slider Title
     *
     * @return string
     */
    public function getSliderTitle()
    {
        if ($this->companyContext->getCustomerId()) {
            return 'Product Recommendations';
        }
    }

    /**
     * Return the count of available products
     *
     * @return int
     */
    public function isProductsAvailable()
    {
        return count($this->getProductCollection()->getItems());
    }
}
