<?php

namespace Casio\LotterySale\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;

class LotteryUrl implements ResolverInterface
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * LotteryUrl constructor.
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return \Magento\Framework\GraphQl\Query\Resolver\Value|mixed|string
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $product = $value['model'];

        return $this->urlBuilder->getUrl('checkout/cart/drawNotice', ['sku' => $product->getSku()]);
    }
}
