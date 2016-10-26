<?php

/**
 * Product:       Xtento_XtCore (2.0.5)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2015-11-26T12:22:53+00:00
 * File:          app/code/Xtento/XtCore/Observer/ConfigurationUpdateCheckObserver.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Observer;

class ConfigurationUpdateCheckObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Xtento\XtCore\Model\System\Config\Backend\Configuration
     */
    protected $configurationCheck;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Xtento\XtCore\Model\System\Config\Backend\Configuration $configurationCheck
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Xtento\XtCore\Model\System\Config\Backend\Configuration $configurationCheck,
        \Magento\Framework\Registry $registry
    ) {
        $this->configurationCheck = $configurationCheck;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $updatedConfiguration = $this->registry->registry('xtento_configuration_updated');
        if ($updatedConfiguration !== null) {
            $this->registry->unregister('xtento_configuration_updated');
            $this->configurationCheck->afterUpdate($updatedConfiguration);
        }
    }
}
