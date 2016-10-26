<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Storelocator\Block\Adminhtml\Store;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Grid extends \Magestore\Storelocator\Block\Adminhtml\Widget\Grid
{
    /**
     * get selected row values.
     *
     * @return array
     */
    public function getSelectedRows()
    {
        $selectedStores = $this->_converter->toFlatArray(
            $this->_storelocatorHelper->getTreeSelectedStores()
        );

        return array_values($selectedStores);
    }
}
