<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\ResourceModel;

use Dotdigitalgroup\Email\Setup\SchemaInterface as Schema;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class LotteryApplication extends AbstractDb
{

    const MAIN_TABLE = 'casio_lottery_application';

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * LotteryApplication constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('casio_lottery_application', 'id');
    }

    /**
     * Returns next autoincrement value for a table
     *
     * @return int
     * @throws LocalizedException
     */
    public function getNextAutoincrement()
    {
        $connection = $this->getConnection();
        $entityStatus = $connection->showTableStatus($this->getMainTable());

        if (empty($entityStatus['Auto_increment'])) {
            throw new LocalizedException(__('Cannot get autoincrement value'));
        }

        return $entityStatus['Auto_increment'];
    }

    /**
     * @param $userId
     * @param $productId
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdByUserIdAndProductId($userId, $productId)
    {
        $connection = $this->getConnection();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $lotterySalesTable = $connection->getTableName(LotterySales::MAIN_TABLE);
        $select = $connection->select()->from($this->getMainTable(), 'id')
            ->join($lotterySalesTable, "lottery_sales_id = {$lotterySalesTable}.id")
            ->where('user_id = :user_id')
            ->where('product_id = :product_id')
            ->where('website_id = :website_id');

        $bind = [':user_id' => $userId,':product_id' => $productId, ':website_id' => $websiteId];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Update ordered value
     *
     * @param $lotteryApplicationIds
     */
    public function updateOrderedValue($lotteryApplicationIds)
    {
        if (empty($lotteryApplicationIds)) {
            return;
        }

        $bind = ['ordered' => true];

        $where = ['id IN(?)' => $lotteryApplicationIds];
        $this->getConnection()->update(
            $this->getTable(self::MAIN_TABLE),
            $bind,
            $where
        );
    }
}
