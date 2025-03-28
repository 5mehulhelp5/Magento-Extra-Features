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

use Magento\Backend\App\Action;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Update blog post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('enrollment');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('status');
                foreach ($itemIds as $postId) {
                    $post = $this->_objectManager->get(\KalyanUs\Scheme\Model\Enrollment::class)->load($postId);
                    $post->setIsActive($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('scheme/*/index');
    }
}
