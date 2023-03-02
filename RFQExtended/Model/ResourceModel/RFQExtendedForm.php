<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RFQExtendedForm extends AbstractDb
{
    public function _construct()
    {
        $this->_init("egcsupply_rfqextended_form","entity_id");
    }
}
