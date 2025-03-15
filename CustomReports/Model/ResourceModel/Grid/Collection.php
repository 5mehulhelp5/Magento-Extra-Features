<?php

namespace Codilar\CustomReports\Model\ResourceModel\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Webkul\Walletsystem\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => ['entity_id' => 'main_table.entity_id', 'customer_email' => 'customer_grid_flat.email', 'status_label' => 'main_table.status']];

    /**
     * Initialize dependencies.
     * Phpcs:disable
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
                      $mainTable = 'wk_ws_wallet_transaction',
                      $resourceModel = \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Render filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['customer_grid_flat' => $this->getTable('customer_grid_flat')],
            'customer_grid_flat.entity_id = main_table.customer_id',
            ['customer_email' => 'email', 'status_label' => 'main_table.status']
        );
        parent::_renderFiltersBefore();
    }
}
