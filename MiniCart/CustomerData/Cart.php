<?php

namespace Codilar\MiniCart\CustomerData;

class Cart extends \Magento\Checkout\CustomerData\Cart
{
    /**
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
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
            $individualItem['regular_price'] = $this->checkoutHelper->formatPrice(($item->getProduct()->getPrice()));
            $individualItem['final_price'] = $this->checkoutHelper->formatPrice($item->getProduct()->getFinalPrice());
            $items[] = $individualItem;
        }
        return $items;
    }
}
