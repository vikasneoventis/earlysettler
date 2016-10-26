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

namespace Anowave\Ec\Block;

class Track extends \Magento\Framework\View\Element\Template
{
	/**
	 * Google Tag Manager Data
	 *
	 * @var \Anowave\Ec\Helper\Data
	 */
	protected $_helper = null;
	
	protected $adwords = null;
	
	/**
	 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
	 */
	protected $_salesOrderCollection;
	
	public function __construct
	(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection, 
		\Anowave\Ec\Helper\Data $helper, 
		array $data = []
	) 
	{
		/**
		 * Set Helper
		 * @var \Anowave\Ec\Helper\Data 
		 */
		$this->_helper = $helper;
		
		/**
		 * Set order collection
		 * 
		 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
		 */
		$this->_salesOrderCollection = $salesOrderCollection;
		
		parent::__construct($context, $data);
		
		/**
		 * Make block non-cachable
		 * 
		 * @var boolean
		 */
		$this->_isScopePrivate = false;
	}
	
	/**
	 * Make block non-cachable
	 *
	 * @see \Magento\Framework\View\Element\AbstractBlock::isScopePrivate()
	 */
	public function isScopePrivate()
	{
		return false;
	}
	
	/**
	 * Get helper
	 * 
	 * @return \Anowave\Ec\Helper\Data
	 */
	public function getHelper()
	{
		return $this->_helper;
	}
	
	/**
	 * Get sales order collection
	 * 
	 * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
	 */
	public function getSalesOrderCollection()
	{
		return $this->_salesOrderCollection;
	}

	/**
	 * Determine page type
	 * 
	 * @return string
	 */
	public function getPageType()
	{
		switch($this->getRequest()->getControllerName())
		{
			case 'index':
			{
								return __('home');
				break;
			}
			case 'cart':		return __('cart');
			case 'category':	return __('category');
			case 'product': 	return __('product');
			case 'result':		return __('searchresults');
			default:			return __('other');
		}
	}
	
	public function getPurchasePush()
	{
		return $this->_helper->getPurchasePush($this);
	}

	public function getPurchaseGoogleTagParams()
	{
		return $this->_helper->getPurchaseGoogleTagParams($this);
	}
	
	public function getVisitorPush()
	{
		return $this->_helper->getVisitorPush($this);
	}
	
	public function getDetailPushForward()
	{
		return $this->_helper->getDetailPushForward($this);
	}
	
	public function getImpressionPushForward()
	{
		return $this->_helper->getImpressionPushForward($this);
	}
	
	public function getSearchPush()
	{
		return $this->_helper->getSearchPush($this);
	}
	
	public function getConversion()
	{
		return $this->_helper->getConversion($this);
	}
	
	/**
	 * Get store configuration variable 
	 * 
	 * @param string $config
	 */
	public function getConfig($config)
	{
		return $this->_helper->getConfig($config);
	}
	
	/**
	 * Get after <body> content
	 * 
	 * @return string
	 */
	public function afterBody()
	{
		return $this->getHelper()->afterBody();
	}

	/**
	 * Get Adwords Data Object
	 * 
	 * @return \Magento\Framework\DataObject
	 */
	public function getAdWords()
	{
		if (!$this->adwords)
		{
			$this->adwords = new \Magento\Framework\DataObject(array
			(
				'google_conversion_id' 			=> $this->getConfig('ec/adwords/conversion_id'),
				'google_conversion_label' 		=> $this->getConfig('ec/adwords/conversion_label'),
				'google_conversion_language'	=> $this->getConfig('ec/adwords/conversion_language'),
				'google_conversion_format'		=> $this->getConfig('ec/adwords/conversion_format'),
				'google_conversion_color' 		=> $this->getConfig('ec/adwords/conversion_color'),
				'google_conversion_currency' 	=> $this->getConfig('ec/adwords/conversion_currency')
			));
		}
		
		return $this->adwords;
	}
	
	/**
	 * Get orders collection
	 * 
	 * @return array
	 */
	public function getOrders()
	{
		return $this->getHelper()->getOrders($this);
	}
	
	/**
	 * Get transaction revenue
	 * 
	 * @return float
	 */
	public function getRevenue()
	{
		$revenue = 0;
		
		foreach ($this->getHelper()->getOrders($this) as $order)
		{
			$revenue = $order->getBaseGrandTotal();
		}
		
		return $revenue;
	}
	
	/**
	 * Escape strig 
	 * 
	 * @param string $string
	 */
	public function escape($string)
	{
		return $this->_helper->escape($string);
	}
	
	/**
	 * Render GTM
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		if (!$this->_helper->isActive())
		{
			return '';	
		}
		
		return $this->_helper->filter
		(
			parent::_toHtml()
		);
	}
}
