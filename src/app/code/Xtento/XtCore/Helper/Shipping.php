<?php

/**
 * Product:       Xtento_XtCore (2.0.5)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-03-31T08:41:29+00:00
 * File:          app/code/Xtento/XtCore/Helper/Shipping.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Helper;

class Shipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core store config
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function determineCarrierTitle($carrierCode, $shippingMethod = '', $storeId = null)
    {
        if ($carrierCode == 'custom') {
            $carrierTitle = (empty($shippingMethod)) ? __('Custom') : $shippingMethod;
        } else {
            $carrierTitle = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if (empty($carrierTitle)) {
                // Try to get carrier from XTENTO custom carrier trackers extension
                $carrierTitle = $this->scopeConfig->getValue(
                    'customtrackers/' . $carrierCode . '/title',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
            }
        }
        return $carrierTitle;
    }
}
