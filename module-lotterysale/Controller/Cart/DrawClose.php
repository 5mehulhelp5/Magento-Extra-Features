<?php

namespace Casio\LotterySale\Controller\Cart;

use Casio\LotterySale\Controller\Action;
use Casio\LotterySale\Model\LotteryApplication;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class DrawClose extends Action
{
    /**
     * @return ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $status = $this->validateLottery();
        if ($status == LotteryApplication::STATUS_NOT_LOTTERY) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        } elseif ($status == LotteryApplication::STATUS_SATISFY) {
            return $this->_redirect('*/*/drawnotice', ['sku' => $this->getRequest()->getParam('sku')]);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Lottery sales application'));

        return $resultPage;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @throws \Magento\Framework\Exception\NotFoundException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->validateAvailable()) {
            return $this->goNoRoutePage();
        }
        if (!$this->getSession()->isLoggedIn()) {
            $this->casioSession->setUrl($this->_url->getUrl('*/*/drawnotice', ['sku' => $this->getRequest()->getParam('sku')]));
            return $this->_redirect($this->clientCasioId->createAuthUrl());
        }

        return parent::dispatch($request);
    }
}
