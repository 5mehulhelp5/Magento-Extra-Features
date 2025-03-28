<?php

namespace KalyanUs\ProductUpdate\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
class AddKJApiAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'kj_hierarchy_name' => [
                'group'                     => 'KJ API Attributes',
                'type'                      => 'varchar',
                'backend'                   => '',
                'frontend'                  => '',
                'label'                     => 'KJ Hierarchy Name',
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
                'is_visible_on_front'       => false
            ],
            'kj_metalrate' => [
                'group'                     => 'KJ API Attributes',
                'type'                      => 'varchar',
                'label'                     => 'KJ Metal Rate',
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
            'kj_mc_unit' => [
                'group'                     => 'KJ API Attributes',
                'type'                      => 'int',
                'label'                     => 'KJ MC Unit',
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
            'kj_mc_rate' => [
                'group'                     => 'KJ API Attributes',
                'type'                      => 'varchar',
                'label'                     => 'KJ MC Rate',
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
            'kj_mccalcpurity' => [
                'group'                     => 'KJ API Attributes',
                'type'                      => 'int',
                'label'                     => 'KJ MC Calc Purity',
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
            ]
        ];

        foreach ($attributes as $code => $data) {
            $eavSetup->addAttribute(Product::ENTITY, $code, $data);
        }


        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Kalyan Store US');

        if ($attributeSetId) {
            $groupName = 'KJ API Attributes';
            try {
                $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            } catch (\Exception $e) {
                $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName);
                $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            }

            foreach (array_keys($attributes) as $code) {
                $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $code, null);
            }
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
