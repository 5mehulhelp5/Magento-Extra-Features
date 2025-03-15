<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model;

use Casio\LotterySale\Api\Data\LotteryApplicationInterface;
use Casio\LotterySale\Api\Data\LotteryApplicationInterfaceFactory;
use Casio\LotterySale\Model\ResourceModel\LotteryApplication\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class LotteryApplication extends AbstractModel
{
    /**
     * Const status lottery
     */
    const STATUS_NOT_LOTTERY = -1;
    const STATUS_CLOSE = 0;
    const STATUS_SATISFY = 1;
    const STATUS_APPLIED = 2;

    /**
     * Const status ordered
     */
    const STATUS_NOT_ORDERED = 0;
    const STATUS_ORDERED = 1;

    /**
     * Const status lottery
     */
    const STATUS_APPLYING = 0;
    const STATUS_LOST = 10;
    const STATUS_WIN = 20;

    /**
     * @var LotteryApplicationInterfaceFactory
     */
    protected LotteryApplicationInterfaceFactory $lotteryApplicationDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $dataObjectHelper;

    /**
     * @var string
     */
    protected $_eventPrefix = 'casio_lottery_application';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param LotteryApplicationInterfaceFactory $lotteryApplicationDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\LotteryApplication $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LotteryApplicationInterfaceFactory $lotteryApplicationDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\LotteryApplication $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->lotteryApplicationDataFactory = $lotteryApplicationDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve lotteryApplication model with lotteryApplication data
     * @return LotteryApplicationInterface
     */
    public function getDataModel()
    {
        $lotteryApplicationData = $this->getData();

        $lotteryApplicationDataObject = $this->lotteryApplicationDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $lotteryApplicationDataObject,
            $lotteryApplicationData,
            LotteryApplicationInterface::class
        );

        return $lotteryApplicationDataObject;
    }
}
