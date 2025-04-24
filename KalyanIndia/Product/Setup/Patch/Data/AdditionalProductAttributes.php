<?php

declare(strict_types=1);

namespace KalyanIndia\Product\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class AdditionalProductAttributes implements DataPatchInterface
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
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetName = 'Kalyan Store India';
        $attributeSetId = $this->getAttributeSetIdByName($attributeSetName, $entityTypeId, $eavSetup);
        $attributeGroupName = 'KJ INDIA Attributes';
        $attributeGroupId = $this->getAttributeGroupIdByName($attributeGroupName, $entityTypeId, $attributeSetId, $eavSetup);

        $attributes = [
            'kji_barcode' => [
                'type'                      => 'varchar',
                'label'                     => 'Barcode',
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
            'kji_collection' => [
                'type'                      => 'int',
                'label'                     => 'Collection',
                'input'                     => 'multiselect',
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
            'kji_gender' => [
                'type'                      => 'int',
                'label'                     => 'Gender',
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
            'kji_material' => [
                'type'                      => 'int',
                'label'                     => 'Material',
                'input'                     => 'multiselect',
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
            'kji_metal_color' => [
                'type'                      => 'int',
                'label'                     => 'Metal Color',
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
            'kji_metal_value' => [
                'type'                      => 'varchar',
                'label'                     => 'Metal Value',
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
            'kji_diamond_weight' => [
                'type'                      => 'varchar',
                'label'                     => 'Diamond Weight',
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
            'kji_diamond_clarity' => [
                'type'                      => 'int',
                'label'                     => 'Diamond Clarity',
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
            'kji_carat_based_diamond_rate' => [
                'type'                      => 'varchar',
                'label'                     => 'Carat Based Diamond Rate',
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
            'kji_cz_diamond_price' => [
                'type'                      => 'varchar',
                'label'                     => 'CZ Diamond Price',
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
            'kji_cz_diamond_weight' => [
                'type'                      => 'varchar',
                'label'                     => 'CZ Diamond Weight',
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
            'kji_gemstone' => [
                'type'                      => 'int',
                'label'                     => 'Gemstone',
                'input'                     => 'multiselect',
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
            'kji_gemstone_weight' => [
                'type'                      => 'varchar',
                'label'                     => 'Gemstone Weight',
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
            'kji_gemstone_quantity' => [
                'type'                      => 'varchar',
                'label'                     => 'Gemstone Quantity',
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
            'kji_gemstone_price' => [
                'type'                      => 'varchar',
                'label'                     => 'Gemstone Price',
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
            'kji_gross_weight' => [
                'type'                      => 'varchar',
                'label'                     => 'Gross Weight',
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
            'kji_other_material' => [
                'type'                      => 'varchar',
                'label'                     => 'Other Material',
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
            'kji_making_charges' => [
                'type'                      => 'varchar',
                'label'                     => 'Making Charges',
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
            'kji_discount_price' => [
                'type'                      => 'varchar',
                'label'                     => 'Discount Price',
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
            'kji_out_of_stock_qty' => [
                'type'                      => 'varchar',
                'label'                     => 'Out of Stock Qty',
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
