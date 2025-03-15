<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Api\Data\LotterySalesInterfaceFactory;
use Casio\LotterySale\Model\ResourceModel\LotterySales\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class LotterySales extends AbstractModel
{
    /**
     * @var LotterySalesInterfaceFactory
     */
    protected LotterySalesInterfaceFactory $lotterySalesDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $dataObjectHelper;

    /**
     * @var string
     */
    protected $_eventPrefix = 'casio_lottery_sales';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param LotterySalesInterfaceFactory $lotterySalesDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\LotterySales $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LotterySalesInterfaceFactory $lotterySalesDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\LotterySales $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->lotterySalesDataFactory = $lotterySalesDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve lotterySales model with lotterySales data
     * @return LotterySalesInterface
     */
    public function getDataModel()
    {
        $lotterySalesData = $this->getData();

        $lotterySalesDataObject = $this->lotterySalesDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $lotterySalesDataObject,
            $lotterySalesData,
            LotterySalesInterface::class
        );

        return $lotterySalesDataObject;
    }
}
