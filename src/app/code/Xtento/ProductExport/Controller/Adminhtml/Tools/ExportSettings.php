<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-14T15:37:57+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Tools/ExportSettings.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Tools;

class ExportSettings extends \Xtento\ProductExport\Controller\Adminhtml\Tools
{
    /**
     * Export action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     * @throws \Exception
     */
    public function execute()
    {
        $profileIds = $this->getRequest()->getPost('profile_ids', []);
        $destinationIds = $this->getRequest()->getPost('destination_ids', []);
        if (empty($profileIds) && empty($destinationIds)) {
            $this->messageManager->addErrorMessage(__('No profiles / destinations to export specified.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $randIdPrefix = rand(100000, 999999);
        $exportData = [];
        $exportData['profiles'] = [];
        $exportData['destinations'] = [];
        foreach ($profileIds as $profileId) {
            $profile = $this->profileFactory->create()->load($profileId);
            $profile->unsetData('profile_id');
            $profileDestinationIds = $profile->getData('destination_ids');
            $newDestinationIds = [];
            foreach (explode("&", $profileDestinationIds) as $destinationId) {
                if (is_numeric($destinationId)) {
                    $newDestinationIds[] = substr($randIdPrefix . $destinationId, 0, 8);
                }
            }
            $profile->setData('new_destination_ids', implode("&", $newDestinationIds));
            $exportData['profiles'][] = $profile->toArray();
        }
        foreach ($destinationIds as $destinationId) {
            $destination = $this->destinationFactory->create()->load($destinationId);
            $destination->setData('new_destination_id', substr($randIdPrefix . $destinationId, 0, 8));
            #$destination->unsetData('destination_id');
            $destination->unsetData('password');
            $exportData['destinations'][] = $destination->toArray();
        }
        $exportData = \Zend_Json::encode($exportData);

        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $file = $this->utilsHelper->prepareFilesForDownload(['xtento_productexport_settings.json' => $exportData]);
        $resultPage->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', strlen($file['data']))
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"')
            ->setHeader('Last-Modified', date('r'));
        $resultPage->setContents($file['data']);
        return $resultPage;
    }
}