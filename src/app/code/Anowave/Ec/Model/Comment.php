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


/**
 * Google Analytics module observer
 *
 */
 
class Comment implements \Magento\Config\Model\Config\CommentInterface
{
	protected $api = null;
	
	/**
	 * Block factory
	 * 
	 * @var \Magento\Framework\View\Element\BlockFactory
	 */
	protected $blockFactory;
	
	/**
	 * Context
	 *
	 * @var \Magento\Framework\App\Helper\Context
	 */
	protected $context = null;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
	 * @param \Magento\Framework\App\Helper\Context $context
	 */
	public function __construct
	(
		\Magento\Framework\View\Element\BlockFactory $blockFactory,
		\Magento\Framework\App\Helper\Context $context
	)
	{
		$this->blockFactory = $blockFactory;
		$this->context		= $context;

	}
	
	public function getCommentText($currentValue)
	{
		$containers = array();
		
		foreach($this->getContainers() as $container)
		{
			$containers[] = "Container: <strong>$container->publicId</strong>,  Container ID: <strong>$container->containerId</strong>";
		}
		
		if (!$this->getApi()->getClient()->isAccessTokenExpired())
		{
			return nl2br(join(PHP_EOL, $containers));
		}

		return $this->blockFactory->createBlock('Anowave\Ec\Block\Comment')->setTemplate('comment.phtml')->toHtml();
	}
	
	protected function getContainers()
	{
		$account = $this->context->getScopeConfig()->getValue('ec/api/google_gtm_account_id');
	
		if ($account)
		{
			return $this->getApi()->getContainers($account);
		}
			
		return array();
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