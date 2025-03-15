<?php

namespace Casio\LotterySale\Plugin;

class AfterProductLoad
{
    /**
     * @var \Casio\LotterySale\Api\LotterySalesRepositoryInterface
     */
    private \Casio\LotterySale\Api\LotterySalesRepositoryInterface $lotterySalesRepository;

    /**
     * @param \Casio\LotterySale\Api\LotterySalesRepositoryInterface $lotterySalesRepository
     */
    public function __construct(
        \Casio\LotterySale\Api\LotterySalesRepositoryInterface $lotterySalesRepository
    ) {
        $this->lotterySalesRepository = $lotterySalesRepository;
    }

    /**
     * Add lottery sales information to the product's extension attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterLoad(\Magento\Catalog\Model\Product $product)
    {
        $productExtension = $product->getExtensionAttributes();
        $lotterySales = $this->lotterySalesRepository->getByProductId($product->getId());
        if ($lotterySales) {
            $productExtension->setCasioLotterySales($lotterySales);
            $product->setExtensionAttributes($productExtension);
        }
        return $product;
    }
}
