<?php

namespace Codilar\ExtendedSales\Ui\Component\Listing\Column\Store;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as storeCollectionFactory;
use Magento\Store\Model\WebsiteFactory;

class Options implements OptionSourceInterface
{
    public const WEBSITE_ID = 'website_id';
    public const NAME = 'name';
    /**
     * @var storeCollectionFactory
     */
    private $storeCollectionFactory;
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * Options constructor.
     * @param storeCollectionFactory $storeCollectionFactory
     * @param WebsiteFactory $websiteFactory
     */
    public function __construct(
        storeCollectionFactory $storeCollectionFactory,
        WebsiteFactory $websiteFactory
    ) {
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->websiteFactory = $websiteFactory;
    }

    /**
     * Return the All Stores
     *
     * @return array
     */
    public function toOptionArray()
    {
        $option = [];

        foreach ($this->storeCollectionFactory->create() as $website) {
            $store = $this->websiteFactory->create()->load($website->getWebsiteId(), self::WEBSITE_ID)->getData();
            $option[$website->getStoreId()] = [
                'value' => $website->getStoreId(),
                'label' => $store[self::NAME] . " ".$website->getName()
            ];
        }
        return $option;
    }
}
