<?php /** @noinspection DuplicatedCode */

/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\Postloginhome\Block\Index;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Catalog\Helper\ImageFactory as ProductImageHelper;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Catalog\Model\ProductFactory;
use Magento\MultipleWishlist\Helper\Data as MultipleWishlistHelper;
use Codilar\EgcSupply\Helper\Product as CodilarProductHelper;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;

class MyWishlist extends Template
{
    /**
     * @var UserContextInterface
     */
    protected UserContextInterface $userContext;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManagerInterface;

    /**
     * @var Data
     */
    private Data $priceHelper;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $_wishlistCollectionFactory;

    /**
     * @var ProductImageHelper
     */
    protected ProductImageHelper $productImageHelper;

    /**
     * @var AppEmulation
     */
    protected AppEmulation $appEmulation;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productLoad;

    /**
     * @var MultipleWishlistHelper
     */
    protected MultipleWishlistHelper $_wishlistData;

    /**
     * @var CodilarProductHelper
     */
    protected CodilarProductHelper $_productMinQty;
    private ListProduct $listProductBlock;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var FormKey
     */
    private FormKey $formKey;

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param StoreManagerInterface $storeManagerInterface
     * @param Data $priceHelper
     * @param CollectionFactory $wishlistCollectionFactory
     * @param AppEmulation $appEmulation
     * @param ProductImageHelper $productImageHelper
     * @param ProductFactory $productLoad
     * @param MultipleWishlistHelper $wishlistData
     * @param CodilarProductHelper $productMinQty
     * @param ListProduct $listProductBlock
     * @param UrlInterface $url
     * @param FormKey $formKey
     * @param array $data [optional]
     */

    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        StoreManagerInterface $storeManagerInterface,
        Data $priceHelper,
        CollectionFactory $wishlistCollectionFactory,
        AppEmulation $appEmulation,
        ProductImageHelper $productImageHelper,
        ProductFactory $productLoad,
        MultipleWishlistHelper $wishlistData,
        CodilarProductHelper $productMinQty,
        ListProduct $listProductBlock,
        UrlInterface $url,
        FormKey $formKey,
        array $data = []
    ) {
        $this->userContext = $userContext;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->priceHelper = $priceHelper;
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productLoad = $productLoad;
        $this->appEmulation = $appEmulation;
        $this->productImageHelper = $productImageHelper;
        $this->_wishlistData = $wishlistData;
        $this->_productMinQty = $productMinQty;
        parent::__construct($context, $data);
        $this->listProductBlock = $listProductBlock;
        $this->url = $url;
        $this->formKey = $formKey;
    }

    /**
     * Get baseurl
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl();
    }

    /**
     * Get formatted price
     *
     * @param $price
     * @return string
     */
    public function getFormattedPrice($price): string
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        if($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        if($this->isCustomerLoggedIn()) {
            return $this->userContext->getUserId();
        } else {
            return null;
        }
    }

    /**
     * Get wishlist collection
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCustomerWishlist($wishlistId): array
    {
        $customerId = $this->getCustomerId();
        $wishlistData = [];
        if ($customerId) {
            $collection = $this->_wishlistCollectionFactory->create()->addCustomerIdFilter($customerId);
            $collection = $collection->addFieldToFilter('wishlist.wishlist_id',['eq' => $wishlistId]);
            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();
                $productLoad = $this->_productLoad->create();
                $currentProduct = $productLoad->load($productInfo['entity_id']);
                $imageURL = $this->getImageUrl($currentProduct, 'product_base_image');
                $productInfo['small_image'] = $imageURL;
                $productInfo['thumbnail'] = $imageURL;
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id"      => $item->getWishlistId(),
                    "product_id"       => $item->getProductId(),
                    "store_id"         => $item->getStoreId(),
                    "added_at"         => $item->getAddedAt(),
                    "final_price"      => $currentProduct->getFinalPrice(),
                    "regular_price"    => $currentProduct->getPrice(),
                    "description"      => $item->getDescription(),
                    "qty"              => round($item->getQty()),
                    "product"          => $productInfo
                ];
                $wishlistData[] = $data;
            }
        }
        return $wishlistData;
    }

    /**
     * Helper function that provides full cache image url
     *
     * @param $product
     * @param string $imageType
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl($product, string $imageType = ''): string
    {
        $storeId = $this->storeManagerInterface->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $imageUrl = $this->productImageHelper->create()->init($product, $imageType)->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();
        return $imageUrl;
    }

    /**
     * Retrieve wishlist collection
     *
     * @return Collection
     */
    public function getWishlists()
    {
        return $this->_wishlistData->getCustomerWishlists($this->getCustomerId());
    }

    /**
     * Retrieve default wishlist for current customer
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getDefaultWishlist()
    {
        return $this->_wishlistData->getDefaultWishlist();
    }

    /**
     * @return int
     */
    public function getMinQtyIncrements($productId)
    {
        return $this->_productMinQty->getMinQtyIncrements($productId);
    }

    /**
     * Return the post params to add to cart
     *
     * @param $productId
     * @return array
     */
    public function getAddToCartPostParams($productId)
    {
        $product = $this->_productLoad->create()->load($productId);
        return $this->listProductBlock->getAddToCartPostParams($product);
    }

    /**
     * Return the redirection url after to wishlist
     *
     * @param $wishlistId
     * @return string
     */
    public function getRedirectUrl($wishlistId)
    {
        return $this->getUrl('wishlist/index/index',['wishlist_id'=>$wishlistId]);
    }

    /**
     * Get form key
     *
     * @return string
     * @since 100.2.0
     */
    public function getFormkey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Build wishlist edit url
     *
     * @param int $wishlistId
     * @return string
     */
    public function getEditUrl($wishlistId)
    {
        return $this->getUrl('wishlist/index/editwishlist', ['wishlist_id' => $wishlistId]);
    }
}
