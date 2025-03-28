<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Enrollment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Quote extends Action
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
                    $post['scheme']['scheme_name']=$this->helperConfigScheme->getSchemeNameByDuration(
                        $post['scheme']['duration']
                    );
                    $this->schemeQuoteProcess->saveQuote($post['scheme']);
                    $quote=$this->schemeQuoteProcess->getFullQuote();
                    if ($quote!=false) {
                        $data=['status'=>true,'data'=>['quote'=>$quote]];
                        return $result->setData($data);
                    } else {
                        $data=['status'=>false,'message'=>'Something went wrong.Please contact to admin.'];
                    }
                }
            } else {
                $data=['status'=>false,'redirect'=>false];
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
     * Validate data
     *
     * @return bool
     */
    public function validateData()
    {
        if ($this->getRequest()->getMethod()=='POST') {
            $post = $this->getRequest()->getPostValue();
            if ($post['scheme']['emi_amount']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Emi Amount is Required Field.'));
            } else {
                if (($post['scheme']['emi_amount'] % 100) !=0) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Emi Amount is multiply of 100.'));
                }
            }
            // phpcs:ignore
            if (!$this->helperDataScheme->validateEmiAmountForScheme($post['scheme']['duration'], $post['scheme']['emi_amount'])) {
                list($min,$max)=$this->helperDataScheme->getEmiSchemeRange($post['scheme']['duration']);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a Emi Amount between '.$min.' and '.$max.'.')
                );
            }
            if ($post['scheme']['duration']=='') {
                throw new \Magento\Framework\Exception\LocalizedException(__('Duration is Required Field.'));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request.'));
        }
        return true;
    }
}
