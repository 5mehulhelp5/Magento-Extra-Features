<?php
declare(strict_types=1);

namespace Casio\LotterySale\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class LotterySales extends AbstractDb
{
    const MAIN_TABLE = 'casio_lottery_sales';
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * LotterySales constructor.
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
        $this->_init(self::MAIN_TABLE, 'id');
    }

    /**
     * @param $productId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdByProductId($productId)
    {
        $connection = $this->getConnection();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $select = $connection->select()->from($this->getMainTable(), 'id')
            ->where('product_id = :product_id')
            ->where('website_id = :website_id');

        $bind = [':product_id' => $productId, ':website_id' => $websiteId];

        return $connection->fetchOne($select, $bind);
    }
}
