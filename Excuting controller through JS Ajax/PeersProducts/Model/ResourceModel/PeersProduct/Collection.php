<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\PeersProducts\Model\ResourceModel\PeersProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Codilar\PeersProducts\Model\PeersProduct as Model;
use Codilar\PeersProducts\Model\ResourceModel\PeersProduct as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
