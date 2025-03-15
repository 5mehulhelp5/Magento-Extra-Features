<?php

namespace Ktpl\ExtendedRma\Model;

use Magento\Store\Model\ResourceModel\Store\CollectionFactory as storeCollectionFactory;
use Magento\Store\Model\Website;

class Store
{
    public const STORE_ID = "store_id";
    public const WEBSITE_ID = "website_id";
    public const NAME = "name";
    /**
     * @var storeCollectionFactory
     */
    private $storeCollectionFactory;
    /**
     * @var Website
     */
    private $website;

    /**
     * Store constructor.
     * @param storeCollectionFactory $storeCollectionFactory
     * @param Website $website
     */
    public function __construct(
        storeCollectionFactory $storeCollectionFactory,
        Website $website
    ) {
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->website = $website;
    }
    /**
     * Return All Stores
     *
     * @return array
     */
    public function toOptionArray()
    {
        $stores = $this->storeCollectionFactory->create()->getData();
        $storeList = [];
        foreach ($stores as $store) {
            $storeId = $store[self::STORE_ID];
            $data = $this->website->load($store[self::WEBSITE_ID], self::WEBSITE_ID)->getData();
            $websiteName = $data[self::NAME];
            $storeView = $store[self::NAME];
            $storeList[$storeId] = $websiteName.' '.$storeView;
        }
        return $storeList;
    }
}

