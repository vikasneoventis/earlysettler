<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Tools/Export.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Tools;

class Export extends \Magento\Backend\Block\Template
{
    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\Destination\CollectionFactory
     */
    protected $destinationCollectionFactory;

    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Xtento\ProductExport\Model\ResourceModel\Destination\CollectionFactory $destinationCollectionFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Xtento\ProductExport\Model\ResourceModel\Destination\CollectionFactory $destinationCollectionFactory,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->destinationCollectionFactory = $destinationCollectionFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    public function getProfiles()
    {
        $profileCollection = $this->profileCollectionFactory->create();
        $profileCollection->getSelect()->order('name ASC');
        return $profileCollection;
    }

    public function getDestinations()
    {
        $destinationCollection = $this->destinationCollectionFactory->create();
        $destinationCollection->getSelect()->order('name ASC');
        return $destinationCollection;
    }
}
