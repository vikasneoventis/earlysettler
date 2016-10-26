<?php

namespace Balance\Box\Controller\Adminhtml\Box;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploader;

    /**
     * @var \Balance\Box\Model\Box\Image
     */
    protected $imageModel;

    /**
     * @param Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploader
     * @param \Balance\Box\Model\Box\Image $imageModel
     */
    public function __construct(
        Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Balance\Box\Model\Box\Image $imageModel
    ) {
        $this->uploader = $uploader;
        $this->imageModel = $imageModel;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Balance_Box::save');
    }

    public function uploadFileAndGetName($input, $destinationFolder, $data) {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->uploader->create(['fileId' => $input]);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                $this->messageManager->addError($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Balance\Box\Model\Box $model */
            $model = $this->_objectManager->create('Balance\Box\Model\Box');

            $id = $this->getRequest()->getParam('box_id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'box_box_prepare_save',
                ['box' => $model, 'request' => $this->getRequest()]
            );

            try {
                $desktopImageName = $this->uploadFileAndGetName('desktop_image', $this->imageModel->getBaseDir(), $data);
                $mobileImageName = $this->uploadFileAndGetName('mobile_image', $this->imageModel->getBaseDir(), $data);
                $model->setDesktopImage($desktopImageName);
                $model->setMobileImage($mobileImageName);
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Box.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['box_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the box.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['box_id' => $this->getRequest()->getParam('box_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}