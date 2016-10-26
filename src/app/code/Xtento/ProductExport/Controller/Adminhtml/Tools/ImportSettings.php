<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-05-30T12:38:27+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Tools/ImportSettings.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Tools;

class ImportSettings extends \Xtento\ProductExport\Controller\Adminhtml\Tools
{
    /**
     * Import action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     * @throws \Exception
     */
    public function execute()
    {
        // Check for uploaded file
        $settingsFile = $this->_request->getFiles('settings_file');
        if (!isset($settingsFile['tmp_name']) || empty($settingsFile['tmp_name'])) {
            $this->messageManager->addErrorMessage(__('No settings file has been uploaded.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $uploadedFile = $settingsFile['tmp_name'];
        // Check if data should be updated or added
        $updateByName = $this->getRequest()->getPost('update_by_name', false);
        if ($updateByName == 'on') {
            $updateByName = true;
        } else {
            $updateByName = false;
        }
        // Counters
        $addedCounter = ['profiles' => 0, 'destinations' => 0];
        $updatedCounter = ['profiles' => 0, 'destinations' => 0];
        // Load and decode JSON settings
        $settingsFile = file_get_contents($uploadedFile);
        try {
            $settingsArray = \Zend_Json::decode($settingsFile);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Import failed. Decoding of JSON import format failed.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        // Process profiles
        if (isset($settingsArray['profiles'])) {
            foreach ($settingsArray['profiles'] as $profileData) {
                if ($updateByName) {
                    $profileCollection = $this->profileFactory->create()->getCollection()
                        ->addFieldToFilter('entity', $profileData['entity'])
                        ->addFieldToFilter('name', $profileData['name']);
                    if ($profileCollection->getSize() === 1) {
                        unset($profileData['new_destination_ids']);
                        $this->profileFactory->create()->setData($profileData)->setId($profileCollection->getFirstItem()->getId())->save();
                        $updatedCounter['profiles']++;
                    } else {
                        if (isset($profileData['new_destination_ids'])) {
                            $profileData['destination_ids'] = $profileData['new_destination_ids'];
                            unset($profileData['new_destination_ids']);
                        }
                        $this->profileFactory->create()->setData($profileData)->save();
                        $addedCounter['profiles']++;
                    }
                } else {
                    if (isset($profileData['new_destination_ids'])) {
                        $profileData['destination_ids'] = $profileData['new_destination_ids'];
                        unset($profileData['new_destination_ids']);
                    }
                    $this->profileFactory->create()->setData($profileData)->save();
                    $addedCounter['profiles']++;
                }
            }
        }
        // Process destinations
        if (isset($settingsArray['destinations'])) {
            foreach ($settingsArray['destinations'] as $destinationData) {
                if ($updateByName) {
                    $destinationCollection = $this->destinationFactory->create()->getCollection()
                        ->addFieldToFilter('type', $destinationData['type'])
                        ->addFieldToFilter('name', $destinationData['name']);
                    if ($destinationCollection->count() === 1) {
                        unset($destinationData['new_destination_id']);
                        $this->destinationFactory->create()->setData($destinationData)->setId($destinationCollection->getFirstItem()->getId())->save();
                        $updatedCounter['destinations']++;
                    } else {
                        $newDestination = $this->destinationFactory->create()->setData($destinationData);
                        if (isset($destinationData['new_destination_id'])) {
                            $newDestination->setId($destinationData['new_destination_id']);
                            unset($destinationData['new_destination_id']);
                            $newDestination->saveWithId();
                        } else {
                            unset($destinationData['new_destination_id']);
                            $newDestination->save();
                        }
                        $addedCounter['destinations']++;
                    }
                } else {
                    $newDestination = $this->destinationFactory->create()->setData($destinationData);
                    if (isset($destinationData['new_destination_id'])) {
                        $newDestination->setId($destinationData['new_destination_id']);
                        unset($destinationData['new_destination_id']);
                        $newDestination->saveWithId();
                    } else {
                        unset($destinationData['new_destination_id']);
                        $newDestination->save();
                    }
                    $addedCounter['destinations']++;
                }
            }
        }
        // Done
        $this->messageManager->addSuccessMessage(__(__('%1 profiles have been added, %2 profiles have been updated, %3 destinations have been added, %4 destinations have been updated.', $addedCounter['profiles'], $updatedCounter['profiles'], $addedCounter['destinations'], $updatedCounter['destinations'])));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}