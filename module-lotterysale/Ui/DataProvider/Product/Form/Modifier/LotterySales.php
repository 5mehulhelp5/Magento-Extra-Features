<?php

namespace Casio\LotterySale\Ui\DataProvider\Product\Form\Modifier;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Api\LotterySalesRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class LotterySales extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private LocatorInterface $locator;
    /**
     * @var LotterySalesRepositoryInterface
     */
    private LotterySalesRepositoryInterface $lotterySalesRepository;
    /**
     * @var ArrayManager
     */
    private ArrayManager $arrayManager;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * LotterySales constructor.
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezone
     * @param LotterySalesRepositoryInterface $lotterySalesRepository
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        LotterySalesRepositoryInterface $lotterySalesRepository
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->lotterySalesRepository = $lotterySalesRepository;
    }
    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $product = $this->locator->getProduct();
        $fromField = 'casio_pre_order_start_date';
        $toField = 'casio_pre_order_end_date';
        $lotterySales = $this->lotterySalesRepository->getByProductId($product->getId());
        if ($lotterySales && ($lotterySales->getApplicationDateFrom() || $lotterySales->getApplicationDateTo())) {
            $preOrderDisabled = true;
            if ($lotterySales->getApplicationDateTo() || $lotterySales->getApplicationDateFrom()) {
                $arguments = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'validation' => [
                                    'required-entry' => true
                                ]
                            ]
                        ]
                    ]
                ];
                $meta['casio_lottery_sales']['children'] = [
                    'application_date_from' => $arguments,
                    'application_date_to' => $arguments,
                    'lottery_date' => $arguments,
                    'purchase_deadline' => $arguments,
                    'title' => $arguments,
                    'description' => $arguments,
                ];
            }
        }
        $fromFieldPath = $this->arrayManager->findPath($fromField, $meta, null, 'children');
        $toFieldPath = $this->arrayManager->findPath($toField, $meta, null, 'children');
        if ($fromFieldPath && $toFieldPath) {
            $meta = $this->setDisabled($meta, [$fromFieldPath, $toFieldPath], $preOrderDisabled ?? false);
        }
        if ($product->getCasioPreOrderStartDate() || $product->getCasioPreOrderEndDate()) {
            $arguments = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'disabled' => true
                        ]
                    ]
                ]
            ];
            $meta['casio_lottery_sales']['children'] = [
                'application_date_from' => $arguments,
                'application_date_to' => $arguments,
            ];
        }
        return $meta;
    }

    /**
     * @param $meta
     * @param $list
     * @param $disabled
     * @return array|mixed
     */
    private function setDisabled($meta, $list, $disabled)
    {
        foreach ($list as $item) {
            $meta = $this->arrayManager->merge(
                $item . self::META_CONFIG_PATH,
                $meta,
                [
                    'imports' => [
                        '__disableTmpl' => [
                            'setApplicationFromDisable' => false,
                            'setApplicationToDisable' => false,
                        ],
                        'setApplicationFromDisable' => 'ns = ${ $.ns }, index = application_date_from:value',
                        'setApplicationToDisable' => 'ns = ${ $.ns }, index = application_date_to:value',
                    ],
                    'enabledValues' => [
                        '1' => 'product[casio_lottery_sales][application_date_from]',
                        '2' => 'product[casio_lottery_sales][application_date_to]',
                    ],
                    'disabled' => $disabled,
                    'serviceDisabled' => $disabled,
                ]
            );
        }
        return $meta;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $lotterySales = $this->lotterySalesRepository->getByProductId($product->getId());
        if ($lotterySales) {
            $data[$product->getId()]['product']['casio_lottery_sales'] = [
                LotterySalesInterface::TITLE => $lotterySales->getTitle(),
                LotterySalesInterface::DESCRIPTION => $lotterySales->getDescription(),
                LotterySalesInterface::LOTTERY_DATE => $lotterySales->getLotteryDate(),
                LotterySalesInterface::APPLICATION_DATE_TO => $this->convertDateTime($lotterySales->getApplicationDateTo()),
                LotterySalesInterface::APPLICATION_DATE_FROM => $this->convertDateTime($lotterySales->getApplicationDateFrom()),
                LotterySalesInterface::PURCHASE_DEADLINE => $this->convertDateTime($lotterySales->getPurchaseDeadline()),
            ];
        }
        return $data;
    }

    /**
     * @param $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    private function convertDateTime($value)
    {
        if ($value) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $timezone = $this->timezone->getConfigTimezone('website', $websiteId);
            $dateTime = new \Datetime($value);
            $dateTime->setTimezone(new \DateTimeZone($timezone));
            $value = $dateTime->format('Y-m-d H:i:s');
        }
        return $value;
    }
}
