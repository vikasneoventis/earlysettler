<?php

/**
 * Product:       Xtento_XtCore (2.0.5)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-03-11T12:32:36+00:00
 * File:          app/code/Xtento/XtCore/Model/System/Config/Source/Order/AllStatuses.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Source\Order;

/**
 * Class AllStatuses
 *
 * Order Statuses source model
 *
 * @package Xtento\XtCore\Model\System\Config\Source\Order
 */
class AllStatuses extends \Magento\Sales\Model\Config\Source\Order\Status
{
    protected $_stateStatuses = false; // Get all statuses, see \Magento\Sales\Model\Config\Source\Order\Status

    /**
     * Function to just put all order status "codes" into an array.
     *
     * @return array
     */
    public function toArray()
    {
        $statuses = $this->toOptionArray();
        $statusArray = [];
        foreach ($statuses as $status) {
            array_push($statusArray, $status['value']);
        }
        return $statusArray;
    }

}
