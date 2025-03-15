<?php

namespace Casio\LotterySale\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Data
 * Casio\LotterySale\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_LOTTERY_SALES_RECEPTION_EMAIL_TEMPLATE = 'lottery_sale/reception_email/template';
    const XML_PATH_LOTTERY_SALES_RECEPTION_EMAIL_SENDER = 'lottery_sale/reception_email/sender';
    const XML_PATH_LOTTERY_SALES_WIN_EMAIL_TEMPLATE = 'lottery_sale/win_email/template';
    const XML_PATH_LOTTERY_SALES_WIN_EMAIL_SENDER = 'lottery_sale/win_email/sender';
    const CUSTOMER_LOTTERY_APPLICATED_COUNT = 'casio_lottery_applicated_count';
    const CUSTOMER_FIRSTNAME_KANA = 'firstnamekana';
    const CUSTOMER_LASTNAME_KANA = 'lastnamekana';
    const XML_PATH_MY_ACCOUNT_ENABLE_LOTTERY_MENU = 'lottery_sale/general/enabled';
    const CUSTOMER_EMAIL = 'email';
    const CUSTOMER_LOTTERY_WIN_COUNT = 'casio_lottery_win_count';
    const STATUS_APPLYING = 0;
    const STATUS_LOST = 10;
    const STATUS_WINNING = 20;
    const STATUS_APPLYING_TITLE = 'Applying';
    const STATUS_LOST_TITLE = 'Lost';
    const STATUS_WINNING_TITLE = 'Winning';
    const STATUS = [
        self::STATUS_APPLYING => self::STATUS_APPLYING_TITLE,
        self::STATUS_LOST => self::STATUS_LOST_TITLE,
        self::STATUS_WINNING => self::STATUS_WINNING_TITLE,
    ];
    const NUMBER_WINNER = 'lottery_application_number_winner';

    /** @var TimezoneInterface  */
    protected $_localeDate;

    /**
     * Data constructor.
     * @param Context $context
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Context $context,
        TimezoneInterface $localeDate
    ) {
        parent::__construct($context);
        $this->_localeDate = $localeDate;
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getWebsiteCreateDateTimezone($websiteId)
    {
        return $this->scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function isEnabled($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MY_ACCOUNT_ENABLE_LOTTERY_MENU,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::MEDIUM);
    }

    /**
     * @param $dateTime
     * @param $websiteId
     * @return string|null
     * @throws \Exception
     */
    public function getWebsiteFormatDateTime($dateTime, $websiteId, $format = 'Y/m/d H:i:s')
    {
        if ($dateTime != null) {
            $returnDateTime = $this->formatDate(
                $dateTime,
                \IntlDateFormatter::MEDIUM,
                true,
                $this->getWebsiteCreateDateTimezone($websiteId)
            );
            return (new \DateTime($returnDateTime))->format($format);
        }
        return $dateTime;
    }

    /**
     * @param null $date
     * @param int $format
     * @param false $showTime
     * @param null $timezone
     * @return string
     * @throws \Exception
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date ?? 'now');
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            'en_US',
            $timezone
        );
    }

    /**
     * Get reception identifier email
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getReceptionIdentifierEmail($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOTTERY_SALES_RECEPTION_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get reception identifier email
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getWinIdentifierEmail($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOTTERY_SALES_WIN_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get identity email
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getWinIdentity($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOTTERY_SALES_WIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get identity email
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getIdentity($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOTTERY_SALES_RECEPTION_EMAIL_SENDER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get date with current timezone
     *
     * @param string $date
     * @return string
     */
    public function getDate(string $date)
    {
        return $this->_localeDate->date(new \DateTime($date))
            ->format('Y-m-d H:i:s') ;
    }
}
