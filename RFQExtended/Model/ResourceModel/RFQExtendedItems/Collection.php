<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Model\ResourceModel\RFQExtendedItems;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct(){
        $this->_init("Codilar\RFQExtended\Model\RFQExtendedItems","Codilar\RFQExtended\Model\ResourceModel\RFQExtendedItems");
    }
}
