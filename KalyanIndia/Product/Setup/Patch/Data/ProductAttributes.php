<?php

declare(strict_types=1);

namespace KalyanIndia\Product\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class ProductAttributes implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param SetFactory $attributeSetFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly SetFactory $attributeSetFactory,
        private readonly AttributeSetRepositoryInterface $attributeSetRepository
    ) {
    }

    /**
     * Function to create product attributes
     *
     * @return void
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetName = 'Kalyan Store India';
        $attributeSetId = $this->getAttributeSetIdByName($attributeSetName, $entityTypeId,$eavSetup);
        $attributeGroupName = 'KJ INDIA Attributes';
        $attributeGroupId = $this->getAttributeGroupIdByName($attributeGroupName, $entityTypeId, $attributeSetId, $eavSetup);

        $attributes = [
            'kji_product_type' => [
                'type'                      => 'int',
                'label'                     => 'Product Type',
                'input'                     => 'select',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
                'is_visible_on_front'       => false,
                'option'                    => []
            ],
            'kji_product_subtype' => [
                'type'                      => 'int',
                'label'                     => 'Product Subtype',
                'input'                     => 'select',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
                'option'                    => []
            ],
            'kji_platinum_weight' => [
                'type'                      => 'varchar',
                'label'                     => 'Platinum Weight',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_gold_weight' => [
                'type'                      => 'varchar',
                'label'                     => 'Gold Weight',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_platinum_purity' => [
                'type'                      => 'int',
                'label'                     => 'Platinum Purity',
                'input'                     => 'select',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
                'option'                    => []
            ],
            'kji_gold_purity' => [
                'type'                      => 'int',
                'label'                     => 'Gold Purity',
                'input'                     => 'select',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
                'option'                    => []
            ],
            'kji_ring_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Ring Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_kada_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Kada Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_anklet_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Anklet Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_mangalsutra_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Mangalsutra Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_chain_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Chain Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_bracelet_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Bracelet Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_bangle_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Bangle Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_fixed_weight_bracelet_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Fixed Weight Bracelet Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_fixed_weight_leather_bracelet_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Fixed weight Leather Bracelet Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_oval_bracelet_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Oval Bracelet Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_pendant_with_chain_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Pendant With Chain Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ],
            'kji_necklace_size' => [
                'type'                      => 'varchar',
                'label'                     => 'Necklace Size',
                'input'                     => 'text',
                'global'                    => ScopedAttributeInterface::SCOPE_STORE,
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'unique'                    => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => false,
            ]
        ];

        foreach ($attributes as $code => $data) {
            $eavSetup->addAttribute(Product::ENTITY, $code, $data);
        }

        foreach (array_keys($attributes) as $code) {
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $code, null);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Function to get dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Function to get aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    public function getAttributeSetIdByName($attributeSetName, $entityTypeId, $eavSetup)
    {
        try {
            $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, $attributeSetName);
        } catch (\Exception $e) {
            $defaultAttributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);
            $attributeSet = $this->attributeSetFactory->create();

            $data = [
                'attribute_set_name' => $attributeSetName,
                'entity_type_id' => $entityTypeId,
                'sort_order' => 200,
            ];
            $attributeSet->setData($data);
            $attributeSet->validate();
            $this->attributeSetRepository->save($attributeSet);
            $attributeSet->initFromSkeleton($defaultAttributeSetId);
            $this->attributeSetRepository->save($attributeSet);
            $attributeSetId = (int) $attributeSet->getId();
        }
        return $attributeSetId;
    }


    public function getAttributeGroupIdByName($attributeGroupName, $entityTypeId, $attributeSetId, $eavSetup)
    {
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupName);
        $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroupName);
        return $attributeGroupId;
    }
}
