<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Codilar\RFQExtended\Model\RfqQuotesStatus;

class State extends Column
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        array $components = [],
        array $data = []
    )
    {
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                $schemeValue = (int)$item[$name];

                $item[$name] = match ($schemeValue) {
                    RfqQuotesStatus::QUOTE_STATUS_OPEN_VALUE => RfqQuotesStatus::QUOTE_STATUS_OPEN_LABEL,
                    RfqQuotesStatus::QUOTE_STATUS_QUOTE_RECEIVED_VALUE => RfqQuotesStatus::QUOTE_STATUS_QUOTE_RECEIVED_LABEL,
                    RfqQuotesStatus::QUOTE_STATUS_CLOSED_VALUE => RfqQuotesStatus::QUOTE_STATUS_CLOSED_LABEL,
                };
            }
        }

        return $dataSource;
    }
}
