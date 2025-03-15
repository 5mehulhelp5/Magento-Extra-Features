<?php

namespace Casio\LotterySale\Controller\Cart;

use Casio\LotterySale\Controller\Action;
use Casio\LotterySale\Model\LotteryApplication;

class DrawNotice extends Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->validateLottery() == LotteryApplication::STATUS_NOT_LOTTERY || !$this->validateAvailable()) {
            return $this->goNoRoutePage();
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Lottery sales application'));
        return $resultPage;
    }
}
