<?php

namespace Casio\LotterySale\Plugin;

use Casio\LotterySale\Api\LotterySalesRepositoryInterface;
use Casio\LotterySale\Model\LotterySalesRepository;

class ProductRepositoryPlugin
{
    /**
     * @var LotterySalesRepositoryInterface
     */
    private LotterySalesRepositoryInterface $lotterySalesRepository;

    /**
     * ProductRepositoryPlugin constructor.
     * @param LotterySalesRepository $lotterySalesRepository
     */
    public function __construct(
        LotterySalesRepository $lotterySalesRepository
    ) {
        $this->lotterySalesRepository = $lotterySalesRepository;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    ) {
        $lotterySalesData = $this->lotterySalesRepository->getByProductId($entity->getId());
        if ($lotterySalesData) {
            $extensionAttributes = $entity->getExtensionAttributes();
            /** get current extension attributes from entity **/
            $extensionAttributes->setCasioLotterySales($lotterySalesData);
            $entity->setExtensionAttributes($extensionAttributes);
        }
        return $entity;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface {
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
            $lotterySalesData = $this->lotterySalesRepository->getByProductId($entity->getId());
            if ($lotterySalesData) {
                $extensionAttributes = $entity->getExtensionAttributes();
                $extensionAttributes->setCasioLotterySales($lotterySalesData);
                $entity->setExtensionAttributes($extensionAttributes);
            }
            $products[$entity->getId()] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $result
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $result, /** result from the save call **/
        \Magento\Catalog\Api\Data\ProductInterface $entity  /** original parameter to the call **/
        /** other parameter not required **/
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        $lotterySalesData = $extensionAttributes->getCasioLotterySales();
        if ($lotterySalesData) {
            $this->lotterySalesRepository->save($lotterySalesData);
            $resultAttributes = $result->getExtentionAttributes();
            $resultAttributes->setCasioLotterySales($lotterySalesData);
            $result->setExtensionAttributes($resultAttributes);
        }

        return $result;
    }
}
