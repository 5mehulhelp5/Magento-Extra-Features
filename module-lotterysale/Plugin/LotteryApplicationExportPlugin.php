<?php
namespace Casio\LotterySale\Plugin;

use Casio\LotterySale\Model\Export\LotteryApplication;
use Casio\LotterySale\Model\Config\Source\Website as SourceWebsite;

class LotteryApplicationExportPlugin
{
    /** @var SourceWebsite  */
    protected $sourceWebsite;

    public function __construct(
        SourceWebsite $sourceWebsite
    ) {
        $this->sourceWebsite = $sourceWebsite;
    }

    /**
     * @param \Magento\ImportExport\Controller\Adminhtml\Export\Export $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundExecute(
        \Magento\ImportExport\Controller\Adminhtml\Export\Export $subject,
        callable $proceed
    ) {
        $params = $subject->getRequest()->getParams();
        if (isset($params['entity']) && $params['entity'] == LotteryApplication::ENTITY_TYPE_CODE) {
            $exportFilter = $params['export_filter'] ?? [];
            if (!isset($exportFilter['website_id'])) {
                $exportFilter['website_id'] = $this->sourceWebsite->getWebsiteIds();
            }
            $params['export_filter'] = $exportFilter;
            $subject->getRequest()->setParams($params);
        }
        return $proceed();
    }
}
