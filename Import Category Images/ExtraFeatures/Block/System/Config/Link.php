<?php

/**
 * @package     Codilar Technologies
 * @author      Prajwal Joshi
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\ExtraFeatures\Block\System\Config;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\View\Element\AbstractBlock;

class Link extends AbstractBlock implements CommentInterface
{
    /**
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $csvFile = $this->_assetRepo->getUrl('Codilar_ExtraFeatures::csv/category_image_sample.csv');
        return "<a href='$csvFile'>Download Sample</a> ";
    }
}
