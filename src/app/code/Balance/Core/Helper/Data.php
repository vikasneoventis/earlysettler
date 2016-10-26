<?php
/**
 * Copyright © 2016 Balance Internet Pty., Ltd. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Balance\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Balance Core Helper
 *
 * @package Balance\Core\Helper
 * @author  Toan Nguyen <toan.nguyen@balanceinternet.com.au>
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $stringUtils;
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $defaultLogger;
    /**
     * @var string
     */
    protected $channelName;
    /**
     * @var string
     */
    protected $logFile;

    const MODULE_NAME = 'Balance_Core';

    /**
     * Data constructor.
     *
     * @param Context                $context       Context
     * @param ObjectManagerInterface $objectManager Object manager
     * @param StoreManagerInterface  $storeManager  Store manager
     * @param StringUtils            $stringUtils   String utils
     * @param ModuleListInterface    $moduleList    Module list
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        StringUtils $stringUtils,
        ModuleListInterface $moduleList
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->stringUtils = $stringUtils;
        $this->moduleList = $moduleList;
        $this->defaultLogger = $context->getLogger();
        $this->channelName = 'balance-debug';
        $this->logFile = BP . '/var/log/balance.log';
    }

    /**
     * Retrieve base url
     *
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Retrieve configuration
     *
     * @param string $configPath XML Path
     *
     * @return mixed
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get model like the Magento 1
     * - Create new object instance
     *
     * @param string $model Instance basename
     * @param array  $agrs  Agruments
     *
     * @return mixed
     */
    public function getModel($model, array $agrs = [])
    {
        return $this->objectManager->create($model, $agrs);
    }

    /**
     * Get singleton like Magento 1
     * - Retrieve cached object instance
     *
     * @param string $model Instance basename
     *
     * @return mixed
     */
    public function getSingleton($model)
    {
        return $this->objectManager->get($model);
    }

    /**
     * Retrieve current store name
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return string utils instance
     *
     * @return \Magento\Framework\Stdlib\StringUtils
     */
    public function getString()
    {
        return $this->stringUtils;
    }

    /**
     * Retrieve module version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->moduleList->getOne(static::MODULE_NAME)['setup_version'];
    }

    /**
     * Create new logger instance with custom channel name
     * and log file name
     *
     * @param string      $type
     * @param string|null $channelName
     * @param string|null $logFile
     *
     * @return \Monolog\Logger|\Psr\Log\LoggerInterface
     */
    public function getLogger($type = 'default', $channelName = null, $logFile = null)
    {
        switch ($type) {
            case 'custom':
                $logger = $this->getCustomLogger($channelName, $logFile);
                break;
            case 'default':
            default:
                $logger = $this->getDefaultLogger();
                break;
        }

        return $logger;
    }

    /**
     * Return default logger instance
     *
     * @return \Psr\Log\LoggerInterface
     */
    private function getDefaultLogger()
    {
        return $this->defaultLogger;
    }

    /**
     * Create new logger instance with custom channel name
     * and log file name
     *
     * @param string|null $channelName
     * @param string|null $logFile
     *
     * @return \Monolog\Logger
     */
    private function getCustomLogger($channelName = null, $logFile = null)
    {
        if (!empty($channelName) && !empty($logFile)) {
            $this->channelName = $channelName;
            $this->logFile = BP . $logFile;
        }

        /** @var \Monolog\Logger $logger */
        $logger = $this->getModel('Monolog\Logger', ['name' => $this->channelName]);
        /** @var \Monolog\Handler\StreamHandler $streamHandler */
        $streamHandler = $this->getModel('Monolog\Handler\StreamHandler', ['stream' => $this->logFile]);
        $logger->pushHandler($streamHandler);

        return $logger;
    }
}
