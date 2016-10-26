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

namespace Anowave\Ec\Model;

use Magento\PageCache\Model\DepersonalizeChecker;
use Magento\Framework\Registry;

/**
 * Google Analytics module observer
 *
 */
class DepersonalizePlugin
{
	/**
	 * @var DepersonalizeChecker
	 */
	protected $depersonalizeChecker;
	
	/**
	 * @var \Magento\Framework\Session\SessionManagerInterface
	 */
	protected $session;
	
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $customerSession;
	
	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $customerFactory;
	
	/**
	 * @var \Magento\Customer\Model\Visitor
	 */
	protected $visitor;
	
	/**
	 * @var int
	 */
	protected $customerGroupId;
	
	protected $registry;
	
	/**
	 * @var string
	 */
	protected $formKey;
	
	/**
	 * @param DepersonalizeChecker $depersonalizeChecker
	 * @param \Magento\Framework\Session\SessionManagerInterface $session
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param \Magento\Customer\Model\Visitor $visitor
	 */
	public function __construct
	(
		DepersonalizeChecker $depersonalizeChecker,
		\Magento\Framework\Session\SessionManagerInterface $session,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Customer\Model\Visitor $visitor,
		\Magento\Framework\Registry $registry
	) 
	{
		$this->session 				= $session;
		$this->customerSession 		= $customerSession;
		$this->customerFactory 		= $customerFactory;
		$this->visitor 				= $visitor;
		$this->depersonalizeChecker = $depersonalizeChecker;
		$this->registry				= $registry;
	}
	
	/**
	 * After generate Xml
	 *
	 * @param \Magento\Framework\View\LayoutInterface $subject
	 * @param \Magento\Framework\View\LayoutInterface $result
	 * @return \Magento\Framework\View\LayoutInterface
	 */
	public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
	{	
		if (is_null($this->registry->registry('cache_session_customer_id')) && 0 < (int) $this->customerSession->getCustomerId())
		{
			$this->registry->register('cache_session_customer_id', (int) $this->customerSession->getCustomerId());
		}
		
		return $result;
	}
}