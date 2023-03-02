<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Block\Header;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Html\Link as CoreLink;

class Link extends CoreLink
{
    const RFQ_REQUEST_URL = 'quick-quote/quote/request';

    const REQUEST_BUTTON_TITTLE = 'Request Quote';


    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl(self::RFQ_REQUEST_URL);
    }

    /**
     * @return Phrase|string
     */
    public function getLabel()
    {
        return __(self::REQUEST_BUTTON_TITTLE);
    }
}
