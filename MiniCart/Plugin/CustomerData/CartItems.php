<?php

namespace Codilar\MiniCart\Plugin\CustomerData;

use Magento\Checkout\CustomerData\Cart;

class CartItems extends Cart
{
    /**
     * @param Cart $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetSectionData(Cart $subject, callable $proceed): array
    {
        $totals = $this->getQuote()->getTotals();
        $subtotalAmount = $totals['subtotal']->getValue();
        return [
            'summary_count' => $this->getSummaryCount(),
            'subtotalAmount' => $subtotalAmount,
            'subtotal' => isset($totals['subtotal'])
                ? $this->checkoutHelper->formatPrice($subtotalAmount)
                : 0,
            'possible_onepage_checkout' => $this->isPossibleOnepageCheckout(),
            'items' => $this->getRecentItems(),
            'extra_actions' => $this->layout->createBlock(\Magento\Catalog\Block\ShortcutButtons::class)->toHtml(),
            'isGuestCheckoutAllowed' => $this->isGuestCheckoutAllowed(),
            'website_id' => $this->getQuote()->getStore()->getWebsiteId(),
            'storeId' => $this->getQuote()->getStore()->getStoreId()
        ];
    }
    public function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (isset($products[$product->getId()])) {
                    $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($urlDataObject);
                }
            }
            $individualItem = $this->itemPoolInterface->getItemData($item);
            $individualItem['regular_price'] = $item->getProduct()->getPrice();
            $items[] = $individualItem;
        }
        return $items;
    }
}
