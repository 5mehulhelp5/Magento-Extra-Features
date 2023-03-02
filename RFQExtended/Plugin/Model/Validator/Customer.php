<?php
/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
namespace Codilar\RFQExtended\Plugin\Model\Validator;

use Magento\NegotiableQuote\Model\Validator\Customer as Subject;
use Magento\NegotiableQuote\Model\Validator\ValidatorResult;
use Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory;

/**
 * Class Customer
 */
class Customer
{
    /**
     * @var ValidatorResultFactory
     */
    private ValidatorResultFactory $validatorResultFactory;

    /**
     * @param ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        ValidatorResultFactory $validatorResultFactory
    ) {
        $this->validatorResultFactory = $validatorResultFactory;
    }

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param $data
     * @return ValidatorResult
     */
    public function aroundValidate(Subject $subject, callable $proceed, $data)
    {
        $result = $this->validatorResultFactory->create();
        if (empty($data['quote'])) {
            return $result;
        }
        $quote = $data['quote'];
        $customer = $quote->getCustomer();
        if (!$customer->getExtensionAttributes()
            || !$customer->getExtensionAttributes()->getCompanyAttributes()
            || !$customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        ) {
            return $result;
        }

        return $proceed($data);
    }
}
