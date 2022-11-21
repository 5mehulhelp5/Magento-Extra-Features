<?php

namespace Codilar\MiniCart\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;

class ConfigProviderPlugin
{
    /**
     *@var checkoutSession
     */
    protected $checkoutSession;
    /**
     * @var CheckoutHelper
     */
    private $checkouthelper;

    /**
     *Constructor
     * @param CheckoutSession $checkoutSession
     * @param CheckoutHelper $checkouthelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CheckoutHelper $checkouthelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkouthelper = $checkouthelper;
    }
    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result): array
    {
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $result['quoteItemData'][$index]['regular_price'] = $this->checkouthelper->formatPrice($quoteItem->getProduct()->getPrice());
            $result['quoteItemData'][$index]['final_price'] = $this->checkouthelper->formatPrice($quoteItem->getProduct()->getFinalPrice());
        }
        return $result;
    }
}
