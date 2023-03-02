<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\PeersProducts\Controller\Product;

use Magento\Framework\App\ActionInterface;
use Magento\Company\Model\CompanyContext;
use Magento\Framework\App\RequestInterface;
use Codilar\PeersProducts\Model\Product\ViewedProductData;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Update implements ActionInterface
{
    /**
     * @var CompanyContext
     */
    private CompanyContext $companyContext;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ViewedProductData
     */
    private ViewedProductData $viewedProductData;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * Update constructor.
     * @param CompanyContext $companyContext
     * @param RequestInterface $request
     * @param ViewedProductData $viewedProductData
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        CompanyContext $companyContext,
        RequestInterface $request,
        ViewedProductData $viewedProductData,
        JsonFactory $resultJsonFactory
    ) {
        $this->companyContext = $companyContext;
        $this->request = $request;
        $this->viewedProductData = $viewedProductData;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Save the Recently Viewed Product
     *
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $productId = $this->request->getParam('productId');
        $result = $this->resultJsonFactory->create();
        if ($this->companyContext->isCurrentUserCompanyUser()) {
            $this->viewedProductData->PeersProductSave($productId);
            $this->viewedProductData->RecentlyViewedProductSave($productId);
            $result->setData(['response' => true]);
        } else {
            $this->viewedProductData->RecentlyViewedProductSave($productId);
            $result->setData(['response' => true]);
        }
        return $result;
    }
}
