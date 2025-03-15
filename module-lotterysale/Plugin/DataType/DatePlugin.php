<?php

namespace Casio\LotterySale\Plugin\DataType;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Date;

class DatePlugin
{
    const LOTTERY_APPLICATION_LISTING = 'casio_lottery_application_listing';
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $localeDate;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * DatePlugin constructor.
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     */
    public function __construct(
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }
    /**
     * @param Date $subject
     * @param callable $proceed
     * @param int $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $setUtcTimeZone
     */
    public function aroundConvertDate(Date $subject, callable $proceed, $date, $hour = 0, $minute = 0, $second = 0, $setUtcTimeZone = true)
    {
        $filters = $this->request->getParam('filters');
        if (isset($filters['website_id'])
            && $this->request->getParam('namespace') == self::LOTTERY_APPLICATION_LISTING
        ) {
            $websiteId = $filters['website_id'];
            try {
                $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
                $this->storeManager->setCurrentStore($storeId);
                $dateObj = $this->localeDate->date($date, $subject->getLocale(), true);

                $dateObj->setTime($hour, $minute, $second);
                if ($setUtcTimeZone) {
                    $dateObj->setTimezone(new \DateTimeZone('UTC'));
                }
                return $dateObj;
            } catch (\Exception $e) {
                return null;
            }
        }

        return $proceed($date, $hour, $minute, $second, $setUtcTimeZone);
    }
}
