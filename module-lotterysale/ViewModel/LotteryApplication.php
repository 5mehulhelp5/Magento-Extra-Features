<?php
namespace Casio\LotterySale\ViewModel;

use Casio\LotterySale\Model\Config\Source\Status as SourceStatus;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Helper\Data as CatalogHelperData;
use Magento\Catalog\Helper\Image as CatalogHelperImage;

/**
 * Class LotteryApplication
 * Casio\LotterySale\ViewModel
 */
class LotteryApplication implements ArgumentInterface
{
    /** @var SourceStatus  */
    protected $_statusSource;

    /** @var CatalogHelperData  */
    protected $_catalogHelperData;

    /** @var CatalogHelperImage  */
    protected $_catalogHelperImage;

    /**
     * LotteryApplication constructor.
     * @param SourceStatus $statusSource
     * @param CatalogHelperData $catalogHelperData
     * @param CatalogHelperImage $catalogHelperImage
     */
    public function __construct(
        SourceStatus $statusSource,
        CatalogHelperData $catalogHelperData,
        CatalogHelperImage $catalogHelperImage
    ) {
        $this->_statusSource = $statusSource;
        $this->_catalogHelperData = $catalogHelperData;
        $this->_catalogHelperImage = $catalogHelperImage;
    }

    /**
     * @return array
     */
    public function getStatusOptions()
    {
        return $this->_statusSource->toArray();
    }

    /**
     * @return CatalogHelperData
     */
    public function getCatalogHelperData()
    {
        return $this->_catalogHelperData;
    }

    /**
     * @return CatalogHelperImage
     */
    public function getCatalogHelperImage()
    {
        return $this->_catalogHelperImage;
    }
}
