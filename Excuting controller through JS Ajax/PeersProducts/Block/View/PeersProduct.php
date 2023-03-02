<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\PeersProducts\Block\View;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory;
use Codilar\PeersProducts\Model\ResourceModel\PeersProduct\CollectionFactory as ViewedProductsCollectionFactory;
use Magento\Company\Model\CompanyContext;
use Codilar\Division\Model\CustomerDivisionProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Codilar\EgcSupply\Helper\Product;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\ViewModel\Product\OptionsData;

class PeersProduct extends Template
{

    /**
     * @var ViewedProductsCollectionFactory
     */
    private ViewedProductsCollectionFactory $viewedCollectionFactory;
    /**
     * @var CompanyContext
     */
    private CompanyContext $companyContext;
    /**
     * @var CustomerDivisionProvider
     */
    private CustomerDivisionProvider $customerDivisionProvider;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var CompanyManagementInterface
     */
    private CompanyManagementInterface $companyManagement;
    /**
     * @var Product
     */
    private Product $productHelper;
    /**
     * @var ListProduct
     */
    private ListProduct $listProduct;
    /**
     * @var OptionsData
     */
    private OptionsData $optionsData;


    /**
     * PeersProduct constructor.
     * @param Context $context
     * @param ViewedProductsCollectionFactory $viewedCollectionFactory
     * @param CustomerDivisionProvider $customerDivisionProvider
     * @param CompanyContext $companyContext
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param CompanyManagementInterface $companyManagement
     * @param Product $productHelper
     * @param ListProduct $listProduct
     * @param OptionsData $optionsData
     * @param array $data
     */
    public function __construct(
        Context $context,
        ViewedProductsCollectionFactory $viewedCollectionFactory,
        CustomerDivisionProvider $customerDivisionProvider,
        CompanyContext $companyContext,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        CompanyManagementInterface $companyManagement,
        Product $productHelper,
        ListProduct $listProduct,
        OptionsData $optionsData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewedCollectionFactory = $viewedCollectionFactory;
        $this->customerDivisionProvider = $customerDivisionProvider;
        $this->companyContext = $companyContext;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->companyManagement = $companyManagement;
        $this->productHelper = $productHelper;
        $this->listProduct = $listProduct;
        $this->optionsData = $optionsData;
    }

    /**
     * Return the PeersProduct Collection
     *
     * @return false|ProductSearchResultsInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductCollection()
    {
        $collection = $this->viewedCollectionFactory->create();
        if ($this->companyContext->isCurrentUserCompanyUser()){
            if ($this->customerDivisionProvider->getDivisionUniqueId()){
                $products= $collection->addFieldToSelect('product_id')
                    ->addFieldToFilter('division_id',$this->customerDivisionProvider->getDivisionUniqueId())
                    ->addFieldToFilter('customer_id',['neq' => $this->companyContext->getCustomerId()])
                    ->setOrder('entity_id','DESC')->setPageSize(15)->toArray();
                $productIds = $this->getProductIds($products);
                $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                    'entity_id', $productIds, 'in'
                )->create();
                return $this->productRepository->getList($searchCriteria);
            } else {
                $customerId = $this->companyContext->getCustomerId();
                $companyId = $this->companyManagement->getByCustomerId($customerId)->getId();
                $products = $collection->addFieldToSelect('product_id')
                    ->addFieldToFilter('company_id',$companyId)
                    ->addFieldToFilter('customer_id',['neq' => $this->companyContext->getCustomerId()])
                    ->setOrder('entity_id','DESC')->setPageSize(15)->toArray();
                $productIds = $this->getProductIds($products);
                $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                    'entity_id', $productIds, 'in'
                )->create();
                return $this->productRepository->getList($searchCriteria);
            }
        } else {
            return false;
        }
    }

    /**
     * Return the Product Ids
     *
     * @param $products
     * @return array
     */
    public function getProductIds($products)
    {
        $productIds = [];
        for ($i=0;$i<count($products['items']);$i++){
            $productIds[$i] = $products['items'][$i]['product_id'];
        }
        return $productIds;
    }

    /**
     * Return the Product Incremental quantity
     *
     * @param $productId
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getIncrementQty($productId)
    {
        if ($this->productHelper->getMinQtyIncrements($productId)){
            return $this->productHelper->getMinQtyIncrements($productId);
        } else{
            return false;
        }
    }

    /**
     * Return the AddToCart parameters
     *
     * @param $product
     * @return array
     */
    public function getAddToCartPostParams($product)
    {
        return $this->listProduct->getAddToCartPostParams($product);
    }

    /**
     * Return the Product OptionData
     *
     * @param $product
     * @return array
     */
    public function getOptionsData($product)
    {
        return $this->optionsData->getOptionsData($product);
    }

    /**
     * Return the Base Url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getBaseUrl();
    }

    /**
     * Return the Slider Title
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSliderTitle(){
        if ($this->companyContext->isCurrentUserCompanyUser()) {
            return 'Products your peers are interested in';
        }
    }
}
