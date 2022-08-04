<?php

namespace Prajwal\Assignmentnew4\Plugin;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General as Subject;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class General
{
    /** @var LocatorInterface */
    protected $locator;
    /**
     * @param LocatorInterface $locator
     */
    public function __construct(
        LocatorInterface $locator
    ) {
        $this->locator = $locator;
    }
    /**
     * To  modify the Product Status
     *
     * @param Subject $subject
     * @param array $data
     * @return array $data
     */
    public function afterModifyData(Subject $subject, array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        if (!$modelId) {
            $data[$modelId][$subject::DATA_SOURCE_DEFAULT][ProductAttributeInterface::CODE_STATUS] = 2;
        }
        return $data;
    }
}
