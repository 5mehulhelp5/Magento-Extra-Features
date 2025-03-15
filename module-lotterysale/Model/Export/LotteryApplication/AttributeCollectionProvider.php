<?php
namespace Casio\LotterySale\Model\Export\LotteryApplication;

use Casio\Core\Model\Source\WebsitesPermission;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;

/**
 * Class AttributeCollectionProvider
 * Casio\LotterySale\Model\Export\LotteryApplication
 */
class AttributeCollectionProvider
{
    /** @var Collection  */
    protected $collection;

    /** @var AttributeFactory  */
    private $attributeFactory;

    /**
     * @var array
     */
    protected $entityAttributes = [
        [
            'id' => 'website_id',
            'backend_type' => 'multiselect',
            'frontend_input' => 'multiselect',
            'source_model' => WebsitesPermission::class,
            'frontend_label' => 'Site ID',
            'attribute_code' => 'website_id'
        ],
        [
            'id' => 'sku',
            'backend_type' => 'varchar',
            'frontend_input' => 'text',
            'source_model' => null,
            'frontend_label' => 'SKU',
            'attribute_code' => 'sku'
        ],
        [
            'id' => 'created_at',
            'backend_type' => 'datetime',
            'frontend_input' => 'datetime',
            'source_model' => null,
            'frontend_label' => 'Applicated Date',
            'attribute_code' => 'created_at'
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
     * @throws \Exception
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
