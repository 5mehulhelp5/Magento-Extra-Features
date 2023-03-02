<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\PeersProducts\Model\Product;

use Magento\Company\Model\CompanyContext;
use Magento\Company\Api\CompanyManagementInterface;
use Codilar\Division\Model\CustomerDivisionProvider;
use Codilar\PeersProducts\Model\PeersProductFactory as PeersModelFactory;
use Codilar\PeersProducts\Model\ResourceModel\PeersProduct as PeersResourceModel;
use Codilar\PeersProducts\Model\ResourceModel\PeersProduct\Collection as PeersCollection;
use Codilar\PeersProducts\Model\RecentlyViewedProductFactory as RecentlyViewedModelFactory;
use Codilar\PeersProducts\Model\ResourceModel\RecentlyViewedProduct as RecentlyViewedResourceModel;
use Codilar\PeersProducts\Model\ResourceModel\RecentlyViewedProduct\Collection as RecentlyViewedCollection;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ViewedProductData
{
    /**
     * @var CompanyContext
     */
    private CompanyContext $companyContext;
    /**
     * @var CompanyManagementInterface
     */
    private CompanyManagementInterface $companyManagement;
    /**
     * @var CustomerDivisionProvider
     */
    private CustomerDivisionProvider $customerDivisionProvider;
    /**
     * @var PeersModelFactory
     */
    private PeersModelFactory $peersModelFactory;
    /**
     * @var PeersResourceModel
     */
    private PeersResourceModel $peersResourceModel;
    /**
     * @var PeersCollection
     */
    private PeersCollection $peersCollection;
    /**
     * @var RecentlyViewedModelFactory
     */
    private RecentlyViewedModelFactory $recentlyViewedFactory;
    /**
     * @var RecentlyViewedResourceModel
     */
    private RecentlyViewedResourceModel $recentlyViewedResourceModel;
    /**
     * @var RecentlyViewedCollection
     */
    private RecentlyViewedCollection $recentlyViewedCollection;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;


    /**
     * ViewedProductData constructor.
     * @param CompanyContext $companyContext
     * @param CompanyManagementInterface $companyManagement
     * @param CustomerDivisionProvider $customerDivisionProvider
     * @param PeersModelFactory $peersModelFactory
     * @param PeersResourceModel $peersResourceModel
     * @param PeersCollection $peersCollection
     * @param RecentlyViewedModelFactory $recentlyViewedFactory
     * @param RecentlyViewedResourceModel $recentlyViewedResourceModel
     * @param RecentlyViewedCollection $recentlyViewedCollection
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        CompanyContext $companyContext,
        CompanyManagementInterface $companyManagement,
        CustomerDivisionProvider $customerDivisionProvider,
        PeersModelFactory $peersModelFactory,
        PeersResourceModel $peersResourceModel,
        PeersCollection $peersCollection,
        RecentlyViewedModelFactory $recentlyViewedFactory,
        RecentlyViewedResourceModel $recentlyViewedResourceModel,
        RecentlyViewedCollection $recentlyViewedCollection,
        JsonFactory $resultJsonFactory
    ) {
        $this->companyContext = $companyContext;
        $this->companyManagement = $companyManagement;
        $this->customerDivisionProvider = $customerDivisionProvider;
        $this->peersModelFactory = $peersModelFactory;
        $this->peersResourceModel = $peersResourceModel;
        $this->peersCollection = $peersCollection;
        $this->recentlyViewedFactory = $recentlyViewedFactory;
        $this->recentlyViewedResourceModel = $recentlyViewedResourceModel;
        $this->recentlyViewedCollection = $recentlyViewedCollection;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Save the Peers Product Id and other data
     *
     * @param $productId
     * @return Json
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function PeersProductSave($productId)
    {
        $result = $this->resultJsonFactory->create();
        $customerId = $this->companyContext->getCustomerId();
        $peersModel = $this->peersModelFactory->create();
        $peersProducts = $this->peersCollection
            ->addFieldToFilter('product_id',$productId)
            ->addFieldToFilter('customer_id',$customerId);
        if (!count($peersProducts)) {
            if ($this->companyContext->isCurrentUserCompanyUser()) {
                if ($this->customerDivisionProvider->getDivisionUniqueId()) {
                    $peersModel->setData([
                        'product_id' => $productId,
                        'customer_id' => $this->companyContext->getCustomerId(),
                        'company_id' => $this->companyManagement->getByCustomerId($customerId)->getId(),
                        'division_id' => $this->customerDivisionProvider->getDivisionUniqueId()
                    ]);
                } else {
                    $peersModel->setData([
                        'product_id' => $productId,
                        'customer_id' => $this->companyContext->getCustomerId(),
                        'company_id' => $this->companyManagement->getByCustomerId($customerId)->getId()
                    ]);
                }
                $this->peersResourceModel->save($peersModel);
                $result->setData(['response' => true]);
            }
        }
        return $result;
    }

    /**
     * Save the Recently Viewed Product Id and other data
     *
     * @param $productId
     * @return Json
     * @throws AlreadyExistsException
     */
    public function RecentlyViewedProductSave($productId)
    {
        $result = $this->resultJsonFactory->create();
        $customerId = $this->companyContext->getCustomerId();
        $recentlyViewedModel = $this->recentlyViewedFactory->create();
        $recentlyViewedProducts = $this->recentlyViewedCollection
            ->addFieldToFilter('product_id',$productId)
            ->addFieldToFilter('customer_id',$customerId);
        if (!count($recentlyViewedProducts)) {
            if ($customerId) {
                $recentlyViewedModel->setData([
                    'product_id' => $productId,
                    'customer_id' => $this->companyContext->getCustomerId()
                ]);
                $this->recentlyViewedResourceModel->save($recentlyViewedModel);
                $result->setData(['response' => true]);
            }
        }
        return $result;
    }
}
