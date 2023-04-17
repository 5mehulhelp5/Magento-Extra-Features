<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ExtendedShopByBrand\Block\Brand;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Codilar\EgcSupply\Helper\Product;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Meetanshi\ShopbyBrand\Block\Frontend\Brand\Featured;
use Meetanshi\ShopbyBrand\Helper\Data as BrandHelper;
use Meetanshi\ShopbyBrand\Model\BrandFactory;
use Meetanshi\ShopbyBrand\Model\CategoryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BrandSlider extends Featured
{
    const BRAND_ATTRIBUTE_CODE = 'shopbybrand/general/brand_attribute';
    /**
     * @var Context
     */
    private Context $context;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
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
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * BrandSlider constructor.
     * @param Context $context
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param BrandFactory $brandFactory
     * @param Registry $coreRegistry
     * @param BlockFactory $blockFactory
     * @param AdapterFactory $imageFactory
     * @param BrandHelper $helper
     * @param ResourceConnection $connection
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Product $productHelper
     * @param ListProduct $listProduct
     * @param OptionsData $optionsData
     * @param WishlistHelper $wishlistHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        BrandFactory $brandFactory,
        Registry $coreRegistry,
        BlockFactory $blockFactory,
        AdapterFactory $imageFactory,
        BrandHelper $helper,
        ResourceConnection $connection,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Product $productHelper,
        ListProduct $listProduct,
        OptionsData $optionsData,
        WishlistHelper $wishlistHelper,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $productCollectionFactory, $categoryFactory, $brandFactory, $coreRegistry, $blockFactory, $imageFactory, $helper, $connection, $productFactory, $productRepository, $data);
        $this->context = $context;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productHelper = $productHelper;
        $this->listProduct = $listProduct;
        $this->optionsData = $optionsData;
        $this->wishlistHelper = $wishlistHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return the Product Collection
     *
     * @return ProductSearchResultsInterface
     */
    public function getProductCollection()
    {
        $brandAttributeCode = $this->scopeConfig->getValue(self::BRAND_ATTRIBUTE_CODE,ScopeInterface::SCOPE_STORE);
        $brandId = $this->_request->getParam('brandId');
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            $brandAttributeCode, $brandId, 'eq'
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
     * Return the Slider Title from Featured By Brand Slider
     *
     * @return string
     */
    public function getSliderTitle()
    {
        return '';
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
