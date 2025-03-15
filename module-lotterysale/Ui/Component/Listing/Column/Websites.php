<?php

namespace Casio\LotterySale\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Casio\LotterySale\Model\Config\Source\Website;

/**
 * Class Websites
 * Casio\LotterySale\Ui\Component\Listing\Column
 */
class Websites extends Column
{
    /** @var Website  */
    protected $sourceWebsite;

    /**
     * Websites constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Website $sourceWebsite
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Website $sourceWebsite,
        array $components = [],
        array $data = []
    ) {
        $this->sourceWebsite = $sourceWebsite;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }
        $websiteNames = $this->sourceWebsite->getAllWebsiteNames();
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $websiteId = $item[$fieldName];
                    $item[$fieldName] = $websiteNames[$websiteId] ?? '';
                }
            }
        }
        return $dataSource;
    }
}
