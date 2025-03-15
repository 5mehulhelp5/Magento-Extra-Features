<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ProductRecommendations\Controller\Product;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;

class Slider implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;
    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * Slider constructor.
     * @param RequestInterface $request
     * @param LayoutInterface $layout
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        RequestInterface $request,
        LayoutInterface $layout,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    )
    {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layout = $layout;
        $this->resultPageFactory = $resultPageFactory;
    }


    /**
     * Display the Slider Template for Product Recommendations
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $layout = $this->layout;
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock('Codilar\ProductRecommendations\Block\Recommendations')
            ->setTemplate('Codilar_EgcSupply::home/slider.phtml')
            ->toHtml();
        $behaviour = $this->getBlockHtml($layout, 'product_recommendations');
        $result->setData(['output' => $block,
            'behaviour' => $behaviour]);
        return $result;
    }

    /**
     * Return the layout for block
     *
     * @param $layout
     * @param $blockName
     * @return string
     */
    private function getBlockHtml($layout, $blockName)
    {
        return $layout->getBlock($blockName) ? $layout->getBlock($blockName)->toHtml() : '';
    }
}
