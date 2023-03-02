<?php

/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\Postloginhome\Plugin;

use Exception;
use InvalidArgumentException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Configuration as StockConfiguration;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\StockStateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Item;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as ResourceWishlist;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Wishlist\Model\Wishlist as Subject;

class Wishlist extends Subject
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var StockConfiguration
     */
    private $stockConfiguration;
    /**
     * @var Redirect
     */
    public $resultRedirect;

    /**
     * Wishlist constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param Data $wishlistData
     * @param ResourceWishlist $resource
     * @param Collection $resourceCollection
     * @param StoreManagerInterface $storeManager
     * @param DateTime\DateTime $date
     * @param ItemFactory $wishlistItemFactory
     * @param CollectionFactory $wishlistCollectionFactory
     * @param ProductFactory $productFactory
     * @param Random $mathRandom
     * @param DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     * @param Redirect $resultRedirect
     * @param bool $useCurrentWebsite
     * @param array $data
     * @param Json|null $serializer
     * @param StockRegistry|null $stockRegistry
     * @param ScopeConfigInterface|null $scopeConfig
     * @param StockConfiguration|null $stockConfiguration
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Catalog\Helper\Product $catalogProduct,
        Data $wishlistData,
        ResourceWishlist $resource,
        Collection $resourceCollection,
        StoreManagerInterface $storeManager,
        DateTime\DateTime $date,
        ItemFactory $wishlistItemFactory,
        CollectionFactory $wishlistCollectionFactory,
        ProductFactory $productFactory,
        Random $mathRandom,
        DateTime $dateTime,
        ProductRepositoryInterface $productRepository,
        Redirect $resultRedirect,
        $useCurrentWebsite = true, array $data = [],
        Json $serializer = null,
        StockRegistry $stockRegistry = null,
        ScopeConfigInterface $scopeConfig = null,
        ?StockConfiguration $stockConfiguration = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $catalogProduct,
            $wishlistData,
            $resource,
            $resourceCollection,
            $storeManager,
            $date,
            $wishlistItemFactory,
            $wishlistCollectionFactory,
            $productFactory,
            $mathRandom,
            $dateTime,
            $productRepository,
            $useCurrentWebsite,
            $data,
            $serializer,
            $stockRegistry,
            $scopeConfig,
            $stockConfiguration
        );

        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->stockConfiguration = $stockConfiguration
            ?: ObjectManager::getInstance()->get(StockConfiguration::class);
        $this->resultRedirect = $resultRedirect;
    }

    /**
     * Plugin to set the Qty if qty parameter is not passed
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param $product
     * @param null $buyRequest
     * @param false $forciblySetQty
     * @return Item|string|null
     * @throws NoSuchEntityException
     */
    public function aroundAddNewItem(Subject $subject, callable $proceed, $product, $buyRequest = null, $forciblySetQty = false)
    {
        if ($product instanceof Product) {
            $productId = $product->getId();
            // Maybe force some store by wishlist internal properties
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $product->getStoreId();
        } else {
            $productId = (int)$product;
            if (isset($buyRequest) && $buyRequest->getStoreId()) {
                $storeId = $buyRequest->getStoreId();
            } else {
                $storeId = $subject->_storeManager->getStore()->getId();
            }
        }

        try {
            /** @var Product $product */
            $product = $subject->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Cannot specify product.'));
        }

        if (!$this->stockConfiguration->isShowOutOfStock($storeId) && !$product->getIsSalable()) {
            throw new StockStateException(__('Cannot add product without stock to wishlist.'));
        }
        if ($buyRequest instanceof DataObject) {
            $_buyRequest = $buyRequest;
        } elseif (is_string($buyRequest)) {
            $isInvalidItemConfiguration = false;
            $buyRequestData = [];
            try {
                $buyRequestData = $this->serializer->unserialize($buyRequest);
                if (!is_array($buyRequestData)) {
                    $isInvalidItemConfiguration = true;
                }
            } catch (Exception $exception) {
                $isInvalidItemConfiguration = true;
            }
            if ($isInvalidItemConfiguration) {
                throw new InvalidArgumentException('Invalid wishlist item configuration.');
            }
            $_buyRequest = new DataObject($buyRequestData);
        } elseif (is_array($buyRequest)) {
            $_buyRequest = new DataObject($buyRequest);
        } else {
            $_buyRequest = new DataObject();
        }
        if ($_buyRequest->getData('action') !== 'updateItem') {
            $_buyRequest->setData('action', 'add');
        }

        /* @var $product Product */
        $cartCandidates = $product->getTypeInstance()->processConfiguration($_buyRequest, clone $product);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $errors = [];
        $items = [];
        $item = null;

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $candidate->setWishlistStoreId($storeId);
            $qtyIncrements = $product->getExtensionAttributes()->getStockItem()->getQtyIncrements();

            // if qty is not passed then incremental qty is set or else qty is set to to 1
            $qty = $qtyIncrements!='0' ? $qtyIncrements :1;
            $qty = $candidate->getQty() ? $candidate->getQty() : $qty;

            $item = $subject->_addCatalogProduct($candidate, $qty, $forciblySetQty);
            $items[] = $item;

            // Collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }

        $subject->_eventManager->dispatch('wishlist_product_add_after', ['items' => $items]);

        return $item;
    }
}
