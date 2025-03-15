<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Casio\LotterySale\Helper\Data as DataHelper;

/**
 * Class CustomerAttributeCollection
 * Casio\LotterySale\Model\Export\LotteryApplication
 */
class CustomerAttributeCollection
{
    /** @var CustomerCollectionFactory  */
    protected $_customerCollectionFactory;

    /**
     * CustomerAttributeCollection constructor.
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->_customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @param array $userIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAttributes(array $userIds)
    {
        $customerAttributeData = [];
        if (!empty($userIds)) {
            $items = $this->_customerCollectionFactory->create()
                ->addAttributeToSelect([
                    DataHelper::CUSTOMER_LOTTERY_APPLICATED_COUNT,
                    DataHelper::CUSTOMER_LOTTERY_WIN_COUNT,
                    DataHelper::CUSTOMER_EMAIL
                ])
                ->addAttributeToFilter(
                    'entity_id',
                    ['in' => $userIds]
                )
                ->getItems();
            foreach ($items as $item) {
                $entityId = $item->getEntityId();
                $customerAttributeData[$entityId] = $item->getData();
            }
        }
        return $customerAttributeData;
    }
}
