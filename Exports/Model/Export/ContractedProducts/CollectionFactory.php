<?php

namespace Codilar\Exports\Model\Export\ContractedProducts;

use Codilar\Contract\Model\ResourceModel\Contract\Collection as ContractedProductsCollection;
use Codilar\Contract\Model\ResourceModel\Contract\CollectionFactory as ContractedProductsCollectionFactory;
use Magento\Framework\Data\Collection as AttributeCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\Config as EavConfig;


class CollectionFactory
{
    /**
     * @var ContractedProductsCollectionFactory
     */
    protected ContractedProductsCollectionFactory $collectionFactory;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $_resourceConnection;

    /** @var
     * EavConfig
     */
    protected EavConfig $_eavConfig;

    /**
     * CollectionFactory constructor.
     * @param ContractedProductsCollectionFactory $collectionFactory
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     */
    public function __construct(
        ContractedProductsCollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_eavConfig = $eavConfig;
    }

    public function create(AttributeCollection $attributeCollection, array $filters)
    {
        /** @var ContractedProductsCollection $collection */
        $collection = $this->collectionFactory->create();

        // Add the join with the company_division table
        $collection->getSelect()->joinLeft(
            ['cpd' => 'company_division'],
            'cpd.division_unique_id = main_table.division_unique_id',
            ['company_id','division_name']
        );

        return $collection;
    }

    /**
     * @param $connection
     * @param $fieldName
     * @param $condition
     * @return mixed
     */
    protected function _getConditionSql($connection, $fieldName, $condition)
    {
        return $connection->prepareSqlCondition($fieldName, $condition);
    }
}
