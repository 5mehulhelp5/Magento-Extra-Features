<?php

namespace Casio\LotterySale\Controller\Cart;

use Casio\LotterySale\Controller\Action;
use Casio\LotterySale\Model\LotteryApplication;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Draw extends Action
{
    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $status = $this->validateLottery();
        switch ($status) {
            case LotteryApplication::STATUS_CLOSE:
            case LotteryApplication::STATUS_APPLIED:
                return $this->goDrawClosePage();
            case LotteryApplication::STATUS_NOT_LOTTERY:
                return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
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
