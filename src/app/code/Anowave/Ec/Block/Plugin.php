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

class Plugin
{
	/**
	 * Helper
	 *
	 * @var \Anowave\Ec\Helper\Data
	 */
	protected $_helper = null;
	
	/**
	 * Config
	 *
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_coreConfig = null;
	
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry = null;
	
	/**
	 * Object manager
	 *
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	protected $_object = null;
	
	/**
	 * Cart
	 *
	 * @var \Magento\Checkout\Model\Cart
	 */
	protected $_cart = null;
	
	/**
	 * ProductRepository
	 * 
	 * @var \Magento\Catalog\Api\ProductRepositoryInterface
	 */
	protected $productRepository = null;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
	 * @param \Magento\Framework\Registry $registry
	 * @param \Anowave\Ec\Helper\Data $helper
	 * @param \Magento\Framework\ObjectManagerInterface $object
	 * @param \Magento\Checkout\Model\Cart $cart
	 * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	 */
	public function __construct
	(
		\Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
		\Magento\Framework\Registry $registry, 
		\Anowave\Ec\Helper\Data $helper, 
		\Magento\Framework\ObjectManagerInterface $object,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository) 
	{
		$this->_cart 		 = $cart;
		$this->_helper 		 = $helper;
		$this->_object 		 = $object;
		$this->_coreConfig   = $coreConfig;
		$this->_coreRegistry = $registry;
		
		/**
		 * Set product repository
		 * 
		 * @var unknown
		 */
		$this->productRepository = $productRepository;

		/**
		 * Define a test case method
		 */
		$this->testCase();
	}
	
	public function testCase()
	{
		return true;
	}
	
	/**
	 * Block output modifier 
	 * 
	 * @param \Magento\Framework\View\Element\Template $block
	 * @param string $html
	 */
	public function afterToHtml($block, $content) 
	{
		if ($this->_helper->isActive())
		{	
			switch($block->getNameInLayout())
			{
				case 'product.info.addtocart':
				case 'product.info.addtocart.additional': 							return $this->augmentAddCartBlock($block, $content);
				case 'category.products.list': 										return $this->augmentListBlock($block, $content);
				case 'search.result': 												return $this->augmentSearchListBlock($block, $content);															
				case 'checkout.root': 												return $this->augmentCheckoutBlock($block, $content);
				case 'checkout.cart.item.renderers.simple.actions.remove':
				case 'checkout.cart.item.renderers.bundle.actions.remove':
				case 'checkout.cart.item.renderers.virtual.actions.remove':
				case 'checkout.cart.item.renderers.default.actions.remove':
				case 'checkout.cart.item.renderers.grouped.actions.remove':
				case 'checkout.cart.item.renderers.downloadable.actions.remove':
				case 'checkout.cart.item.renderers.configurable.actions.remove':    return $this->augmentRemoveCartBlock($block, $content);
			}
		}
		
		return $content;
	}
	
	private function augmentCheckoutBlock($block, $content)
	{
		return $content .= $block->getLayout()->createBlock('Anowave\Ec\Block\Track')->setTemplate('checkout.phtml')->setData
		(
			array
			(
				'checkout_push' => $this->_helper->getCheckoutPush($block, $this->_cart, $this->_coreRegistry, $this->_object)
			)
		)
		->toHtml();
	}

	private function augmentListBlock($block, $content)
	{
		/**
		 * Retrieve list of impression product(s)
		 * 
		 * @var array
		 */
		$products = array();
		
		foreach ($block->getLoadedProductCollection() as $product)
		{
			$products[] = $product;
		}
		
		/**
		 * Append tracking
		 */
		$doc = new \DOMDocument('1.0','utf-8');
		$dom = new \DOMDocument('1.0','utf-8');
		
		@$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

		$query = new \DOMXPath($dom);
		
		foreach ($query->query($this->_helper->getConfig('ec/selectors/list')) as $key => $element)
		{
			if (isset($products[$key]))
			{
				/**
				 * Get current category
				 *  
				 * @var object
				 */
				$category = $this->_coreRegistry->registry('current_category');

				/**
				 * Add data-* attributes used for tracking dynamic values
				 */
				foreach ($query->query($this->_helper->getConfig('ec/selectors/click'), $element) as $a)
				{
					$click = $a->getAttribute('onclick');
						
					$a->setAttribute('data-id', 		$this->_helper->escapeDataArgument($products[$key]->getSku()));
					$a->setAttribute('data-name', 		$this->_helper->escapeDataArgument($products[$key]->getName()));
					$a->setAttribute('data-price', 		$this->_helper->escapeDataArgument($this->_helper->getPrice($products[$key])));
					$a->setAttribute('data-category',   $this->_helper->escapeDataArgument($category->getName()));
					$a->setAttribute('data-list',		$this->_helper->escapeDataArgument($this->_helper->getCategoryList($category)));
					$a->setAttribute('data-brand',		$this->_helper->escapeDataArgument($this->_helper->getBrand($products[$key])));
					$a->setAttribute('data-quantity', 	1);
					$a->setAttribute('data-click',		$click);
					$a->setAttribute('onclick',			'return AEC.click(this,dataLayer)');
				}
			}
		}
		
		return $this->getDOMContent($dom, $doc);
	}
	
