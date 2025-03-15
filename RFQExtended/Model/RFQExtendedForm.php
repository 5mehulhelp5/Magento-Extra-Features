<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\RFQExtended\Model;

use Magento\Framework\Model\AbstractModel;

class RFQExtendedForm extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Codilar\RFQExtended\Model\ResourceModel\RFQExtendedForm');
    }
}
