<?php
namespace Casio\LotterySale\Model\Config\Source\Email;

use Magento\Framework\Option\ArrayInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;

/**
 * Class Template
 * Casio\LotterySale\Model\Config\Source\Email
 */
class Template implements ArrayInterface
{
    /** @var string  */
    protected $templateIdentifier = 'lottery_application_winner_email_template';

    /** @var CollectionFactory  */
    protected $_collectionFactory;

    /** @var Config  */
    protected $_emailConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CollectionFactory $collectionFactory
     * @param Config $emailConfig
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Config $emailConfig
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_emailConfig = $emailConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $array = [
            'value' => $this->templateIdentifier,
            'label' => 'Default email template'
        ];
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter(
                'orig_template_code',
                ['eq' => $this->templateIdentifier]
            )
            ->load();
        $options = $collection->toOptionArray();
        array_unshift($options, $array);
        return $options;
    }
}
