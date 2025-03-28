<?php

namespace KalyanUs\ProductUpdate\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
class ProductParentBarcode implements DataPatchInterface
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
        $eavSetup->addAttribute(
            Product::ENTITY,
            'kj_parent_barcode',
            [
                'group'                     => 'General',
                'type'                      => 'varchar',
                'backend'                   => '',
                'frontend'                  => '',
                'label'                     => 'KJ Parent Barcode',
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
            ]
        );

        // Assign to "Kalyan Store US" attribute set
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Kalyan Store US');

        if ($attributeSetId) {
            $groupName = 'General';
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'kj_parent_barcode', null);
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
