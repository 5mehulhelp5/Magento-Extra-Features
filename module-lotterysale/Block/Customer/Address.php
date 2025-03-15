<?php

namespace Casio\LotterySale\Block\Customer;

class Address extends \Magento\Customer\Block\Address\Edit
{
    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($defaultBilling = $this->getCustomer()->getDefaultBilling()) {
            $this->getRequest()->setParams(['id' => $defaultBilling]);
        }
        parent::_prepareLayout();

        return $this;
    }

    /**
     * Generate name block html.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\Magento\Customer\Block\Widget\Name::class)
            ->setTemplate('Casio_LotterySale::widget/name.phtml')
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'checkout/cart/drawapply',
            ['_secure' => true]
        );
    }

    /**
     * Get product lottery sku
     *
     * @return mixed
     */
    public function getProductSku()
    {
        return $this->getRequest()->getParam('sku');
    }
}
