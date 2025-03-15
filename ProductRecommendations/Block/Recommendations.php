<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ProductRecommendations\Block;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Company\Model\CompanyContext;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Codilar\EgcSupply\Helper\Product;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Authorization\Model\UserContextInterface;

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
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * Recommendations constructor.
     * @param Context $context
     * @param CompanyContext $companyContext
     * @param ProductCollectionFactory $productCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param Product $productHelper
     * @param ListProduct $listProduct
     * @param OptionsData $optionsData
     * @param WishlistHelper $wishlistHelper
     * @param UserContextInterface $userContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        CompanyContext $companyContext,
        ProductCollectionFactory $productCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        Product $productHelper,
        ListProduct $listProduct,
        OptionsData $optionsData,
        WishlistHelper $wishlistHelper,
        UserContextInterface $userContext,
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
        $this->productCollectionFactory = $productCollectionFactory;
        $this->userContext = $userContext;
    }


    /**
     * Return the Product Collection
     *
     * @return ProductSearchResultsInterface
     */
    public function getProductCollection()
    {
        $productIds = $this->_request->getParam('productIds');
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'entity_id', $productIds, 'in'
        )->create();
        return $this->productRepository->getList($searchCriteria);
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
     * Return the Slider Title from Magento Product Recommendations
     *
     * @return string
     */
    public function getSliderTitle()
    {
        return $this->_request->getParam('sliderLabel');
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

    /**
     * Checks whether customer is Logged in or not
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            return true;
        } else {
            return false;
        }
    }
}
