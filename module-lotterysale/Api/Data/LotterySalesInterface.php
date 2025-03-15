<?php

namespace Casio\LotterySale\Api\Data;

use Casio\LotterySale\Model\Data\LotterySales;
use Magento\Framework\Api\ExtensibleDataInterface;

interface LotterySalesInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const SKU = 'sku';
    const LOTTERY_DATE = 'lottery_date';
    const APPLICATION_DATE_FROM = 'application_date_from';
    const APPLICATION_DATE_TO = 'application_date_to';
    const PURCHASE_DEADLINE = 'purchase_deadline';
    const WEBSITE_ID = 'website_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

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
     * Retrieve description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Retrieve title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return mixed|null
     */
    public function getWebsiteId();

    /**
     * @return mixed|null
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * @return mixed|null
     */
    public function getUpdatedAt();

    /**
     * @param $updateAt
     * @return mixed
     */
    public function setUpdatedAt($updateAt);

    /**
     * @param $websiteId
     * @return LotterySales
     */
    public function setWebsiteId($websiteId);

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
     * @return string
     */
    public function getApplicationDateFrom();

    /**
     * @param string $applicationDateFrom
     * @return $this
     */
    public function setApplicationDateFrom($applicationDateFrom);

    /**
     * @return string
     */
    public function getApplicationDateTo();

    /**
     * @param string $applicationDateTo
     * @return $this
     */
    public function setApplicationDateTo($applicationDateTo);

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Casio\LotterySale\Api\Data\LotterySalesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Casio\LotterySale\Api\Data\LotterySalesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Casio\LotterySale\Api\Data\LotterySalesExtensionInterface $extensionAttributes
    );
}
