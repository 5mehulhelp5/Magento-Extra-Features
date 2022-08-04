<?php


namespace Task\Employee\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    /**
     * @var PageFactory
     */
    private PageFactory $pageFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory=$pageFactory;
    }

    /**
     *  Excute
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
