<?php

/**
 * @package     Codilar Technologies
 * @author      Prajwal Joshi
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\ExtraFeatures\Controller\Adminhtml\CategoryImage;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class Import extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var CategoryFactory
     */
    protected CategoryFactory $categoryFactory;

    /**
     * @var Category
     */
    protected Category $category;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var WriteInterface
     */
    protected WriteInterface $mediaDirectory;


    /**
     * Import Constructor
     *
     * @param Context $context
     * @param CategoryFactory $categoryFactory
     * @param Category $category
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        Category $category,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->categoryFactory = $categoryFactory;
        $this->category = $category;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }


    /**
     * Save the Images from csv Data
     *
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $imageData = $this->getRequest()->getParam('data');
        $imageData = json_decode($imageData,true);
        foreach ($imageData as $data){
            $imagePath = $this->writeImage($data['image_url']);
            $this->saveCategoryImage($data['category_id'], $imagePath, $data['store_id']);
        }
        return $result->setData(['data'=> 'Imported Successfully']);
    }


    /**
     * Save Category Image by ID
     *
     * @param $categoryId
     * @param $imagePath
     * @param $storeId
     * @return void
     * @throws Exception
     */
    public function saveCategoryImage($categoryId, $imagePath, $storeId)
    {
        $category = $this->categoryFactory->create();
        $this->category->load($category, $categoryId);
        $category->setStoreId($storeId);
        $category->setImage($imagePath);
        $this->category->save($category);
    }


    /**
     * Write the given Category Image in Media and return the file Path

     * @param $image
     * @return string
     * @throws FileSystemException
     */
    protected function writeImage($image)
    {
        $imageName = basename($image);
        $filePath = 'catalog/category/'.$imageName;
        $this->mediaDirectory->writeFile($filePath,file_get_contents($image));
        $this->mediaDirectory->changePermissions($filePath, 0777);
        return '/media/'. $filePath;
    }
}
