<?php

namespace Codilar\ProductCsv\Controller\productcsv;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    /**
     * @var PageFactory
     * 
     */
    protected $productModel;
    protected $imageProcessor;
    protected $pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->productModel = $productModel;
        $this->imageProcessor = $imageProcessor;
    }

    public function execute()
    { 
        $sku= 'ABC1087';

        $productId =  $this->productModel->getIdBySku($sku);
        $product = $this->productModel->load( $productId );
        $gallery = $product->getMediaGalleryImages();
        foreach($gallery as $image){
            $this->imageProcessor->removeImage($product,$image->getFile());
        }
        $product->save();
        var_dump("sh");
        die();
      
    }
      
    

}
