<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


namespace Amasty\SeoRichData\Plugin\Block;

use Amasty\SeoRichData\Model\DataCollector;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ViewInterface;

class Breadcrumbs
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var DataCollector
     */
    protected $dataCollector;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ViewInterface $view,
        DataCollector $dataCollector
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->view = $view;
        $this->dataCollector = $dataCollector;
    }

    public function beforeAssign(
        \Magento\Theme\Block\Html\Breadcrumbs $subject, $key, $value
    ) {
        if ($key == 'crumbs'
            && $this->scopeConfig->isSetFlag(
                'amseorichdata/breadcrumbs/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ) {
            $this->dataCollector->setData('breadcrumbs', $value);
        }
    }
}
