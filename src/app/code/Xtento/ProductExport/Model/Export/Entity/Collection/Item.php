<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-09-09T09:53:31+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Entity/Collection/Item.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Entity\Collection;

class Item extends \Magento\Framework\DataObject
{
    public $collectionItem;
    public $collectionSize;
    public $currItemNo;

    public function __construct($collectionItem, $entityType, $currItemNo, $collectionCount)
    {
        parent::__construct();
        $this->collectionItem = $collectionItem;
        $this->collectionSize = $collectionCount;
        $this->currItemNo = $currItemNo;
        if ($entityType == \Xtento\ProductExport\Model\Export::ENTITY_PRODUCT) {
            $this->setProduct($collectionItem);
        }
        if ($entityType == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
            $this->setReview($collectionItem);
        }
        if ($entityType == \Xtento\ProductExport\Model\Export::ENTITY_CATEGORY) {
            $this->setCategory($collectionItem);
        }
    }

    public function getObject()
    {
        return $this->collectionItem;
    }
}