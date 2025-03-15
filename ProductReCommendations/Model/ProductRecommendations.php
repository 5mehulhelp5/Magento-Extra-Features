<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ProductReCommendations\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\ProductReCommendations\Model\ResourceModel\ProductRecommendations as ResourceModel;

class ProductRecommendations extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
