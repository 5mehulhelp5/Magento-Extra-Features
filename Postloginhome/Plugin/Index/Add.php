<?php

/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\Postloginhome\Plugin\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Controller\Index\Add as Subject;

class Add extends Subject
{
    /**
     * Add constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param RedirectInterface|null $redirect
     * @param UrlInterface|null $urlBuilder
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        RedirectInterface $redirect = null,
        UrlInterface $urlBuilder = null
    ) {
        parent::__construct($context, $customerSession, $wishlistProvider, $productRepository, $formKeyValidator, $redirect, $urlBuilder);
    }

    /**
     * Redirect to Product Detail Page if qty is passed and it is not of incremental
     *
     * @param Subject $subject
     * @param callable $proceed
     * @return Json|Redirect|ResultInterface|Layout
     * @throws NoSuchEntityException
     */
    public function aroundExecute(Subject $subject, callable $proceed)
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/');
        }

        if ($this->getRequest()->getParam('login')) {
            $productId = $this->getRequest()->getParam('product');
            $product = $this->productRepository->getById($productId);
            return $this->resultRedirectFactory->create()->setUrl($product->getProductUrl());
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        $session = $this->_customerSession;

        $requestParams = $this->getRequest()->getParams();
        $qty = $this->getRequest()->getParam('qty');

        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }

        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;


        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $product = $this->productRepository->getById($productId);
            $qtyIncrements = $product->getExtensionAttributes()->getStockItem()->getQtyIncrements();
            $productUrl = $product->getProductUrl();
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if ($qty && $qtyIncrements!='0' && $qty % $qtyIncrements) {
            return $this->resultRedirectFactory->create()->setUrl($productUrl);
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $buyRequest = new \Magento\Framework\DataObject($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                throw new LocalizedException(__($result));
            }
            if ($wishlist->isObjectNew()) {
                $wishlist->save();
            }
            $this->_eventManager->dispatch(
                'wishlist_add_product',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_redirect->getRefererUrl();
            }

            $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();

            $this->messageManager->addComplexSuccessMessage(
                'addProductSuccessMessage',
                [
                    'product_name' => $product->getName(),
                    'referer' => $referer
                ]
            );
            // phpcs:disable Magento2.Exceptions.ThrowCatch
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
        }

        if ($this->getRequest()->isAjax()) {
            $url = $this->urlBuilder->getUrl('*', $this->redirect->updatePathParams(['wishlist_id' => $wishlist->getId()]));
            /** @var Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['backUrl' => $url]);

            return $resultJson;
        }


        $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);

        return $resultRedirect;
    }
}
