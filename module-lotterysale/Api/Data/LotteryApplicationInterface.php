<?php
declare(strict_types=1);

namespace Casio\LotterySale\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface LotteryApplicationInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const LOTTERY_SALES_ID = 'lottery_sales_id';
    const USER_ID = 'user_id';
    const EMAIL = 'email';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const FIRSTNAME_KANA = 'firstnamekana';
    const LASTNAME_KANA = 'lastnamekana';
    const TELEPHONE = 'telephone';
    const POSTCODE = 'postcode';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const CITY = 'city';
    const STREET = 'street';
    const BUILDING = 'building';
    const STATUS = 'status';
    const ORDERED = 'ordered';
    const WEBSITE_ID = 'website_id';
    const LOTTERY_SALES_CODE = 'lottery_sales_code';
    const CREATED_AT = 'created_at';
    const UPDATED = 'updated_at';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return LotteryApplicationInterface
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getLotterySalesId();

    /**
     * @param $lotterySalesId
     * @return mixed
     */
    public function setLotterySalesId($lotterySalesId);

    /**
     * @return mixed
     */
    public function getUserId();

    /**
     * @param $userId
     * @return mixed
     */
    public function setUserId($userId);

    /**
     * @return mixed
     */
    public function getEmail();

    /**
     * @param $email
     * @return mixed
     */
    public function setEmail($email);

    /**
     * @return mixed
     */
    public function getFirstname();

    /**
     * @param $firstname
     * @return mixed
     */
    public function setFirstname($firstname);

    /**
     * @return mixed
     */
    public function getLastname();

    /**
     * @param $lastname
     * @return mixed
     */
    public function setLastname($lastname);

    /**
     * @return mixed
     */
    public function getFirstnamekana();

    /**
     * @param $firstnamekana
     * @return mixed
     */
    public function setFirstnamekana($firstnamekana);

    /**
     * @return mixed
     */
    public function getLastnamekana();

    /**
     * @param $lastnamekana
     * @return mixed
     */
    public function setLastnamekana($lastnamekana);

    /**
     * @return mixed
     */
    public function getTelephone();

    /**
     * @param $telephone
     * @return mixed
     */
    public function setTelephone($telephone);

    /**
     * @return mixed
     */
    public function getPostcode();

    /**
     * @param $zipcode
     * @return mixed
     */
    public function setPostcode($postcode);

    /**
     * @return mixed
     */
    public function getRegionId();

    /**
     * @param $regionId
     * @return mixed
     */
    public function setRegionId($regionId);

    /**
     * @return mixed
     */
    public function getRegion();

    /**
     * @param $region
     * @return mixed
     */
    public function setRegion($region);

    /**
     * @return mixed
     */
    public function getCity();

    /**
     * @param $city
     * @return mixed
     */
    public function setCity($city);

    /**
     * @return mixed
     */
    public function getStreet();

    /**
     * @param $street
     * @return mixed
     */
    public function setStreet($street);

    /**
     * @return mixed
     */
    public function getBuilding();

    /**
     * @param $building
     * @return mixed
     */
    public function setBuilding($building);

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return mixed
     */
    public function getOrdered();

    /**
     * @param $ordered
     * @return mixed
     */
    public function setOrdered($ordered);

    /**
     * @return mixed
     */
    public function getWebsiteId();

    /**
     * @param $websiteId
     * @return mixed
     */
    public function setWebsiteId($websiteId);

    /**
     * @return mixed
     */
    public function getLotterySalesCode();

    /**
     * @param $lotterySalesCode
     * @return mixed
     */
    public function setLotterySalesCode($lotterySalesCode);

    /**
     * @return string
     */
    public function getPurchaseDeadline();

    /**
     * @param string $purchaseDeadline
     * @return $this
     */
    public function setPurchaseDeadline($purchaseDeadline);

    /**
     * @return string
     */
    public function getLotteryDate();

    /**
     * @param string $lotteryDate
     * @return $this
     */
    public function setLotteryDate($lotteryDate);

    /**
     * Retrieve product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $timestamp
     * @return $this
     */
    public function setCreatedAt($timestamp);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Casio\LotterySale\Api\Data\LotteryApplicationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Casio\LotterySale\Api\Data\LotteryApplicationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Casio\LotterySale\Api\Data\LotteryApplicationExtensionInterface $extensionAttributes
    );
}
