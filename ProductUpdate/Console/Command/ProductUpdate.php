<?php

namespace KalyanUs\ProductUpdate\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use KalyanUs\ProductUpdate\Model\ProductUpdateApi;
class ProductUpdate extends Command
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var ProductUpdateApi
     */
    private ProductUpdateApi $productUpdateApi;

    /**
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductUpdateApi $productUpdateApi
     */
    public function __construct(
        State $state,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        ProductUpdateApi $productUpdateApi
    ) {
        parent::__construct();
        $this->state = $state;
        $this->productUpdateApi = $productUpdateApi;
    }

    protected function configure()
    {
        $this->setName('kalyanus:productupdate:update-products-for-store')
            ->setDescription('Update Products Gold for Kalyan US Store');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            $output->writeln("<error>Unable to set the Area Code!: {$e->getMessage()} </error>");
            return Cli::RETURN_FAILURE;
        }
        $this->productUpdateApi->updateProducts($output);
        return Cli::RETURN_SUCCESS;
    }
}
