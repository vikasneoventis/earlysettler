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
class Success implements ObserverInterface
{
	/**
	 * @var \Magento\Framework\View\LayoutInterface
	 */
	protected $_layout;
	
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;
	
	/**
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\View\LayoutInterface $layout
	 * @param \Anowave\Ec\Helper\Data $helper
	 */
	public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager,\Magento\Framework\View\LayoutInterface $layout,\Anowave\Ec\Helper\Data $helper) 
	{
		$this->_layout = $layout;
	}
	
	/**
	 * Add order information into GA block to render on checkout success pages
	 *
	 * @param EventObserver $observer
	 * @return void
	 */
	public function execute(EventObserver $observer)
	{
		$orderIds = $observer->getEvent()->getOrderIds();
		
		if (empty($orderIds) || !is_array($orderIds)) 
		{
			return;
		}
		
		$block = $this->_layout->getBlock('ec_purchase');
		
		if ($block) 
		{
			$block->setOrderIds($orderIds);
		}
	}
}
