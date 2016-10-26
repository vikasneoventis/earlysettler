<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Label\Controller\Adminhtml\Labels;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;

class Save extends \Amasty\Label\Controller\Adminhtml\Labels
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Amasty\Label\Model\Labels');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    ['from_date' => $this->_dateFilter, 'to_date' => $this->_dateFilter],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('label_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getLabelId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong label is specified.'));
                    }
                }
                if (isset($data['customer_group_ids'])) {
                    $data['customer_group_ids'] = serialize($data['customer_group_ids']);
                }
                /*if only one store exists*/
                if (isset($data['stores']) && !$data['stores']) {
                    $data['stores'] = 1;
                }

                if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];

                    unset($data['rule']);

                    $rule = $this->_objectManager->create('Amasty\Label\Model\Rule');
                    $rule->loadPost($data);

                    $data['cond_serialize'] = serialize($rule->getConditions()->asArray());
                    unset($data['conditions']);
                }

                if (!empty($data['to_time'])) {
                    $data['to_date'] = $data['to_date'] . ' ' . $data['to_time'];
                }

                if (!empty($data['from_time'])) {
                    $data['from_date'] = $data['from_date'] . ' ' . $data['from_time'];
                }

                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());

                $this->_prepareForSave($model);

                $model->save();

                $this->messageManager->addSuccess(__('You saved the label.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_label/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('amasty_label/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_label/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_label/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('amasty_label/*/edit', ['id' =>  $id]);
                return;
            }
        }
        $this->_redirect('amasty_label/*/');
    }


    protected function _prepareForSave($model)
    {
        //upload images
        $data = $this->getRequest()->getPost();
        $path = $this->_filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
            'amasty/amlabel/'
        );

        $this->_ioFile->checkAndCreateFolder($path);

        $imagesTypes = array('prod', 'cat');
        foreach ($imagesTypes as $type) {
            $field = $type . '_img';

            $files = $this->getRequest()->getFiles();
            $isRemove = array_key_exists('remove_' . $field, $data);
            $hasNew   = !empty($files[$field]['name']);

            try {
                // remove the old file
                if ($isRemove || $hasNew) {
                    $oldName = isset($data['old_' . $field]) ? $data['old_' . $field] : '';
                    if ($oldName) {
                        $this->_ioFile->rm($path . $oldName);
                        $model->setData($field, '');
                    }
                }

                // upload a new if any
                if (!$isRemove && $hasNew) {
                    //find the first available name
                    $newName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $files[$field]['name']);
                    if (substr($newName, 0, 1) == '.') // all non-english symbols
                        $newName = 'label' . $newName;
                    $i = 0;
                    while ($this->_ioFile->fileExists($path . $newName)) {
                        $newName = (++$i) . $newName;
                    }

                    /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $field]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->save($path, $newName);

                    $model->setData($field, $newName);
                }
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        return true;
    }
}
