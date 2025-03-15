<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\Postloginhome\Block\Index;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Company\Model\CompanyContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Index extends Template
{
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param StoreManagerInterface $storeManager
     * @param CompanyContext $companyContext
     * @param array $data [optional]
     */

    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        StoreManagerInterface $storeManager,
        CompanyContext $companyContext,
        array $data = []
    )
    {
        $this->userContext = $userContext;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
        $this->companyContext = $companyContext;
    }

    /**
     * @return bool
     */
    public function isCustomerLogin(): bool
    {
        if($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        if($this->isCustomerLogin()) {
            return $this->userContext->getUserId();
        } else {
            return null;
        }
    }

    /**
     * Get baseurl
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Return RecentlyViewed Product Block
     *
     * @return |null
     * @throws LocalizedException
     */
    public function getRecentlyViewedBlock()
    {
        $recentlyViewedProducts = null;
        if ($this->companyContext->getCustomerId()) {
            $recentlyViewedProducts = $this->getLayout()
                ->createBlock('Codilar\PeersProducts\Block\View\RecentlyViewedProduct')
                ->setTemplate('Codilar_EgcSupply::home/slider.phtml')
                ->setBlockId('recently_viewed')
                ->toHtml();
        }
        return $recentlyViewedProducts;
    }

    /**
     * Return the RecommendedForYou Block
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRecommendedForYou()
    {
        $recommendedForYou = null;
        if ($this->getCustomerId()) {
            $recommendedForYou = $this->getLayout()
                ->createBlock('Codilar\RecommendedForYou\Block\View\RecommendedSection')
                ->setTemplate('Codilar_EgcSupply::home/slider.phtml')
                ->setBlockId('recommended-for-you')
                ->toHtml();
        }
        return $recommendedForYou;
    }

    /**
     * Return the Peers Product Block
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPeerViewedProductsBlock()
    {
        $peersViewedProducts = null;
        if ($this->companyContext->isCurrentUserCompanyUser()) {
            $peersViewedProducts = $this->getLayout()
                ->createBlock('Codilar\PeersProducts\Block\View\PeersProduct')
                ->setTemplate('Codilar_EgcSupply::home/slider.phtml')
                ->setBlockId('peers-slider')
                ->toHtml();
        }
        return $peersViewedProducts;
    }
}
