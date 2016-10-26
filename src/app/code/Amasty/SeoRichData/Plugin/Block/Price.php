<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


namespace Amasty\SeoRichData\Plugin\Block;

use Amasty\SeoRichData\Model\DataCollector;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Price
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DataCollector $dataCollector
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param                                               $result
     *
     * @return array|string
     */
    public function afterToHtml(
        \Magento\Catalog\Pricing\Render\FinalPriceBox $subject, $result
    ) {
        if ($subject->getZone() != 'item_view')
            return $result;

        if (!$this->scopeConfig->isSetFlag(
            'amseorichdata/product/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return $result;
        }

        $product = $subject->getSaleableItem();

        if (!$product)
            return $result;

        $data = [];

        $data['availability'] = $product->isAvailable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock';

        // Fix malformed price rich data
        $result = preg_replace('|itemprop="price"|', '', $result);
        $data['price'] = $subject->getPrice()->getValue();

        $meta = '';
        foreach ($data as $name => $value) {
            $meta .= "\n<meta itemprop=\"$name\" content=\"$value\">";
        }

        $result = preg_replace('|(<[^>]+http://schema.org/Offer[^>]+>)|', '\1'.$meta, $result, 1);

        return $result;
    }
}
