<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-28T15:48:49+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Index.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml;

abstract class Index extends \Xtento\ProductExport\Controller\Adminhtml\Action
{
    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_ProductExport::profiles');
        $resultPage->addBreadcrumb(__('Products'), __('Products'));
        $resultPage->addBreadcrumb(__('Product Export'), __('Product Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Export'));
    }
}
