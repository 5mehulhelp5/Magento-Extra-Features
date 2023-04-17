<?php


namespace Codilar\Catalog\Ui\DataProvider;


use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Authorization;

class ProductForm extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;
    /**
     * @var Authorization
     */
    private $authorization;

    public function __construct(
        LocatorInterface $locator,
        RequestInterface $request,
        Authorization $authorization,
        LayoutFactory $layoutFactory
    ) {
        $this->locator = $locator;
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->authorization = $authorization;
    }

    public function modifyMeta(array $meta)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $resourceId = 'Codilar_Catalog::Inventory';
        $isAuthorize =  $this->authorization->isAllowed($resourceId);
        $logger->info($isAuthorize);
        if (!$isAuthorize) {
            $meta["custom_options"] = [
                "arguments" => [
                    "data" => [
                        "config" => [
                            "componentType" => "fieldset",
                            "collapsible" => false,
                            "sortOrder" => 1,
                            'opened' => false,
                            'canShow' => false,
                            'visible' => false
                        ]
                    ]
                ]
            ];
        }
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        return (string)$this->request->getParam('type', $this->locator->getProduct()->getTypeId());
    }
}
