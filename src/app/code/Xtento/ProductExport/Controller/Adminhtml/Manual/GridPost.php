<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-15T20:41:58+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Manual/GridPost.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Manual;

use Magento\Framework\Exception\LocalizedException;

class GridPost extends \Xtento\ProductExport\Controller\Adminhtml\Manual
{
    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirectInterface;

    /**
     * @var \Xtento\ProductExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;


    /**
     * GridPost constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\ProductExport\Model\ExportFactory $exportFactory
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Magento\Framework\Registry $registry,
        \Xtento\ProductExport\Model\ExportFactory $exportFactory,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig, $profileFactory);
        $this->entityHelper = $entityHelper;
        $this->registry = $registry;
        $this->exportFactory = $exportFactory;
        $this->utilsHelper = $utilsHelper;
        $this->redirectInterface = $context->getRedirect();
    }

    /*
     * Export from grid handler
     */
    public function execute()
    {
        $exportType = $this->getRequest()->getParam('type', false);
        if (!$exportType) {
            $this->messageManager->addErrorMessage(__('Export type not specified.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
             $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
             $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
             return $resultRedirect;
        }
        $exportIds = $this->getRequest()->getPost('selected', false);
        if (!$exportIds) {
            $this->messageManager->addErrorMessage(__('Please select objects to export.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
             $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
             $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
             return $resultRedirect;
        }
        $profileId = $this->getRequest()->getParam('profile_id', false);
        if (!$profileId) {
            $this->messageManager->addErrorMessage(__('No export profile specified.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
             $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
             $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
             return $resultRedirect;
        }
        $profile = $this->profileFactory->create()->load($profileId);
        // Export
        try {
            $beginTime = time();
            $exportedFiles = $this->exportFactory->create()->setProfile($profile)->gridExport($exportIds);
            $endTime = time();
            if ($profile->getStartDownloadManualExport()) {
                /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $file = $this->utilsHelper->prepareFilesForDownload($exportedFiles);
                if (empty($file)) {
                    throw new LocalizedException(
                        __('No files have been exported. Please check your XSL Template and/or profile filters.')
                    );
                }
                $resultPage->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Content-type', 'application/octet-stream', true)
                    ->setHeader('Content-Length', strlen($file['data']))
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"')
                    ->setHeader('Last-Modified', date('r'));
                $resultPage->setContents($file['data']);
                return $resultPage;
            } else {
                $this->messageManager->addComplexSuccessMessage(
                    'backendHtmlMessage',
                    [
                        'html' => (string)__(
                            'Export of %1 %2 completed successfully in %3 seconds. Click <a href="%4">here</a> to download exported files.',
                            $this->registry->registry('productexport_log')->getRecordsExported(),
                            $this->entityHelper->getPluralEntityName($profile->getEntity()),
                            ($endTime - $beginTime),
                            $this->getUrl(
                                'xtento_productexport/log/download',
                                ['id' => $this->registry->registry('productexport_log')->getId()]
                            )
                        )
                    ]
                );
                if ($this->registry->registry('productexport_log')->getResult() !== \Xtento\ProductExport\Model\Log::RESULT_SUCCESSFUL) {
                    $this->messageManager->addErrorMessage(
                        __(nl2br($this->registry->registry('productexport_log')->getResultMessage()))
                    );
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: %1', nl2br($e->getMessage())));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
         $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
         $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
         return $resultRedirect;
    }
}
