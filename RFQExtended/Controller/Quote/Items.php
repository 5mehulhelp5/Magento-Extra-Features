<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Controller\Quote;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Url;

class Items implements ActionInterface
{
    /**
     * @var PageFactory
     */
    private PageFactory $pageFactory;
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $resultRedirect;
    /**
     * @var Url
     */
    private Url $url;

    /**
     * Item constructor.
     * @param PageFactory $pageFactory
     * @param RedirectFactory $resultRedirect
     * @param UserContextInterface $userContext
     * @param Url $url
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $resultRedirect,
        UserContextInterface $userContext,
        Url $url
    ) {
        $this->pageFactory = $pageFactory;
        $this->userContext = $userContext;
        $this->resultRedirect = $resultRedirect;
        $this->url = $url;
    }

    /**
     * Quote Item  page action
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        $customerId = $this->userContext->getUserId();
        if($customerId) {
            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Quote Items'));
            return $resultPage;
        } else {
            $url = $this->url->getLoginUrl();
            return $this->resultRedirect->create()->setPath($url);
        }
    }
}
