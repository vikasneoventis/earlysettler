<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Destination/Grid/Renderer/Result.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Destination\Grid\Renderer;

class Result extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render destination status
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = 'grid-severity-major';
        $text = __('No Result');
        switch ($this->_getValue($row)) {
            case 0:
                $class = 'grid-severity-critical';
                $text = __('Failed');
                break;
            case 1:
                $class = 'grid-severity-notice';
                $text = __('Success');
                break;
        }
        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
