<?php

declare(strict_types=1);

namespace Casio\LotterySale\Model\ResourceModel\LotterySales;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Model\ResourceModel\LotterySales;
use Magento\Framework\App\ResourceConnection;

/**
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SaveMultiple
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Multiple save lottery sale items
     *
     * @param array $lotterySaleItems
     * @return void
     */
    public function execute(array $lotterySaleItems)
    {
        if (!count($lotterySaleItems)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(LotterySales::MAIN_TABLE);

        $updateFields = [
            LotterySalesInterface::SKU,
            LotterySalesInterface::TITLE,
            LotterySalesInterface::DESCRIPTION,
            LotterySalesInterface::LOTTERY_DATE,
            LotterySalesInterface::APPLICATION_DATE_FROM,
            LotterySalesInterface::APPLICATION_DATE_TO,
            LotterySalesInterface::PURCHASE_DEADLINE,
        ];
        $lotterySaleItems = $this->getLotterySalesItemsArray($lotterySaleItems);
        $connection->insertOnDuplicate($tableName, $lotterySaleItems, $updateFields);
    }

    /**
     * Get Sql bind data
     *
     * @param LotterySalesInterface[] $lotterySalesItems
     * @return array
     */
    private function getLotterySalesItemsArray(array $lotterySalesItems): array
    {
        $lotterySalesItemArray = [];
        foreach ($lotterySalesItems as $lotterySalesItem) {
            $lotterySalesItemArray[] = [
                'product_id'=> $lotterySalesItem->getProductId(),
                'sku'=> $lotterySalesItem->getSku(),
                'title'=> $lotterySalesItem->getTitle(),
                'description'=> $lotterySalesItem->getDescription(),
                'lottery_date'=> $lotterySalesItem->getLotteryDate(),
                'application_date_from'=> $lotterySalesItem->getApplicationDateFrom(),
                'application_date_to'=> $lotterySalesItem->getApplicationDateTo(),
                'purchase_deadline'=> $lotterySalesItem->getPurchaseDeadline(),
                'website_id'=> $lotterySalesItem->getWebsiteId()
            ];
        }
        return $lotterySalesItemArray;
    }
}
