<?php
namespace Codilar\ProductCsv\Block;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

class ReadCsv extends \Magento\Framework\View\Element\Template
{
    protected $_csvParser;
    protected $_fileDriver;
    protected $_productRepository;
	
    public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Framework\File\Csv $csvParser,
    \Magento\Framework\Filesystem\Driver\File $fileDriver,
    \Magento\Catalog\Model\ProductRepository $productRepository,
        
        array $data = []
    ) {
	    $this->_csvParser = $csvParser;
        $this->_fileDriver = $fileDriver;
        $this->_productRepository = $productRepository;
        parent::__construct($context,$data);
    }

    /**
     * read data from csv file
     *
     * @return string[]
     */
    public function getCsvFileData()
    {
        $csvData = ['sku'];
        $csvFile = '../var/csv/test_file.csv';
		
		if ($this->_fileDriver->isExists($csvFile)) { 
			$this->_csvParser->setDelimiter(','); 
			$csvData = $this->_csvParser->getData($csvFile);

			  // to get array in key/value pair then use below function
			  // $csvData = $this->_csvParser->getDataPairs($csvFile);
		
			if (count($csvData) > 0) {
				// foreach($csvData as $row => $data) {
				// 	if ($row > 0) { // skip header row
				// 		print_r('hi');
				// 	}
				// }
                return $csvData;
			}
		} 
                else 
                {
                    $this->_logger->info('Csv file not exist at given path.');
                    return __('Csv file not exist at given path.');
                }
    }

    public function getProductBySku($sku)
	{
		return $this->_productRepository->get($sku);
	}

}
