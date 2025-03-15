<?php

namespace Codilar\ProductCsv\Ui\Component;

use Codilar\ProductCsv\Model\ResourceModel\ProductCsv\CollectionFactory;
use Magento\Framework\App\RequestInterface as MagentoRequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class EditDataProvider extends AbstractDataProvider
{
    protected $loadedData;
    /**
     * @var MagentoRequestInterface
     */
    private $request;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param MagentoRequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        MagentoRequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collectionFactory = $collectionFactory;
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }
    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
          $id = $this->request->getParam('entity_id');
          $items = $this->collectionFactory->create()->addFieldToFilter('entity_id', $id)->getItems();

          foreach ($items as $item) {
            $customerData = $item->getData();
            $this->loadedData[$item->getId()] = $customerData;
         }
        return $this->loadedData;
    }
}

