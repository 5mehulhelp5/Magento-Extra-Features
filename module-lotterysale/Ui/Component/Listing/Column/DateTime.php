<?php
namespace Casio\LotterySale\Ui\Component\Listing\Column;

use Casio\LotterySale\Helper\Data as HelperData;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class DateTime
 * Casio\LotterySale\Ui\Component\Listing\Column
 */
class DateTime extends \Magento\Ui\Component\Listing\Columns\Date
{
    /** @var HelperData  */
    protected $_helperData;

    /**
     * DateTime constructor.
     * @param HelperData $helperData
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param BooleanUtils $booleanUtils
     * @param array $components
     * @param array $data
     * @param ResolverInterface|null $localeResolver
     * @param DataBundle|null $dataBundle
     */
    public function __construct(
        HelperData $helperData,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        array $components = [],
        array $data = [],
        ResolverInterface $localeResolver = null,
        DataBundle $dataBundle = null
    ) {
        $this->_helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $timezone, $booleanUtils, $components, $data, $localeResolver, $dataBundle);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                ) {
                    $websiteId = $item['website_id'] ?? 1;
                    $date = new \DateTime($item[$this->getData('name')]);
                    $item[$this->getData('name')] = $this->_helperData->getWebsiteFormatDateTime($date, $websiteId);
                }
            }
        }

        return $dataSource;
    }
}
