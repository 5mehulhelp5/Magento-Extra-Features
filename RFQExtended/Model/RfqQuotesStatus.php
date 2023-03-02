<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Model;

class RfqQuotesStatus
{
    const QUOTE_STATUS_OPEN_VALUE = 1 ;

    const QUOTE_STATUS_OPEN_LABEL = 'Open' ;

    const QUOTE_STATUS_QUOTE_RECEIVED_VALUE = 2 ;

    const QUOTE_STATUS_QUOTE_RECEIVED_LABEL = 'Quote Received' ;

    const QUOTE_STATUS_CLOSED_VALUE = 0 ;

    const QUOTE_STATUS_CLOSED_LABEL = 'Closed' ;

    /**
     * Return the All Quote Status
     *
     * @return array
     */
    public function getAllQuoteStatus()
    {
        return $allStatus = [
                        ['label' => self::QUOTE_STATUS_OPEN_LABEL, 'value' => self::QUOTE_STATUS_OPEN_VALUE],
                        ['label' => self::QUOTE_STATUS_QUOTE_RECEIVED_LABEL, 'value' => self::QUOTE_STATUS_QUOTE_RECEIVED_VALUE],
                        ['label' => self::QUOTE_STATUS_CLOSED_LABEL, 'value' => self::QUOTE_STATUS_CLOSED_VALUE]
                    ];
    }
}
