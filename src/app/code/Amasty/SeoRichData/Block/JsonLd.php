<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

namespace Amasty\SeoRichData\Block;

use Amasty\SeoRichData\Helper\Category as CategoryHelper;
use Amasty\SeoRichData\Model\DataCollector;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class JsonLd extends AbstractBlock
{
    /**
     * @var DataCollector
     */
    protected $dataCollector;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Core registry
     *
     * @var CategoryHelper
     */
    protected $categoryHelper = null;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = [],
        DataCollector $dataCollector,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CategoryHelper $categoryHelper,
        EncoderInterface $jsonEncoder
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->jsonEncoder = $jsonEncoder;
        $this->dataCollector = $dataCollector;
        $this->storeManager = $storeManager;
        $this->categoryHelper = $categoryHelper;

        return parent::__construct($context, $data);
    }

    protected function prepareData()
    {
        $data = [];

        $this->addBreadcrumbsData($data);
        $this->addWebsiteName($data);
        $this->addOrganizationData($data);
        $this->addCategoryData($data);
        $this->addSearchData($data);

        return $data;
    }

    protected function addWebsiteName(&$data)
    {
        if (!$this->_scopeConfig->isSetFlag(
            'amseorichdata/website/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return;
        }

        $name = $this->_scopeConfig->getValue(
            'amseorichdata/website/name', ScopeInterface::SCOPE_STORE
        );

        if ($name) {
            $this->addWebsiteData($data);
            $data['website']['name'] = $name;
        }
    }

    protected function addBreadcrumbsData(&$data)
    {
        $breadcrumbs = $this->dataCollector->getData('breadcrumbs');
        if (is_array($breadcrumbs)) {
            $items = [];
            $position = 0;
            foreach ($breadcrumbs as $breadcrumb) {
                if (!$breadcrumb['link'])
                    continue;

                $items []= [
                    '@type' => 'ListItem',
                    'position' => ++$position,
                    'item' => [
                        '@id' => $breadcrumb['link'],
                        'name' => $breadcrumb['label']
                    ]
                ];
            }

            if (sizeof($items) > 0) {
                $data['breadcrumbs'] = [
                    '@context'        => 'http://schema.org',
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => $items
                ];
            }
        }
    }

    protected function addOrganizationData(&$data)
    {
        if (!$this->_scopeConfig->isSetFlag(
            'amseorichdata/organization/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return;
        }

        $data['organization'] = [
            '@context' => 'http://schema.org',
            '@type' => 'Organization',
            'url' => $this->_urlBuilder->getBaseUrl()
        ];

        if ($name = $this->_scopeConfig->getValue(
            'amseorichdata/organization/name', ScopeInterface::SCOPE_STORE
        )) {
            $data['organization']['name'] = $name;
        }

        if ($logoUrl = $this->_scopeConfig->getValue(
            'amseorichdata/organization/logo_url', ScopeInterface::SCOPE_STORE
        )) {
            $data['organization']['logo'] = $logoUrl;
        }
    }

    protected function addCategoryData(&$data)
    {
        if (!$this->_scopeConfig->isSetFlag(
            'amseorichdata/category/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return;
        }

        $category = $this->coreRegistry->registry('current_category');

        if (!$category)
            return;

        if ('category' != $this->_request->getControllerName())
            return;

        $data['category'] = [
            '@context' => 'http://schema.org',
            '@type' => 'Product',
            'name' => $category->getName(),
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'lowPrice' => $this->categoryHelper->getMinimalPrice($category)
            ]
        ];

        $reviewSummary = $this->categoryHelper->getReviewSummaryInfo($category);

        if ($reviewSummary) {
            $data['category']['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round(($reviewSummary['rating'] * 5) / 100, 2),
                'reviewCount' => $reviewSummary['reviews']
            ];
        }
    }

    protected function addSearchData(&$data)
    {
        if (!$this->_scopeConfig->isSetFlag(
            'amseorichdata/search/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $this->addWebsiteData($data);
        $data['website']['potentialAction'] = [
            '@type' => 'SearchAction',
            'target' => $this->_urlBuilder->getUrl('catalogsearch/result') . "?q={search_term_string}",
            'query-input' => 'required name=search_term_string'
        ];
    }

    protected function addWebsiteData(&$data)
    {
        if (isset($data['website']))
            return;

        $data['website'] = [
            '@context' => 'http://schema.org',
            '@type' => 'WebSite',
            'url' => $this->_urlBuilder->getBaseUrl()
        ];
    }

    protected function _toHtml()
    {
        $data = $this->prepareData();

        $result = '';
        foreach ($data as $section) {
            $result .= "<script type=\"application/ld+json\">{$this->jsonEncoder->encode($section)}</script>";
        }

        return $result;
    }
}
