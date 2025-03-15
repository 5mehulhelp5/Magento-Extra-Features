<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\PeersProducts\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\PeersProducts\Model\ResourceModel\RecentlyViewedProduct as ResourceModel;

class RecentlyViewedProduct extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
