<?php

namespace Codilar\Exports\Model\Export\ContractedProducts;

use Exception;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
class AttributeCollectionProvider
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var AttributeFactory
     */
    private AttributeFactory $attributeFactory;

    /**
     * @var array
     */
    protected array $entityAttributes = [
        [
            'id' => 'sku',
            'backend_type' => 'varchar',
            'frontend_input' => 'text',
            'source_model' => null,
            'frontend_label' => 'SKU',
            'attribute_code' => 'sku'
        ]
    ];

    /**
     * AttributeCollectionProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->collection = $collectionFactory->create(Collection::class);
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function get()
    {
        if (count($this->collection) === 0) {
            foreach ($this->entityAttributes as $attribute) {
                /** @var \Magento\Eav\Model\Entity\Attribute $attributeModel */
                $attributeModel = $this->attributeFactory->create();
                $attributeModel->setId($attribute['id']);
                $attributeModel->setBackendType($attribute['backend_type']);
                $attributeModel->setFrontendInput($attribute['frontend_input']);
                if ($attribute['source_model'] != null) {
                    $attributeModel->setSourceModel($attribute['source_model']);
                }
                $attributeModel->setDefaultFrontendLabel($attribute['frontend_label']);
                $attributeModel->setAttributeCode($attribute['attribute_code']);
                $this->collection->addItem($attributeModel);
            }
        }
        return $this->collection;
    }
}
