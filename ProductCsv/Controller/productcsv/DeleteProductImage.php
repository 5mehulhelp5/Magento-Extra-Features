<?php

namespace Codilar\ProductCsv\Controller\productcsv;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class DeleteProductImage extends Action
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $sku= 'ABC1087';
        $product = Mage::getModel ( 'catalog/product' );
        $product_id = $product->getIdBySku ( $sku);
        $product->load ( $product_id );
         
        /**
         * BEGIN REMOVE EXISTING MEDIA GALLERY
         */
        $attributes = $product->getTypeInstance ()->getSetAttributes ();
        if (isset ( $attributes ['media_gallery'] )) {
            $gallery = $attributes ['media_gallery'];
            //Get the images
            $galleryData = $product->getMediaGallery ();
            foreach ( $galleryData ['images'] as $image ) {
                //If image exists
                if ($gallery->getBackend ()->getImage ( $product, $image ['file'] )) {
                    $gallery->getBackend ()->removeImage ( $product, $image ['file'] );
                }
            }
            $product->save ();
        }
        var_dump("deleted");
        die();
    }
}