	private function augmentSearchListBlock($block, $content)
	{
		return $content;
	}

	private function augmentRemoveCartBlock($block, $content)
	{
		/**
		 * Append tracking
		 */
		$doc = new \DOMDocument('1.0','utf-8');
		$dom = new \DOMDocument('1.0','utf-8');
		
		@$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		
		/**
		 * Modify DOM
		 */
		
		$x = new \DOMXPath($dom);
		
		foreach ($x->query($this->_helper->getConfig('ec/selectors/cart_delete')) as $element)
		{
			$element->setAttribute('onclick', 			'return AEC.remove(this, dataLayer)');
			$element->setAttribute('data-id', 			$this->_helper->escapeDataArgument($block->getItem()->getProduct()->getSku()));
			$element->setAttribute('data-name', 		$this->_helper->escapeDataArgument($block->getItem()->getProduct()->getName()));
			$element->setAttribute('data-price', 		$this->_helper->escapeDataArgument($this->_helper->getPrice($block->getItem()->getProduct())));
			$element->setAttribute('data-category', 	'');
			$element->setAttribute('data-brand',		$this->_helper->escapeDataArgument($this->_helper->getBrand($block->getItem()->getProduct())));
			$element->setAttribute('data-quantity',		(int) $block->getItem()->getQty());
		}
		
		
		return $this->getDOMContent($dom, $doc);
	}
	
	
	/**
	 * Augment "Add to cart" block
	 * 
	 * @param string $content
	 * @return unknown
	 */
	private function augmentAddCartBlock($block, $content)
	{
		$doc = new \DOMDocument('1.0','utf-8');
		$dom = new \DOMDocument('1.0','utf-8');
		
		@$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		
		$x = new \DOMXPath($dom);

		foreach ($x->query($this->_helper->getConfig('ec/selectors/cart')) as $element)
		{
			$category = $this->_coreRegistry->registry('current_category');
			
			if (!$category)
			{
				/**
				 * Get all product categories
				 */
				$categories = $block->getProduct()->getCategoryIds();
					
				/**
				 * Load last category
				*/
				$category = $this->_object->create('\Magento\Catalog\Model\Category')->load
				(
					end($categories)
				);
			}
			
			/**
			 * Get existing onclick attribute
			 * 
			 * @var string
			 */
			$click = $element->getAttribute('onclick');
			
			$element->setAttribute('onclick', 			'return AEC.ajax(this,dataLayer)');
			$element->setAttribute('data-id', 			$this->_helper->escapeDataArgument($block->getProduct()->getSku()));
			$element->setAttribute('data-name', 		$this->_helper->escapeDataArgument($block->getProduct()->getName()));
			$element->setAttribute('data-price', 		$this->_helper->escapeDataArgument($this->_helper->getPrice($block->getProduct())));
			$element->setAttribute('data-category', 	$this->_helper->escapeDataArgument($category->getName()));
			$element->setAttribute('data-brand', 		'');
			$element->setAttribute('data-click', 		$click);
			
			if ('grouped' == $block->getProduct()->getTypeId())
			{
				$element->setAttribute('data-grouped',1);
			}
			
			if ('configurable' == $block->getProduct()->getTypeId())
			{
				$element->setAttribute('data-configurable',1);
			}
		}

		return $this->getDOMContent($dom, $doc);
	}
	
	/**
	 * Retrieves body
	 *
	 * @param DOMDocument $dom
	 * @param DOMDocument $doc
	 * @param string $decode
	 */
	public function getDOMContent(\DOMDocument $dom, \DOMDocument $doc, $debug = false, $originalContent = '')
	{
		try
		{
			$head = $dom->getElementsByTagName('head')->item(0);
			$body = $dom->getElementsByTagName('body')->item(0);
			
			if ($head instanceof \DOMElement)
			{
				foreach ($head->childNodes as $child)
				{
					$doc->appendChild($doc->importNode($child, true));
				}
			}
		
			if ($body instanceof \DOMElement)
			{
				foreach ($body->childNodes as $child)
				{
					$doc->appendChild($doc->importNode($child, true));
				}
			}
		}
		catch (\Exception $e)
		{
			
		}

		$content = $doc->saveHTML();
		
		return html_entity_decode($content, ENT_COMPAT, 'UTF-8');
	}
	
	public function getCurrentProduct()
	{
		return $this->_coreRegistry->registry('current_product');
	}
}