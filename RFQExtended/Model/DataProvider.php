<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Codilar\RFQExtended\Model\ResourceModel\RFQExtendedForm\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    const DATA_SCOPE_GENERAL = 'general';

    private array $loadedData;

    protected CollectionFactory $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];

        $items = $this->collection->getItems();

        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $this->prepareData($item);
        }

        return $this->loadedData;
    }


    private function prepareData($item)
    {
        return array(
            self::DATA_SCOPE_GENERAL => array(
                'customer_id' => $item->getCustomerId(),
                'quote_name' => $item->getQuoteMessage(),
                'quote_message' => $item->getQuoteMessage(),
                'state' => $item->getState()
            )
        );
    }
}

