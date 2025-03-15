<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\Data;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class LotterySales extends AbstractExtensibleObject implements LotterySalesInterface
{
    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Casio\LotterySale\Api\Data\LotterySalesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return mixed|null
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * @param $websiteId
     * @return LotterySales
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @return int|mixed|null
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return LotterySales
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return string|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return LotterySales
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|mixed|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return LotterySales
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return mixed|string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * @param string $sku
     * @return LotterySales
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @return mixed|string|null
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return LotterySales
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return mixed|string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * @param string $title
     * @return LotterySales
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return mixed|string|null
     */
    public function getLotteryDate()
    {
        return $this->_get(self::LOTTERY_DATE);
    }

    /**
     * @param string $lotteryDate
     * @return LotterySales
     */
    public function setLotteryDate($lotteryDate)
    {
        return $this->setData(self::LOTTERY_DATE, $lotteryDate);
    }

    /**
     * @return mixed|string|null
     */
    public function getApplicationDateFrom()
    {
        return $this->_get(self::APPLICATION_DATE_FROM);
    }

    /**
     * @param string $applicationDateFrom
     * @return LotterySales
     */
    public function setApplicationDateFrom($applicationDateFrom)
    {
        return $this->setData(self::APPLICATION_DATE_FROM, $applicationDateFrom);
    }

    /**
     * @return mixed|string|null
     */
    public function getApplicationDateTo()
    {
        return $this->_get(self::APPLICATION_DATE_TO);
    }

    /**
     * @param string $applicationDateTo
     * @return LotterySales
     */
    public function setApplicationDateTo($applicationDateTo)
    {
        return $this->setData(self::APPLICATION_DATE_TO, $applicationDateTo);
    }

    /**
     * @return mixed|string|null
     */
    public function getPurchaseDeadline()
    {
        return $this->_get(self::PURCHASE_DEADLINE);
    }

    /**
     * @param string $purchaseDeadline
     * @return LotterySales
     */
    public function setPurchaseDeadline($purchaseDeadline)
    {
        return $this->setData(self::PURCHASE_DEADLINE, $purchaseDeadline);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Casio\LotterySale\Api\Data\LotterySalesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Casio\LotterySale\Api\Data\LotterySalesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Casio\LotterySale\Api\Data\LotterySalesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
