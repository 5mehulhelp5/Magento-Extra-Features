<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\Postloginhome\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Product\AttributeValueProvider;

/**
 * Wishlist Remove Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveWishlistItem extends \Magento\Wishlist\Controller\AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var AttributeValueProvider
     */
    private $attributeValueProvider;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param WishlistProviderInterface $wishlistProvider
     * @param AttributeValueProvider|null $attributeValueProvider
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        WishlistProviderInterface $wishlistProvider,
        AttributeValueProvider $attributeValueProvider = null
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->attributeValueProvider = $attributeValueProvider
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeValueProvider::class);
        parent::__construct($context);
    }

    /**
     * Remove item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $id = (int)$this->getRequest()->getParam('item');
        /** @var Item $item */
        $item = $this->_objectManager->create(Item::class)->load($id);
        if (!$item->getId()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }
        try {
            $item->delete();
            $wishlist->save();
            $productName = $this->attributeValueProvider->getRawAttributeValue($item->getProductId(), 'name');
            $result->setData(['deleted' => 1]);
            $this->messageManager->addComplexSuccessMessage(
                'removeWishlistItemSuccessMessage',
                [
                    'product_name' => $productName,
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData(['deleted' => 0]);
            $this->messageManager->addErrorMessage(
                __('We can\'t delete the item from Wish List right now because of an error: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $result->setData(['deleted' => 0]);
            $this->messageManager->addErrorMessage(__('We can\'t delete the item from the Wish List right now.'));
        }
        return $result;
    }
}
