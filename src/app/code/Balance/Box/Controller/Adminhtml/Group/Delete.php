<?php

namespace Balance\Box\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Balance_Box::delete');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('Balance\Box\Model\Group');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The box has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a box to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}