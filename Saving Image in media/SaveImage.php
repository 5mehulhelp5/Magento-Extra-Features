<?php

namespace Codilar\ExtraFeatures\Controller\Adminhtml\CategoryImage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class SaveImage extends Action
{


    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDirectory;

    /**
     * @var Filesystem
     */
    private Filesystem $fileSystem;

    public function __construct(
        Context $context,
        Filesystem $fileSystem,
    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
        $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function execute()
    {
        $image = '/home/codilar/Downloads/Categories/MyOrder.png';
        $imageName = basename($image);
        $filePath = 'catalog/category/'.$imageName;
        $this->mediaDirectory->writeFile($filePath,file_get_contents($image));
        $this->mediaDirectory->changePermissions($filePath, 0777);
        var_dump('hello');
        die();
    }
}
