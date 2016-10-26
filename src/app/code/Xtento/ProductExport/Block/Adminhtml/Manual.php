<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-17T13:39:10+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Manual.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml;

class Manual extends \Magento\Backend\Block\Template
{
    /**
     * @var \Xtento\ProductExport\Model\System\Config\Source\Export\Profile
     */
    protected $profileSource;

    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\View\Element\Html\Date
     */
    protected $dateElement;

    /**
     * @var \Xtento\ProductExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * Manual constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Xtento\ProductExport\Model\System\Config\Source\Export\Profile $profileSource
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Magento\Framework\View\Element\Html\Date $dateElement
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
     * @param \Xtento\ProductExport\Model\ExportFactory $exportFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Xtento\ProductExport\Model\System\Config\Source\Export\Profile $profileSource,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter,
        \Xtento\ProductExport\Model\ExportFactory $exportFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileSource = $profileSource;
        $this->entityHelper = $entityHelper;
        $this->systemStore = $systemStore;
        $this->dateElement = $dateElement;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->exportFactory = $exportFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Xtento_ProductExport::manual_export.phtml');
    }

    public function getJs($filename)
    {
        $url = $this->_assetRepo->createAsset(
            'Xtento_ProductExport::js/' . $filename,
            ['_secure' => $this->getRequest()->isSecure()]
        )->getUrl();
        return $url;
    }

    public function getProfileSelectorHtml()
    {
        $html = '<select class="select" name="profile_id" id="profile_id" style="width: 320px;">';
        $html .= '<option value="">' . __('--- Select Profile---') . '</option>';
        $enabledProfiles = $this->profileSource->toOptionArray();
        $profilesByGroup = [];
        foreach ($enabledProfiles as $profile) {
            $profilesByGroup[$profile['entity']][] = $profile;
        }
        foreach ($profilesByGroup as $entity => $profiles) {
            $html .= '<optgroup label="' . __(
                    '%1 Export',
                    $this->entityHelper->getEntityName($entity)
                ) . '">';
            foreach ($profiles as $profile) {
                $html .= '<option value="' . $profile['value'] . '" entity="' . $entity . '">' . $profile['label'] . ' (' . __(
                        'ID: %1',
                        $profile['value']
                    ) . ')</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getCalendarHtml($id)
    {
        $this->dateElement->setData(
            [
                'name' => $id,
                'id' => $id,
                'class' => '',
                'value' => '',
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            ]
        );
        return $this->dateElement->getHtml();
    }

    public function getSelectValues()
    {
        $html = '';

        $exportModel = $this->exportFactory->create();
        $lastEntityIds = [];
        foreach ($exportModel->getEntities() as $entity => $label) {
            $lastEntityIds[$entity] = $this->entityHelper->getLastEntityId($entity);
        }
        $html .= $this->arrayToJsHash('last_entity_ids', $lastEntityIds);

        $profiles = $this->profileSource->toOptionArray(
            false,
            false,
            true
        );

        $profileLinks = [];
        foreach ($profiles as $profile) {
        $profileLinks[$profile['value']] = $this->getUrl(
            'xtento_productexport/profile/edit',
            ['id' => $profile['value']]
        );
    }
        $html .= $this->arrayToJsHash('profile_edit_links', $profileLinks);

        $profileSettings = [];
        $settingsToFetch = [
            'export_filter_datefrom',
            'export_filter_dateto',
            'export_filter_new_only',
            'start_download_manual_export'
        ];
        foreach ($profiles as $profile) {
            foreach ($settingsToFetch as $setting) {
                $value = $profile['profile']->getData($setting);
                if (($setting == 'export_filter_datefrom' || $setting == 'export_filter_dateto') && !empty($value)) {
                    $value = $this->dateTimeFormatter->formatObject(
                        $this->_localeDate->date($value),
                        $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)
                    );
                }
                $profileSettings[$profile['value']][$setting] = $value;
            }
        }
        $html .= $this->arrayToJsHash('profile_settings', $profileSettings);

        return $html;
    }

    public function getSession()
    {
        return $this->_session;
    }

    protected function arrayToJsHash($name, $array)
    {
        $html = 'window.' . $name . ' = $H({' . "\n";
        $loopLength = 0;
        foreach ($array as $index => $data) {
            if (!empty($data) && is_array($data)) {
                $loopLength++;
            }
        }
        $loopCounter = 0;
        foreach ($array as $index => $data) {
            $loopCounter++;
            $loopLength2 = count($array[$index]);
            $loopCounter2 = 0;
            if (is_array($data)) {
                $html .= '\'' . $this->escapeStringJs($index) . '\': {' . "\n";
                foreach ($data as $code => $label) {
                    $loopCounter2++;
                    $html .= '\'' . $this->escapeStringJs($code) . '\': \'' . $this->escapeStringJs($label) . '\'';
                    if ($loopCounter2 !== $loopLength2) {
                        $html .= ',';
                    }
                    $html .= "\n";
                }
                $html .= '}';
                if ($loopCounter !== $loopLength) {
                    $html .= ",\n";
                }
            } else {
                $html .= '\'' . $this->escapeStringJs($index) . '\': ';
                $html .= '\'' . $this->escapeStringJs($data) . '\'';
                if ($loopCounter !== count($array)) {
                    $html .= ",\n";
                }
            }
        }
        $html .= "});\n";
        return $html;
    }

    protected function escapeStringJs($string)
    {
        return str_replace(["'", "\n", "\r"], ["\\'", " ", " "], $string);
    }

    protected function _toHtml()
    {
        $messagesBlock = <<<EOT
<div id="messages">
    <div class="messages">
        <div class="message message-warning warning" id="warning-msg" style="display:none">
            <div id="warning-msg-text"></div>
        </div>
        <div class="message message-success success" id="success-msg" style="display:none">
            <div id="success-msg-text"></div>
        </div>
    </div>
</div>
EOT;
        return $messagesBlock . parent::_toHtml();
    }
}
