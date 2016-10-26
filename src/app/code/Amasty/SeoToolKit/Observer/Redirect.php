<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


namespace Amasty\SeoToolKit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Event\ObserverInterface;

class Redirect implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    public function __construct(
        \Magento\Framework\App\State $appState,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->appState = $appState;
        $this->_scopeConfig = $scopeConfig;
        $this->_urlBuilder = $urlBuilder;
        $this->_response = $response;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            return;

        /** @var Request $request */
        $request = $observer->getRequest();

        if ($request->getMethod() != 'GET' ||
            !$this->_scopeConfig->isSetFlag('amseotoolkit/general/home_redirect')
        ) {
            return;
        }

        $baseUrl = $this->_urlBuilder->getBaseUrl();

        if (!$baseUrl) {
            return;
        }

        $requestPath = $request->getRequestUri();
        $params      = preg_split('/^.+?\?/', $request->getRequestUri());
        $baseUrl 	.= isset($params[1]) ? '?' . $params[1] : '';

        $redirectUrls = [
            '',
            '/cms',
            '/cms/',
            '/cms/index',
            '/cms/index/',
            '/index.php',
            '/index.php/',
            '/home',
            '/home/',
        ];

        if (!is_null($requestPath) && in_array($requestPath, $redirectUrls)) {
            $this->_response
                ->setRedirect($baseUrl, 301)
                ->sendResponse();

            exit;
        }
    }
}
