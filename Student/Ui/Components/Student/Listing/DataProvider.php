<?php

namespace Adobe\Student\Ui\Components\Student\Listing;

use Adobe\Student\Model\ResourceModel\Student\Collection as Collection;
use Adobe\Student\Model\ResourceModel\Student\CollectionFactory as CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    protected $loadedData;

    private $request;

    /**
     * @var Collection
     */
    private $collectionFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $id = $this->request->getParam('student_id');
        if ($id) {
            $items = $this->collectionFactory->create()->addFieldToFilter('student_id', $id)->getItems();
            foreach ($items as $item) {
                $studentData = $item->getData();
                $this->loadedData[$item->getId()] = $studentData;
            }
            return $this->loadedData;
        } else {
            return $this->collection->toArray();
        }
    }
}
