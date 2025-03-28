<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class CheckLogin extends Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \KalyanUs\Scheme\Model\SchemeQuoteProcess
     */
    protected $schemeQuoteProcess;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customersession;

    /**
     * @var \KalyanUs\Scheme\Helper\Config
     */
    protected $helperConfigScheme;

    /**
     * @var \KalyanUs\Scheme\Helper\Data
     */
    protected $helperDataScheme;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess
     * @param \Magento\Customer\Model\Session $customersession
     * @param \KalyanUs\Scheme\Helper\Config $helperConfigScheme
     * @param \KalyanUs\Scheme\Helper\Data $helperDataScheme
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \KalyanUs\Scheme\Model\SchemeQuoteProcess $schemeQuoteProcess,
        \Magento\Customer\Model\Session $customersession,
        \KalyanUs\Scheme\Helper\Config $helperConfigScheme,
        \KalyanUs\Scheme\Helper\Data $helperDataScheme
    ) {
        $this->coreRegistry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->schemeQuoteProcess = $schemeQuoteProcess;
        $this->customersession =$customersession;
        $this->helperConfigScheme =$helperConfigScheme;
        $this->helperDataScheme =$helperDataScheme;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            if ($this->customersession->isLoggedIn()) {
                if ($this->validateData()) {
                    $post = $this->getRequest()->getPostValue();
                    $post['scheme_name']=$this->helperConfigScheme->getSchemeNameByDuration($post['duration']);
                    $this->schemeQuoteProcess->saveQuote($post);
                    $data=['status'=>true,'redirect_url'=>$this->helperDataScheme->getNewEnrollmentUrl()];
                }
            } else {
                $data=['status'=>false,'isLoggedIn'=>false];
            }
            return $result->setData($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $message = $e->getMessage();
            $data=['status'=>false,'message'=>$message];
            return $result->setData($data);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $data=['status'=>false,'message'=>$message];
            return $result->setData($data);
        }
    }

    /**
     * Validate request data
     *
     * @return bool
     */
    public function validateData()
    {
        if ($this->getRequest()->getMethod()=='POST') {
            $post = $this->getRequest()->getPostValue();
            if ($post['emi_amount']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Emi Amount is Required Field.'));
            }
            if ($post['duration']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Duration is Required Field.'));
            }
            if (!$this->helperDataScheme->validateEmiAmountForScheme($post['duration'], $post['emi_amount'])) {
                list($min,$max)=$this->helperDataScheme->getEmiSchemeRange($post['duration']);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a Emi Amount between '.$min.' and '.$max.'.')
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request.'));
        }
        return true;
    }
}
