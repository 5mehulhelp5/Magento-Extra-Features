<?php

namespace Casio\LotterySale\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class Website
 * Casio\LotterySale\Model\Config\Source
 */
class Website extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**
     * Core system store model
     *
     * @var Store
     */
    protected $_store;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Website constructor.
     * @param Store $store
     * @param StoreManager $storeManager
     */
    public function __construct(
        Store $store,
        StoreManager $storeManager
    ) {
        $this->_store = $store;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * @param false $withDefault
     * @param string $attribute
     * @return array
     */
    public function getOptionArray($withDefault = false, $attribute = 'name')
    {
        return $this->_store->getWebsiteOptionHash($withDefault, $attribute);
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return self::toOptionArray();
    }

    /**
     * @return array
     */
    public function getAllWebsiteNames()
    {
        $websites = $this->storeManager->getWebsites();
        $websiteNames = [];
        if (isset($websites)) {
            foreach ($websites as $website) {
                $websiteNames[$website->getId()] = $website->getName();
            }
        }
        return $websiteNames;
    }

    /**
     * @return array
     */
    public function getAllWebsiteCode()
    {
        $websites = $this->storeManager->getWebsites();
        $websiteNames = [];
        if (isset($websites)) {
            foreach ($websites as $website) {
                $websiteNames[$website->getId()] = $website->getCode();
            }
        }
        return $websiteNames;
    }

    /**
     * @return int[]|string[]
     */
    public function getWebsiteIds()
    {
        return array_keys($this->getAllWebsiteNames());
    }
}
