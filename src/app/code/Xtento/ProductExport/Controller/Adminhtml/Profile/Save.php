<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-07-21T12:35:23+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/Save.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

class Save extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * Save profile
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        /** @var $postData \Zend\Stdlib\Parameters */
        if ($postData = $this->getRequest()->getPost()) {
            $postData = $postData->toArray();
            // Validate dates
            if (array_key_exists('export_filter_datefrom', $postData) && !empty($postData['export_filter_datefrom'])) {
                $inputFilter = new \Zend_Filter_Input(
                    ['export_filter_datefrom' => $this->dateFilter],
                    [],
                    ['export_filter_datefrom' => $postData['export_filter_datefrom']]
                );
                $filteredData = $inputFilter->getUnescaped();
                $postData['export_filter_datefrom'] = $filteredData['export_filter_datefrom'];
            }
            if (array_key_exists('export_filter_dateto', $postData) && !empty($postData['export_filter_dateto'])) {
                $inputFilter = new \Zend_Filter_Input(
                    ['export_filter_dateto' => $this->dateFilter],
                    [],
                    ['export_filter_dateto' => $postData['export_filter_dateto']]
                );
                $filteredData = $inputFilter->getUnescaped();
                $postData['export_filter_dateto'] = $filteredData['export_filter_dateto'];
            }
            if (!isset($postData['name'])) {
                $this->messageManager->addErrorMessage(
                    __('Could not find any data to save in the POST request. POST request too long maybe?')
                );
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }
            $model = $this->profileFactory->create();
            if (isset($postData['rule']['conditions'])) {
                $postData['conditions'] = $postData['rule']['conditions'];
                unset($postData['rule']);
            }
            #var_dump($postData); die();
            $model->setData($postData);
            if ($model->getId()) {
                $profile = $model->load($model->getId());
                $this->registry->unregister('productexport_profile');
                $this->registry->register('productexport_profile', $profile);
                try {
                    $model->loadPost($postData);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('An error occurred while saving this export profile: %1', $e->getMessage())
                    );
                }
            }
            $model->setLastModification(time());

            if (!$model->getId()) {
                $model->setEnabled(1);
            }

            // Handle certain fields
            $fields = [
                'event_observers',
                'export_filter_product_type',
                'attributes_to_select',
                'export_filter_product_visibility',
                'export_filter_product_status'
            ];
            foreach ($fields as $field) {
                $value = $model->getData($field);
                $model->setData($field, '');
                if (is_array($value)) {
                    $model->setData($field, implode(',', $value));
                }
                if (empty($value)) {
                    $model->setData($field, '');
                }
            }
            // Handle date fields
            $fields = [
                'export_filter_datefrom',
                'export_filter_dateto',
                'export_filter_last_x_days',
                'export_filter_older_x_minutes',
                'export_filter_updated_last_x_minutes'
            ];
            foreach ($fields as $field) {
                $value = $model->getData($field);
                if (empty($value)) {
                    if ($field == 'export_filter_last_x_days' && $value === '0') {
                    } else {
                        $model->setData($field, null);
                    }
                }
            }

            try {
                $model->save();
                $this->_session->setFormData(false);
                $this->messageManager->addSuccessMessage(__('The export profile has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $model->getId(), 'active_tab' => $this->getRequest()->getParam('active_tab')]
                    );
                    return $resultRedirect;
                } else {
                    $resultRedirect->setPath('*/*');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while saving this export profile: ' . $e->getMessage())
                );
            }

            $this->_session->setFormData($postData);
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        } else {
            $this->messageManager->addErrorMessage(
                __('Could not find any data to save in the POST request. POST request too long maybe?')
            );
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
    }
}