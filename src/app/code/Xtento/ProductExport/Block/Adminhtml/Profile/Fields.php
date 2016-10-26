<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-09-09T12:19:38+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Fields.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile;

class Fields extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\ProductExport\Model\Output\Xml\Writer
     */
    protected $xmlWriter;

    /**
     * Fields constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Xtento\ProductExport\Model\Output\Xml\Writer $xmlWriter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Xtento\ProductExport\Model\Output\Xml\Writer $xmlWriter,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->xmlWriter = $xmlWriter;
        parent::__construct($context, $data);
    }

    public function getFieldJson()
    {
        $export = $this->objectManager->create('Xtento\ProductExport\Model\Export\Entity\\' . ucfirst($this->registry->registry('productexport_profile')->getEntity()));
        $export->setShowEmptyFields(1);
        $export->setProfile($this->registry->registry('productexport_profile'));
        $filterField = $this->registry->registry('productexport_profile')->getEntity() == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW ? 'main_table.review_id': 'entity_id';
        $export->setCollectionFilters(
            [
                [$filterField => ['in' => explode(",", $this->getTestId())]]
            ]
        );
        $returnArray = $export->runExport();
        if (empty($returnArray)) {
            return false;
        }
        return \Zend_Json::encode($this->prepareJsonArray($returnArray));
    }

    /*
     * Convert Array into EXTJS TreePanel JSON
     */
    protected function prepareJsonArray($array, $parentKey = '')
    {
        static $depth = 0;
        $newArray = [];

        $depth++;
        if ($depth >= '100') {
            return '';
        }

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $key = $this->xmlWriter->handleSpecialParentKeys($key, $parentKey);
                $newArray[] = ['text' => '<strong>' . $key . '</strong>', 'leaf' => false, 'expanded' => true, 'cls' => 'x-tree-noicon', 'children' => $this->prepareJsonArray($val, $key)];
            } else {
                if ($val == '') {
                    $val = __('NULL');
                }
                $newArray[] = ['text' => $key, 'leaf' => false, 'cls' => 'x-tree-noicon', 'children' => [['text' => $val, 'leaf' => true, 'cls' => 'x-tree-noicon']]];
            }
        }
        return $newArray;
    }

    public function getTestId()
    {
        return urldecode($this->getRequest()->getParam('test_id'));
    }

    public function getRegistry()
    {
        return $this->registry;
    }
}