<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Controller\Quote;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Url;

class MyQuotes implements ActionInterface
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
     * MyQuotes constructor.
     * @param PageFactory $pageFactory
     * @param RedirectFactory $resultRedirect
     * @param Url $url
     * @param UserContextInterface $userContext
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $resultRedirect,
        Url $url,
        UserContextInterface $userContext
    ) {
        $this->pageFactory = $pageFactory;
        $this->userContext = $userContext;
        $this->resultRedirect = $resultRedirect;
        $this->url = $url;
    }

    /**
     * View  page action
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $customerId = $this->userContext->getUserId();
        if($customerId) {
            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('My Quotes'));
            return $resultPage;
        } else {
            $url = $this->url->getLoginUrl();
            return $this->resultRedirect->create()->setPath($url);
        }
    }
}
