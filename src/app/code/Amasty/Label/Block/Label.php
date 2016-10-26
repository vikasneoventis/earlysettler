<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Label\Block;

class Label extends \Magento\Framework\View\Element\Template
{
    CONST DISPLAY_PRODUCT  = 'amasty_label/display/product';
    CONST DISPLAY_CATEGORY = 'amasty_label/display/category';

    /**
     * @var \Amasty\Label\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Amasty\Label\Model\Labels
     */
    protected $_label;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Amasty\Label\Helper\Data $helper
    )
    {
        parent::__construct($context, $data);

        $this->_helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->setTemplate('Amasty_Label::label.phtml');
    }

    public function setLabel(\Amasty\Label\Model\Labels $label) {
        $this->_label = $label;
        return $this;
    }

    public function getLabel() {
        return $this->_label;
    }

    /**
     * Get container path from module settings
     *
     * @return string
     */
    public function getContainerPath() {
        if ($this->_label->getMode() == 'cat') {
            $path= $this->_scopeConfig->getValue(self::DISPLAY_CATEGORY);
        }
        else {
            $path = $this->_scopeConfig->getValue(self::DISPLAY_PRODUCT);
        }

        return $path;
    }

    /**
     * Get image url withmode and site url
     *
     * @return string
     */
    public function getImageScr() {
        $img = $this->_label->getValue('img');
        return $this->_helper->getImageUrl($img);
    }

}