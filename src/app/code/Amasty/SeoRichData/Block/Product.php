<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

namespace Amasty\SeoRichData\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\ScopeInterface;

class Product extends AbstractBlock
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = [],
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;

        return parent::__construct($context, $data);
    }

    protected function prepareData()
    {
        if (!$this->_scopeConfig->isSetFlag(
            'amseorichdata/product/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return [];
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->coreRegistry->registry('current_product');

        if (!$product)
            return [];

        $data = [];

 //       $data['availability'] = $product->isAvailable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock';

        return $data;
    }

    protected function _toHtml()
    {
        $data = $this->prepareData();

        $result = '';
        foreach ($data as $name => $value) {
            $result .= "\n<meta itemprop=\"$name\" content=\"$value\">";
        }

        return $result;
    }
}
