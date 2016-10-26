<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Model/System/Config/Source/Cron/Frequency.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\System\Config\Source\Cron;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Frequency implements ArrayInterface
{
    const VERSION = 'G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=';

    const CRON_CUSTOM = 'custom';
    const CRON_1MINUTE = '* * * * *';
    const CRON_5MINUTES = '*/5 * * * *';
    const CRON_10MINUTES = '*/10 * * * *';
    const CRON_15MINUTES = '*/15 * * * *';
    const CRON_20MINUTES = '*/20 * * * *';
    const CRON_HALFHOURLY = '*/30 * * * *';
    const CRON_HOURLY = '0 * * * *';
    const CRON_2HOURLY = '0 */2 * * *';
    const CRON_DAILY = '0 0 * * *';
    const CRON_TWICEDAILY = '0 0,12 * * *';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('--- Select Frequency ---'),
                'value' => '',
            ],
            [
                'label' => __('Use "custom export frequency" field'),
                'value' => self::CRON_CUSTOM,
            ],
            [
                'label' => __('Every 5 minutes'),
                'value' => self::CRON_5MINUTES,
            ],
            [
                'label' => __('Every 10 minutes'),
                'value' => self::CRON_10MINUTES,
            ],
            [
                'label' => __('Every 15 minutes'),
                'value' => self::CRON_15MINUTES,
            ],
            [
                'label' => __('Every 20 minutes'),
                'value' => self::CRON_20MINUTES,
            ],
            [
                'label' => __('Every 30 minutes'),
                'value' => self::CRON_HALFHOURLY,
            ],
            [
                'label' => __('Every hour'),
                'value' => self::CRON_HOURLY,
            ],
            [
                'label' => __('Every 2 hours'),
                'value' => self::CRON_2HOURLY,
            ],
            [
                'label' => __('Daily (at midnight)'),
                'value' => self::CRON_DAILY,
            ],
            [
                'label' => __('Twice Daily (12am, 12pm)'),
                'value' => self::CRON_TWICEDAILY,
            ],
        ];
    }
}
