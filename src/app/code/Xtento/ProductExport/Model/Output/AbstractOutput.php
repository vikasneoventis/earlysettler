<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-17T12:36:21+00:00
 * File:          app/code/Xtento/ProductExport/Model/Output/AbstractOutput.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Output;

use Magento\Framework\Exception\LocalizedException;

abstract class AbstractOutput extends \Magento\Framework\Model\AbstractModel implements OutputInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $coreDate;

    /**
     * @var \Xtento\XtCore\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Xtento\ProductExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\Log\CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AbstractOutput constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\ProductExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->coreDate = $coreDate;
        $this->dateHelper = $dateHelper;
        $this->profileFactory = $profileFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public static $iteratingKeys = [
        'items',
        'custom_options',
        'product_attributes',
        'product_options'
    ];

    protected function replaceFilenameVariables($filename, $exportArray)
    {
        $filename = str_replace("|", "-", $filename); // Remove the pipe character - it's not allowed in file names anyways and we use it to separate multiple files in the DB
        // Replace variables in filename
        $replaceableVariables = [
            '/%d%/' => $this->coreDate->date('d'),
            '/%m%/' => $this->coreDate->date('m'),
            '/%y%/' => $this->coreDate->date('y'),
            '/%Y%/' => $this->coreDate->date('Y'),
            '/%h%/' => $this->coreDate->date('H'),
            '/%i%/' => $this->coreDate->date('i'),
            '/%s%/' => $this->coreDate->date('s'),
            '/%timestamp%/' => $this->coreDate->timestamp(time()),
            '/%lastentityid%/' => $this->getVariableValue('last_entity_id', $exportArray, $filename, '%lastentityid%'),
            '/%collectioncount%/' => $this->getVariableValue('collection_count', $exportArray, $filename, '%collectioncount%'),
            '/%exportCountForObject%/' => $this->getVariableValue('export_count_for_object', $exportArray, $filename, '%exportCountForObject%'), // How often was this object exported before by this profile?
            '/%dailyExportCounter%/' => $this->getVariableValue('daily_export_counter', $exportArray, $filename, '%dailyExportCounter%'), // How many objects have been exported today by this profile?
            '/%profileExportCounter%/' => $this->getVariableValue('profile_export_counter', $exportArray, $filename, '%profileExportCounter%'), // How many objects have been exported by this profile? Basically an incrementing counter for each export
            '/%uuid%/' => uniqid(),
            '/%exportid%/' => $this->getVariableValue('export_id', $exportArray, $filename, '%exportid%'),
        ];

        // Ability to add custom variables to the filename using an event
        $transportObject = new \Magento\Framework\DataObject();
        $transportObject->setCustomVariables([]);
        $this->_eventManager->dispatch(
            'xtento_productexport_replace_filename_variables_before',
            ['transport' => $transportObject]
        );
        $replaceableVariables = array_merge($replaceableVariables, $transportObject->getCustomVariables());

        // Register variables for other usage
        if (!empty($exportArray) ||
            (empty($exportArray) && $this->_registry->registry('xtento_productexport_export_variables') === null)
        ) {
            $this->_registry->register('xtento_productexport_export_variables', $replaceableVariables, true);
        }

        $filename = preg_replace(array_keys($replaceableVariables), array_values($replaceableVariables), $filename);
        return $filename;
    }

    protected function getVariableValue($variable, $exportArray, $filename = false, $attributeVariableName = false)
    {
        if (!empty($filename) && !empty($attributeVariableName) && !stristr($filename, $attributeVariableName)) {
            // Variable not required in filename
            return '';
        }
        $arrayToWorkWith = $exportArray;
        if ($variable == 'export_id') {
            if ($this->_registry->registry('productexport_log')) {
                return $this->_registry->registry('productexport_log')->getId();
            } else {
                return 0;
            }
        }
        if ($variable == 'collection_count') {
            return count($arrayToWorkWith);
        }
        if ($variable == 'last_entity_id') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['entity_id'])) {
                return $lastItem['entity_id'];
            }
        }
        if ($variable == 'date_from_timestamp') {
            $firstObject = array_shift($arrayToWorkWith);
            return $this->dateHelper->convertDateToStoreTimestamp($firstObject['created_at']);
        }
        if ($variable == 'date_to_timestamp') {
            $lastObject = array_pop($arrayToWorkWith);
            return $this->dateHelper->convertDateToStoreTimestamp($lastObject['created_at']);
        }
        if ($variable == 'root_category_id') {
            $firstObject = array_shift($arrayToWorkWith);
            $storeId = 0;
            if (isset($firstObject['store_id'])) {
                $storeId = $firstObject['store_id'];
            }
            return $this->storeManager->getStore($storeId)->getRootCategoryId();
        }
        if ($variable == 'export_count_for_object') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['entity_id'])) {
                $exportEntity = false;
                $profileId = false;
                if ($this->_registry->registry('productexport_log')) {
                    $profileId = $this->_registry->registry('productexport_log')->getProfileId();
                    $profile = $this->profileFactory->create()->load($profileId);
                    $exportEntity = $profile->getEntity();
                }
                if ($this->_registry->registry('productexport_profile')) {
                    $exportEntity = $this->_registry->registry('productexport_profile')->getEntity();
                    $profileId = $this->_registry->registry('productexport_profile')->getId();
                }
                if (!$exportEntity) {
                    return '';
                }
                $exportHistoryCollection = $this->historyCollectionFactory->create();
                $exportHistoryCollection->addFieldToFilter('entity', $exportEntity);
                $exportHistoryCollection->addFieldToFilter('entity_id', $lastItem['entity_id']);
                $exportHistoryCollection->addFieldToFilter('profile_id', $profileId);
                return $exportHistoryCollection->getSize() + 1;
            }
        }
        if ($variable == 'daily_export_counter' || $variable == 'profile_export_counter') {
            $exportEntity = false;
            $profileId = false;
            if ($this->_registry->registry('productexport_log')) {
                $profileId = $this->_registry->registry('productexport_log')->getProfileId();
                $profile = $this->profileFactory->create()->load($profileId);
                $exportEntity = $profile->getEntity();
            }
            if ($this->_registry->registry('productexport_profile')) {
                $exportEntity = $this->_registry->registry('productexport_profile')->getEntity();
                $profileId = $this->_registry->registry('productexport_profile')->getId();
            }
            if (!$exportEntity) {
                return '';
            }
            $exportLogCollection = $this->logCollectionFactory->create();
            #$exportHistoryCollection->addFieldToFilter('entity', $exportEntity);
            if ($variable == 'daily_export_counter') {
                $exportLogCollection->getSelect()->where('DATE(created_at) = DATE(NOW())');
            }
            $exportLogCollection->addFieldToFilter('main_table.profile_id', $profileId);
            $exportLogCollection->getSelect()->where('records_exported > 0');
            return $exportLogCollection->getSize();
        }
        // GUID
        if ($variable == 'guid') {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
        return '';
    }

    protected function throwXmlException($message)
    {
        $message .= "\n";
        foreach (libxml_get_errors() as $error) {
            // XML error codes: http://www.xmlsoft.org/html/libxml-xmlerror.html
            $message .= "\tLine " . $error->line . " (Error Code: ".$error->code."): " . $error->message;
            if (strpos($error->message, "\n") === FALSE) {
                $message .= "\n";
            }
        }
        libxml_clear_errors();
        throw new LocalizedException(__($message));
    }

    protected function changeEncoding($input, $encoding, $charsetLocale = '')
    {
        if (!empty($charsetLocale)) {
            // Set locale based on XSL Template "locale" attribute
            $oldLocale = setlocale(LC_CTYPE, "0"); // Get current locale
            @setlocale(LC_CTYPE, $charsetLocale);
        }
        $output = $input;
        if (!empty($encoding) && @function_exists('iconv')) {
            $output = @iconv("UTF-8", $encoding, $input);
            if (!$output && !empty($input)) {
                // Conversion failed, try as UTF-8 re-encoded
                $output = @iconv("UTF-8", $encoding, utf8_encode(utf8_decode($input)));
                if (!$output && !empty($input)) {
                    if (!empty($charsetLocale)) {
                        // Reset locale
                        setlocale(LC_CTYPE, $oldLocale);
                    }
                    $this->throwXmlException(__("While trying to convert your export data into the requested encoding '%1', the PHP iconv() function failed. You either forgot to add //IGNORE to the encoding, or you are affected by this bug: https://bugs.php.net/bug.php?id=48147", $encoding));
                }
            }
        }
        if (!empty($charsetLocale)) {
            // Reset locale
            setlocale(LC_CTYPE, $oldLocale);
        }
        return $output;
    }
}