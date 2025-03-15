<?php

declare(strict_types=1);

namespace Casio\LotterySale\Model\ResourceModel\LotterySales;

use Casio\LotterySale\Api\Data\LotterySalesInterface;
use Casio\LotterySale\Model\ResourceModel\LotterySales;
use Magento\Framework\App\ResourceConnection;

/**
 * Implementation of LotterySales delete multiple operation for specific db layer
 * Delete Multiple used here for performance efficient purposes over single delete operation
 */
class DeleteMultiple
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
     * Multiple delete lottery sales
     *
     * @param LotterySalesInterface[] $lotterySales
     * @return void
     */
    public function execute(array $lotterySales)
    {
        if (!count($lotterySales)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(LotterySales::MAIN_TABLE);

        $whereSql = $this->buildWhereSqlPart($lotterySales);
        $connection->delete($tableName, $whereSql);
    }

    /**
     * @param array $lotterySales
     * @return string
     */
    private function buildWhereSqlPart(array $lotterySales): string
    {
        $connection = $this->resourceConnection->getConnection();

        $condition = [];
        /** @var LotterySalesInterface $lotterySale */
        foreach ($lotterySales as $lotterySale) {
            $productIdCondition = $connection->quoteInto(
                LotterySalesInterface::PRODUCT_ID . ' = ?',
                $lotterySale->getProductId()
            );
            $websiteIdCondition = $connection->quoteInto(
                LotterySalesInterface::WEBSITE_ID . ' = ?',
                $lotterySale->getWebsiteId()
            );
            $condition[] = '(' . $productIdCondition . ' AND ' . $websiteIdCondition . ')';
        }
        return implode(' OR ', $condition);
    }
}
