<?php


namespace Codilar\Catalog\Plugin;

use Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Form\Modifier\SourceItems as Subject;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Framework\Authorization;


class SourceItems
{
    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;
    /**
     * @var LocatorInterface
     */
    private $locator;
    /**
     * @var Authorization
     */
    private $authorization;

    public function __construct(
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        Authorization $authorization
    )
    {
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->authorization = $authorization;
    }

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param array $meta
     */
    public function aroundModifyMeta(Subject $subject, callable $proceed, array $meta)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/prajwal.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $product = $this->locator->getProduct();
        $resourceId = 'Codilar_Catalog::Inventory';
        $isAuthorize =  $this->authorization->isAllowed($resourceId);

        $logger->info($isAuthorize);

        if(!$isAuthorize) {
            $logger->info('if');
            $meta['sources'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'visible' => false,
                        ],
                    ],
                ]
            ];
        } else {
            $logger->info('else');
            $meta['sources'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'visible' => !$this->isSingleSourceMode->execute() &&
                                $this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()),
                        ],
                    ],
                ]
            ];
        }

        return $meta;
    }
}
