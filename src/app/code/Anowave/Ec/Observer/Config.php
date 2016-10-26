<?php
/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2016 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */

namespace Anowave\Ec\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Google Analytics module observer
 *
 */
class Config implements ObserverInterface
{
	protected $_helper 			= null;
	protected $_messageManager 	= null;
	
	/**
	 * API 
	 * 
	 * @var \Anowave\Ec\Model\Api
	 */
	protected $api = null;
	
	public function __construct(\Anowave\Ec\Helper\Data $helper, \Magento\Framework\Message\ManagerInterface $messageManager)
	{
		$this->_helper 			= $helper;
		$this->_messageManager 	= $messageManager;
	}
	/**
	 * Add order information into GA block to render on checkout success pages
	 *
	 * @param EventObserver $observer
	 * @return void
	 */
	public function execute(EventObserver $observer)
	{
		$this->_helper->notify($this->_messageManager);
		
		/**
		 * Operation log
		*/
		$log = array();
		
		if ($_POST && isset($_POST['args']))
		{
			foreach (@$_POST['args'] as $entry)
			{
				$log = array_merge($log, $this->getApi()->create($entry));
			}
		}
		
		if (!$log && isset($_POST['args']))
		{
			$log[] = 'Container configured succesfully. Please go to Google Tag Manager to preview newly created tags, variables and triggers.';
		}
		
		if ($log)
		{
			$this->_messageManager->addNotice(nl2br(join(PHP_EOL, $log)));
		}
		
		return true;
	}
	
	protected function getApi()
	{
		if (!$this->api)
		{
			$this->api = \Magento\Framework\App\ObjectManager::getInstance()->create('Anowave\Ec\Model\Api');
		}
			
		return $this->api;
	}
}
