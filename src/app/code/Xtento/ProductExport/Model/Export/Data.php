<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-18T19:37:01+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export;

use Magento\Framework\Exception\LocalizedException;

class Data extends \Magento\Framework\Model\AbstractModel
{
    protected $registeredExportData = null;
    protected $exportClassInstances = [];

    /**
     * File locator
     *
     * @var \Magento\Framework\Module\Dir
     */
    protected $fileResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Dir $fileResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Dir $fileResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fileResolver = $fileResolver;
        $this->objectManager = $objectManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function getRegisteredExportData()
    {
        $this->registeredExportData = [];
        // Load registered export data
        $exportDataFile = $this->fileResolver->getDir(
                'Xtento_ProductExport',
                ''
            ) . DIRECTORY_SEPARATOR . 'etc'. DIRECTORY_SEPARATOR . 'xtento' . DIRECTORY_SEPARATOR . 'export_data.xml';
        if (!file_exists($exportDataFile)) {
            throw new LocalizedException(__('Could not load export_data.xml when exporting.'));
        }
        $this->loadExportDataFile($exportDataFile);
        // Users own export data file
        $exportOwnDataFile = $this->fileResolver->getDir(
                'Xtento_ProductExport',
                ''
            ) . DIRECTORY_SEPARATOR . 'etc'. DIRECTORY_SEPARATOR . 'xtento' . DIRECTORY_SEPARATOR . 'export_data.own.xml';
        if (file_exists($exportOwnDataFile)) {
            $this->loadExportDataFile($exportOwnDataFile, false);
        }
    }

    protected function loadExportDataFile($exportDataFile, $throwFileException = true)
    {
        if (file_exists($exportDataFile) || is_readable($exportDataFile)) {
            $exportData = simplexml_load_file($exportDataFile);
            if ($exportData) {
                foreach ($exportData->data->children() as $dataIdentifier => $dataConfig) {
                    $profileIds = (string)$dataConfig->profile_ids; // Apply class only to profile IDs X,Y,Z (comma-separated)
                    if ($profileIds !== '') {
                        if ($this->getProfile() && in_array($this->getProfile()->getId(), explode(",", $profileIds))) {
                            $this->registeredExportData[$dataIdentifier] = $dataConfig;
                        }
                    } else {
                        //array_push($this->registeredExportData, array('name' => $exportName, 'config' => $dataConfig));
                        $this->registeredExportData[$dataIdentifier] = $dataConfig;
                    }
                }
            } else {
                throw new LocalizedException (__(
                    'Could not load export_data.xml file for data exporting. File broken? Location: ' . $exportDataFile
                ));
            }
        } else {
            if ($throwFileException) {
                throw new LocalizedException (__(
                    'Could not load export_data.xml file for data exporting. File does not exist or is not readable. Location: ' . $exportDataFile
                ));
            }
        }
    }

    public function getExportData($entityType, $collectionItem = false, $getConfiguration = false)
    {
        if ($this->registeredExportData === null) {
            $this->getRegisteredExportData();
        }
        $exportData = [];
        foreach ($this->registeredExportData as $dataIdentifier => $dataConfig) {
            $className = @current($dataConfig->class);
            if (!$className) {
                $className = (string)$dataConfig->class;
            }
            $classIdentifier = str_replace('\Xtento\ProductExport\Model\Export\Data\\', '', $className);
            if (isset($this->exportClassInstances[$className])) {
                $exportClass = $this->exportClassInstances[$className];
            } else {
                $exportClass = $this->objectManager->get($className);
            }
            if (!isset($this->exportClassInstances[$className])) {
                $this->exportClassInstances[$className] = $exportClass;
            }
            if ($exportClass) {
                #$memBefore = memory_get_usage();
                #echo "Before - ".$exportConfig['config']->class.": $memBefore<br>";
                if ($getConfiguration) {
                    if ($exportClass->getEnabled() && $exportClass->confirmDependency() && in_array(
                            $entityType,
                            $exportClass->getApplyTo()
                        )
                    ) {
                        $exportData[] = [
                            'class' => $className,
                            'class_identifier' => $classIdentifier,
                            'configuration' => $exportClass->getConfiguration()
                        ];
                    }
                } else {
                    if (!in_array($entityType, $exportClass->getApplyTo())) {
                        continue;
                    }
                    if (!$exportClass->getEnabled() || !$exportClass->confirmDependency()) {
                        continue;
                    }
                    $returnData = $exportClass
                        ->setProfile($this->getProfile())
                        ->setShowEmptyFields($this->getShowEmptyFields())
                        ->getExportData($entityType, $collectionItem);
                    if (is_array($returnData)) {
                        $exportData = array_merge_recursive($exportData, $returnData);
                    }
                    #var_dump($className, $returnData);
                }
                #echo "After: ".memory_get_usage()." (Difference: ".round((memory_get_usage() - $memBefore) / 1024 / 1024, 2)." MB)<br>";
            }
        }
        #\Zend_Debug::dump($collectionItem); die();
        $exportData = array_merge_recursive($exportData, $this->addPrivateFields($collectionItem, $exportData));
        return $exportData;
    }

    /*
     * As data export classes are used as singletons during a single profile run, we need to reset them for each new profile exported so now old data is retained in the export classes
     */
    public function resetExportClasses()
    {
        if ($this->registeredExportData === null) {
            $this->getRegisteredExportData();
        }
        foreach ($this->registeredExportData as $dataIdentifier => $dataConfig) {
            $className = @current($dataConfig->class);
            unset($this->exportClassInstances[$className]);
        }
    }

    protected function addPrivateFields($collectionItem, $exportData)
    {
        $privateFields = [];
        if ($collectionItem !== false && $collectionItem->getObject()) {
            if (!isset($exportData['entity_id'])) {
                $privateFields['entity_id'] = $collectionItem->getObject()->getId();
            }
            #if (!isset($exportData['store_id'])) {
                #$privateFields['store_id'] = $collectionItem->getObject()->getStoreId();
            #}
            if (!isset($exportData['created_at'])) {
                $privateFields['created_at'] = $collectionItem->getObject()->getCreatedAt();
            }
        }
        return $privateFields;
    }
}