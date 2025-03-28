<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Enrollment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use KalyanUs\Scheme\Helper\Config;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index extends \KalyanUs\Scheme\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @param Context $context
     * @param Config $config
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Config $config,
        RedirectFactory $redirectFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->config = $config;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setUrl($this->_url->getUrl(''));
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
