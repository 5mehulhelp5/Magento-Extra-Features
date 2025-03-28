<?php
/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
namespace KalyanUs\Scheme\Controller\Adminhtml\enrollment;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Execute method
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('enrollment');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $post = $this->_objectManager->get(\KalyanUs\Scheme\Model\Enrollment::class)->load($itemId);
                    $post->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('scheme/*/index');
    }
}
