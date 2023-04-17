<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ExtendedShopByBrand\Controller\Brand;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class BrandSlider implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * BrandSlider constructor.
     * @param RequestInterface $request
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ){
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Return the json response of the block
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock('Codilar\ExtendedShopByBrand\Block\Brand\BrandSlider')
            ->setTemplate('Codilar_EgcSupply::home/slider.phtml')
            ->toHtml();
        $result->setData(['sliderdata' => $block]);
        return $result;
    }
}
