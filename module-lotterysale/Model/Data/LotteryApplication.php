<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\Data;

use Casio\LotterySale\Api\Data\LotteryApplicationInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class LotteryApplication extends AbstractExtensibleObject implements LotteryApplicationInterface
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
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return mixed|null
     */
    public function getLotterySalesId()
    {
        return $this->_get(self::LOTTERY_SALES_ID);
    }

    /**
     * @param $lotterySalesId
     * @return LotteryApplication
     */
    public function setLotterySalesId($lotterySalesId)
    {
        return $this->setData(self::LOTTERY_SALES_ID, $lotterySalesId);
    }

    /**
     * @return mixed|null
     */
    public function getUserId()
    {
        return $this->_get(self::USER_ID);
    }

    /**
     * @param $userId
     * @return LotteryApplication
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * @return mixed|null
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * @param $email
     * @return LotteryApplication
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @return mixed|null
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * @param $firstname
     * @return LotteryApplication
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * @return mixed|null
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * @param $lastname
     * @return LotteryApplication
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * @return mixed|null
     */
    public function getFirstnamekana()
    {
        return $this->_get(self::FIRSTNAME_KANA);
    }

    /**
     * @param $firstnamekana
     * @return LotteryApplication
     */
    public function setFirstnamekana($firstnamekana)
    {
        return $this->setData(self::FIRSTNAME_KANA, $firstnamekana);
    }

    /**
     * @return mixed|null
     */
    public function getLastnamekana()
    {
        return $this->_get(self::LASTNAME_KANA);
    }

    /**
     * @param $lastnamekana
     * @return LotteryApplication
     */
    public function setLastnamekana($lastnamekana)
    {
        return $this->setData(self::LASTNAME_KANA, $lastnamekana);
    }

    /**
     * @return mixed|null
     */
    public function getTelephone()
    {
        return $this->_get(self::TELEPHONE);
    }

    /**
     * @param $telephone
     * @return LotteryApplication
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * @return mixed|null
     */
    public function getPostcode()
    {
        return $this->_get(self::POSTCODE);
    }

    /**
     * @param $postcode
     * @return LotteryApplication
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @return mixed|null
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * @param $regionId
     * @return LotteryApplication
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @return mixed|null
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * @param $region
     * @return LotteryApplication
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * @return mixed|null
     */
    public function getCity()
    {
        return $this->_get(self::CITY);
    }

    /**
     * @param $city
     * @return LotteryApplication
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @return mixed|null
     */
    public function getStreet()
    {
        return $this->_get(self::STREET);
    }

    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * @return mixed|null
     */
    public function getBuilding()
    {
        return $this->_get(self::BUILDING);
    }

    /**
     * @param $building
     * @return LotteryApplication
     */
    public function setBuilding($building)
    {
        return $this->setData(self::BUILDING, $building);
    }

    /**
     * @return mixed|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @param $status
     * @return LotteryApplication
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return mixed|null
     */
    public function getOrdered()
    {
        return $this->_get(self::ORDERED);
    }

    /**
     * @param $ordered
     * @return LotteryApplication
     */
    public function setOrdered($ordered)
    {
        return $this->setData(self::ORDERED, $ordered);
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
     * @return LotteryApplication
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @return mixed|null
     */
    public function getLotterySalesCode()
    {
        return $this->_get(self::LOTTERY_SALES_CODE);
    }

    /**
     * @param $lotterySalesCode
     * @return LotteryApplication
     */
    public function setLotterySalesCode($lotterySalesCode)
    {
        return $this->setData(self::LOTTERY_SALES_CODE, $lotterySalesCode);
    }

    /**
     * @return mixed|string|null
     */
    public function getPurchaseDeadline()
    {
        return $this->_get(LotterySales::PURCHASE_DEADLINE);
    }

    /**
     * @param string $purchaseDeadline
     * @return LotteryApplication
     */
    public function setPurchaseDeadline($purchaseDeadline)
    {
        return $this->setData(LotterySales::PURCHASE_DEADLINE, $purchaseDeadline);
    }

    /**
     * @return mixed|string|null
     */
    public function getLotteryDate()
    {
        return $this->_get(LotterySales::LOTTERY_DATE);
    }

    /**
     * @param string $lotteryDate
     * @return LotteryApplication
     */
    public function setLotteryDate($lotteryDate)
    {
        return $this->setData(LotterySales::LOTTERY_DATE, $lotteryDate);
    }

    /**
     * @return mixed|string|null
     */
    public function getSku()
    {
        return $this->_get(LotterySales::SKU);
    }

    /**
     * @param string $sku
     * @return LotteryApplication
     */
    public function setSku($sku)
    {
        return $this->setData(LotterySales::SKU, $sku);
    }

    /**
     * @return int|mixed|null
     */
    public function getProductId()
    {
        return $this->_get(LotterySales::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return LotteryApplication
     */
    public function setProductId($productId)
    {
        return $this->setData(LotterySales::PRODUCT_ID, $productId);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(LotteryApplication::CREATED_AT);
    }

    /**
     * @param string $timestamp
     * @return $this
     */
    public function setCreatedAt($timestamp)
    {
        return $this->setData(LotteryApplication::CREATED_AT, $timestamp);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Casio\LotterySale\Api\Data\LotteryApplicationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
