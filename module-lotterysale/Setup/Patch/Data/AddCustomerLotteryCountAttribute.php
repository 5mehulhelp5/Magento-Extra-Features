<?php

namespace Casio\LotterySale\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class AddCustomerLotteryCountAttribute implements DataPatchInterface
{
    const CUSTOM_ATTRIBUTES = [
        [
            'code' => 'casio_lottery_applicated_count',
            'label' => 'Casio Lottery Applicated Count',
            'sort_order' => 610
        ],
        [
            'code' => 'casio_lottery_win_count',
            'label' => 'Casio Lottery Win Count',
            'sort_order' => 620
        ]
    ];
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * AddCustomerLotteryAttributes constructor.
     *
     * @param Config              $eavConfig
     * @param EavSetupFactory     $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @return AddCustomerLotteryCountAttributes|void
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        foreach (self::CUSTOM_ATTRIBUTES as $attribute) {
            $this->setCustomAttribute($attribute);
        }
    }

    /**
     * @param $attribute
     * @throws LocalizedException
     */
    private function setCustomAttribute($attribute)
    {
        $eavSetup = $this->eavSetupFactory->create();
        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $eavSetup->addAttribute('customer', $attribute['code'], [
            'type'             => 'int',
            'label'            => $attribute['label'],
            'visible'          => false,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'global'           => true,
            'default'          => 0,
            'visible_on_front' => false,
            'sort_order'       => $attribute['sort_order'],
            'position'         => $attribute['sort_order']
        ]);
        $customAttribute = $this->eavConfig->getAttribute('customer', $attribute['code']);

        $customAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer']
        ]);
        $customAttribute->save();
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
