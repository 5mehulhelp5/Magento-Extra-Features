<?php
namespace Casio\LotterySale\Plugin;

use Casio\LotterySale\Model\Export\LotteryApplication;
use Casio\LotterySale\Model\Config\Source\Website as SourceWebsite;
use Magento\Framework\Serialize\Serializer\Json as JsonFramework;

/**
 * Class ScheduledOperation
 * Casio\LotterySale\Plugin
 */
class ScheduledOperation
{
    /** @var SourceWebsite  */
    protected $sourceWebsite;

    /** @var JsonFramework  */
    protected $jsonFramework;

    public function __construct(
        SourceWebsite $sourceWebsite,
        JsonFramework $jsonFramework
    ) {
        $this->sourceWebsite = $sourceWebsite;
        $this->jsonFramework = $jsonFramework;
    }

    /**
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundBeforeSave(
        \Magento\ScheduledImportExport\Model\Scheduled\Operation $subject,
        callable $proceed
    ) {
        if ($subject->getEntityType() == LotteryApplication::ENTITY_TYPE_CODE) {
            $attrsInfo = $subject->getEntityAttributes();
            if (!isset($attrsInfo['export_filter']['website_id'])) {
                $allowWebsiteIds = $this->sourceWebsite->getWebsiteIds();
                $attrsInfo['export_filter']['website_id'] = $allowWebsiteIds;
            }
            if (is_array($attrsInfo) && $attrsInfo) {
                $subject->setEntityAttributes($this->jsonFramework->serialize($attrsInfo));
            }
        }
        return $proceed();
    }
}
