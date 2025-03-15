<?php
namespace Casio\LotterySale\Plugin\Framework\View\Element;

use Casio\LotterySale\Helper\Data as DataHelper;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Bookmark;
use Magento\Framework\App\RequestInterface;

/**
 * Class UiComponentFactoryPlugin
 * Casio\LotterySale\Plugin\Framework\View\Element
 */
class UiComponentFactoryPlugin
{
    /** @var DataHelper  */
    protected $dataHelper;

    /** @var RequestInterface  */
    protected $requestInterface;

    public function __construct(
        DataHelper $dataHelper,
        RequestInterface $requestInterface
    ) {
        $this->dataHelper = $dataHelper;
        $this->requestInterface = $requestInterface;
    }

    /**
     * @param UiComponentFactory $subject
     * @param $identifier
     * @param null $name
     * @param array $arguments
     * @return array
     */
    public function beforeCreate(UiComponentFactory $subject, $identifier, $name = null, array $arguments = [])
    {
        if (isset($arguments['context'])) {
            $context = $arguments['context'];
            if ($context->getNamespace() == 'casio_lottery_application_listing' && isset($arguments['config']) & isset($arguments['name']) && isset($arguments['config']['dataType']) && $arguments['config']['dataType'] == 'date') {
                $config = $arguments['config'];
                $fieldName = $arguments['name'];
                $arguments['data']['config'] = $this->conventDateTimeValue($fieldName, $config);
            }
        }
        return [$identifier, $name, $arguments];
    }

    /**
     * @param $fieldName
     * @param $config
     * @return mixed
     */
    private function conventDateTimeValue($fieldName, $config)
    {
        $params = $this->requestInterface->getParams();
        if (isset($params['filters'])) {
            $filters = $params['filters'];
            if (isset($filters[$fieldName]) && isset($filters['website_id'])) {
                $dateFormat = $this->dataHelper->getDateTimeFormat();
                $timezone = $this->dataHelper->getWebsiteCreateDateTimezone($filters['website_id']);
                $config['timezone'] = $timezone;
                $config['dateFormat'] = $dateFormat;
                $config['component'] = "Casio_CustomDatetime/js/grid/columns/date";
            }
        }

        return $config;
    }
}
