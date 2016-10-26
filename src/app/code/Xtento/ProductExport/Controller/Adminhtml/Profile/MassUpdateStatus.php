<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-05-30T12:34:18+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/MassUpdateStatus.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

class MassUpdateStatus extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * Mass update status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $ids = $this->getRequest()->getParam('profile');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select profiles to update.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        try {
            foreach ($ids as $id) {
                $model = $this->profileFactory->create();
                $model->load($id);
                $model->setEnabled($this->getRequest()->getParam('enabled'));
                $model->save();
            }
            $this->messageManager->addSuccessMessage(
                __('Total of %1 profile(s) were successfully updated.', count($ids))
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}