<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\ExtendedShopByBrand\Plugin\Helper;

class Data
{

    /**
     * AroundPlugin for Meetanshi ShopByBrand Sql  Bug Fix
     *
     * @param \Meetanshi\ShopbyBrand\Helper\Data $subject
     * @param callable $proceed
     * @param $char
     * @return string
     */
    public function aroundCheckCharacter(\Meetanshi\ShopbyBrand\Helper\Data $subject, callable $proceed, $char)
    {
        $specialChar = [
            '!',
            '"',
            '#',
            '$',
            '&',
            '(',
            ')',
            '*',
            '+',
            ',',
            '-',
            '.',
            '/',
            ':',
            ';',
            '<',
            '=',
            '>',
            '?',
            '@',
            '[',
            ']',
            '^',
            '_',
            '`',
            '{',
            '|',
            '}',
            '~'
        ];
        if (in_array($char, $specialChar, true)) {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . "'" . $char . "%'";
        } elseif ($char === "'") {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . '"' . $char . '%"';
        } else {
            $sqlCond = 'CAST((IF(tsv.value_id > 0, tsv.value, tdv.value)) AS BINARY) REGEXP BINARY ' . "'^" . $char . "'";
        }
        return $sqlCond;
    }
}
