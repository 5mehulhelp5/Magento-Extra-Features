<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\PeersProducts\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PeersProduct extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('company_viewed_products', 'entity_id');
    }
}
