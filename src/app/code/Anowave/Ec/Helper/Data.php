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


namespace Anowave\Ec\Helper;

use Magento\Store\Model\Store;
use Anowave\Package\Helper\Package;
use Magento\Framework\Registry;
use Anowave\Package\Helper\Base;

class Data extends \Anowave\Package\Helper\Package
{
	const USE_ASYNC_EVENTS = false;
	
	/**
	 * Package name
	 * @var string
	 */
	protected $package = 'MAGE2-GTM';
	
	/**
	 * Config path 
	 * @var string
	 */
	protected $config = 'ec/general/license';
	
	/**
	 * Order products array 
	 * 
	 * @var array
	 */
	private $_orders = array(); 
	
	
	protected $productRepository = null;
	
	/**
	 * Customer session
	 * 
	 * @var \Magento\Customer\Model\Session $session
	 */
	protected $session = null;
	
	/**
	 * Group registry 
	 * 
	 * @var \Magento\Customer\Model\GroupRegistry
	 */
	protected $groupRegistry = null;
	
	/**
	 * Order collection factory 
	 * 
	 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
	 */
	protected $orderCollectionFactory = null;

	/**
	 * Order config
	 *
	 * @var \Magento\Sales\Model\Order\Config
	 */
	protected $orderConfig = null;
	
	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $registry = null;
	
	/**
	 * @var \Magento\Framework\App\Http\Context
	 */
	protected $httpContext = null;

	protected $catalogData = null;
	
	protected $customer = null;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	 * @param \Magento\Customer\Model\Session $session
	 * @param \Magento\Customer\Model\GroupRegistry $groupRegistry
	 * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
	 * @param \Magento\Sales\Model\Order\Config $orderConfig
	 */
	public function __construct
	(
		\Magento\Framework\App\Helper\Context $context, 
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Customer\Model\Session $session,
		\Magento\Customer\Model\GroupRegistry $groupRegistry,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Sales\Model\Order\Config $orderConfig,
		\Magento\Framework\App\Http\Context $httpContext,
		\Magento\Catalog\Helper\Data $catalogData,
		array $data = []
	)
	{
		parent::__construct($context);
		
		$this->registry = $registry;
		
		/**
		 * Set product repository
		 * 
		 * @var \Magento\Catalog\Api\ProductRepositoryInterface
		 */
		$this->productRepository = $productRepository;
		
		/**
		 * Set Group Registry 
		 * 
		 * @var \Magento\Customer\Model\GroupRegistry
		 */
		$this->groupRegistry = $groupRegistry;
		
		/**
		 * Set session
		 * 
		 * @var \Magento\Customer\Model\Session $session
		 */
		$this->session = $session;
		
		/**
		 * Set order collection factory 
		 * 
		 * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
		 */
		$this->orderCollectionFactory = $orderCollectionFactory;
		
		/**
		 * Set order config 
		 * 
		 * @var \Magento\Sales\Model\Order\Config
		 */
		$this->orderConfig = $orderConfig;
		
		$this->httpContext = $httpContext;
		
		$this->catalogData = $catalogData;
	}


	/**
	 * Get checkout push 
	 * 
	 * @param unknown $block
	 * @param \Magento\Checkout\Model\Cart $cart
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\ObjectManagerInterface $object
	 */
	public function getCheckoutPush($block, \Magento\Checkout\Model\Cart $cart, \Magento\Framework\Registry $registry, \Magento\Framework\ObjectManagerInterface $object)
	{
		return json_encode(array
		(
			'event' => 'checkout',
			'ecommerce' => array
			(
				'checkout' => array
				(
					'actionField' => array
					(
						'step' => 1
					),
					'products' => $this->getCheckoutProducts($block, $cart, $registry, $object)
				)
			)
		), JSON_PRETTY_PRINT);
	}
	
	/**
	 * Get checkout products 
	 * 
	 * @param unknown $block
	 * @param \Magento\Checkout\Model\Cart $cart
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\ObjectManagerInterface $object
	 */
	public function getCheckoutProducts($block, \Magento\Checkout\Model\Cart $cart, \Magento\Framework\Registry $registry, \Magento\Framework\ObjectManagerInterface $object)
	{
		$products = array();
		
		
		foreach ($cart->getQuote()->getAllVisibleItems() as $item)
		{
			/**
			 * Get all product categories
			 */
			$categories = $item->getProduct()->getCategoryIds();
			
			/**
			 * Load last category 
			 */
			$category = $object->create('\Magento\Catalog\Model\Category')->load
			(
				end($categories)
			);
			
			$variant = [];
			
			$data = new \Magento\Framework\DataObject(array
			(
				'id' 		=> 		 $item->getSku(),
				'name' 		=> 		 $item->getName(),
				'price' 	=> 		 $item->getPriceInclTax(),
				'quantity' 	=> (int) $item->getQty(),
				'category'	=> 		 $this->getCategory($category),
				'brand'		=> 		 $this->getBrand
				(
					$item->getProduct()
				)
			));
			
			if ('configurable' == $item->getProduct()->getTypeId())
			{
				$variant = array();
				
				/**
				 * Get info buy request
				 *
				 * @var \Magento\Framework\DataObject
				*/				
				 
				$buyRequest = $item->getProduct()->getCustomOption('info_buyRequest');
				
				if (isset($buyRequest['value']))
				{
					$value = unserialize($buyRequest['value']);
					
					$info = new \Magento\Framework\DataObject($value);
				}
				else 
				{
					$info = new \Magento\Framework\DataObject(array
					(
						/**
						 * Check
						 */
					));
				}

				/**
				 * Construct variant
				*/
				foreach ((array) $info->getSuperAttribute() as $id => $option)
				{
					/**
					 * @todo: Pull attribute data
					 */
					$attribute = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($id);
			
					if ($attribute->usesSource())
					{
						$variant[] = join(':', array
						(
							$attribute->getFrontendLabel(),
							$attribute->getSource()->getOptionText($option)
						));
					}
				}
					
				$data->setId
				(
					$item->getProduct()->getSku()
				);
					
				$data->setName
				(
					$item->getProduct()->getName()
				);
					
				/**
				 * Load configurable
				 */
				$configurable = $this->productRepository->getById
				(
					$item->getProductId()
				);

				/**
				 * Swap data with configurable
				 */
				$data->setId($configurable->getId());
				$data->setSku($configurable->getSku());
				$data->setName($configurable->getName());
				$data->setBrand
				(
					$this->getBrand($configurable)
				);
					
				/**
				 * Push variant to data
				 *
				 * @var array
				 */
				$data->setVariant(join(chr(44), $variant));
			}
			
			$products[] = $data->getData();
		}
		
		return $products;
	}
	
	public function getImpressionPushForward($block)
	{
		try 
		{
			$list = $block->getLayout()->getBlock('category.products.list');
			
			
			if ($list)
			{
				$category = $this->registry->registry('current_category');
				
				$response = array
				(
					'ecommerce' => array
					(
						'actionField' => array
						(
							'list' => $this->getCategoryList($category)
						),
						'impressions' => array()
					)
				);
				
				$position = 1;
	
				$data = array();
				
				
				foreach ($this->getLoadedCollection($list) as $product)
				{
					$response['ecommerce']['impressions'][] = array
					(
						'list' 			=> $this->getCategoryList($category),
						'category'		=> $this->getCategory($category),
						'id'			=> $product->getSku(),
						'name'			=> $product->getName(),
						'brand'			=> $this->getBrand
						(
							$product
						),
						'price'			=> $this->getPrice($product),
						'position'		=> $position++
					);
				}
			}
	
			return (object) 
			[
				'push' 				=> json_encode($response, JSON_PRETTY_PRINT),
				'google_tag_params' => array
				(
					'ecomm_pagetype' 	=> 'category',
					'ecomm_category'	=> $this->escape($this->getCategory($category))
				)
			];
		}
		catch (\Exception $e)
		{
			
		}
		
		return false;
	}
	
	/**
	 * Get loaded product collection from product list block 
	 *  
	 * @param \Magento\Catalog\Block\Product\ListProduct $list
	 */
	private function getLoadedCollection(\Magento\Catalog\Block\Product\ListProduct $list)
	{
		$collection = $list->getLoadedProductCollection();
		
		/**
		 * Get toolbar
		 */
		$toolbar = $list->getToolbarBlock();
		
		if ($toolbar)
		{
			$orders = $list->getAvailableOrders();
			
			if ($orders) 
			{
				$toolbar->setAvailableOrders($orders);
			}
			
			$sort = $list->getSortBy();
			
			if ($sort) 
			{
				$toolbar->setDefaultOrder($sort);
			}
			
			$dir = $list->getDefaultDirection();
			
			if ($dir) 
			{
				$toolbar->setDefaultDirection($dir);
			}
			
			$modes = $list->getModes();
			
			if ($modes)
			{
				$toolbar->setModes($modes);
			}
			
			$collection->setCurPage($toolbar->getCurrentPage());

			$limit = (int) $toolbar->getLimit();
			
			if ($limit) 
			{
				$collection->setPageSize($limit);
			}
			
			if ($toolbar->getCurrentOrder()) 
			{
				$collection->setOrder($toolbar->getCurrentOrder(), $toolbar->getCurrentDirection());
			}
		}
		
		return $collection;
	}
	
	public function getDetailPushForward($block)
	{
		$info = $block->getLayout()->getBlock('product.info');
		
		if ($info)
		{
			$category = $this->registry->registry('current_category');
			
			if (!$category)
			{
				/**
				 * Get all product categories
				 */
				$categories = $info->getProduct()->getCategoryIds();
					
				/**
				 * Load last category
				*/
				$category = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Model\Category')->load
				(
					end($categories)
				);
			}
			
			$data = array
			(
				'ecommerce' => array
				(
					'detail' => array
					(
						'actionField' => array
						(
							'list' => $this->getCategoryList($category)
						),
						'products' => array
						(
							array
							(
								'id' 		=> $info->getProduct()->getSku(),
								'name' 		=> $info->getProduct()->getName(),
								'price' 	=> $this->getPrice($info->getProduct()),
								'brand'		=> $this->getBrand
								(
									$info->getProduct()
								),
								'category'	=> $this->getCategory($category),
								'quantity' 	=> 1
							)
						)
					)
				)
			);
			
			return (object) 
			[
				'push' 				=> json_encode($data, JSON_PRETTY_PRINT),
				'google_tag_params' => array
				(
					'ecomm_pagetype' 	=> 'product',
					'ecomm_category'	=> $this->escape($this->getCategory($category)),
					'ecomm_prodid'		=> json_encode
					(
						[
							$info->getProduct()->getSku()
						]
					)
				),
				'group' => $this->getDetailGroup($info, $category)
			];
		}
		
		return false;
	}
	
	/**
	 * Get detail JSON 
	 * 
	 * @param \Anowave\Ec\Block\Track $block
	 * @return string JSON
	 * @deprecated
	 */
	public function getDetailPush($block, $category)
	{
		if (self::USE_ASYNC_EVENTS)
		{
			$data = array
			(
				'ecommerce' => array
				(
					'detail' => array
					(
						'actionField' => array
						(
							'list' => $this->getCategoryList($category)
						),
						'products' => array
						(
							array
							(
								'id' 		=> $block->getProduct()->getSku(),
								'name' 		=> $block->getProduct()->getName(),
								'price' 	=> $this->getPrice($block->getProduct()),
								'brand'		=> $this->getBrand
								(
									$block->getProduct()
								),
								'category'	=> $this->getCategory($category),
								'quantity' 	=> 1
							)
						)
					)
				)
			);
			
			if ($this->afterBody())
			{
				$data['event'] = 'detail';
			}
			
			return json_encode($data, JSON_PRETTY_PRINT);
		}
		
		return false;
	}
	
	public function getDetailGroup($block, $category)
	{
		$group = [];
		
		if ('grouped' == $block->getProduct()->getTypeId())
		{
			foreach ($block->getProduct()->getTypeInstance(true)->getAssociatedProducts($block->getProduct()) as $product)
			{
				$group[] = array
				(
					'id' 		=> $product->getId(),
					'sku' 		=> $product->getSku(),
					'name' 		=> $product->getName(),
					'price' 	=> $this->getPrice($product),
					'brand'		=> $this->getBrand($product),
					'category'	=> $this->getCategory($category)
				);
			}
		}
		
		return json_encode($group);
	}
	
	public function getPurchasePush($block)
	{
		foreach ($this->getOrders($block) as $order)
		{
			$response = array
			(
				'ecommerce' => array
				(
					'currencyCode' => $this->getStore()->getCurrentCurrencyCode(),
					'purchase' 	   => array
					(
						'actionField' => array
						(
							'id' 			=> 			$order->getIncrementId(),
							'revenue' 		=> 			$order->getBaseGrandTotal(),
							'tax'			=> 			$order->getBaseTaxAmount(),
							'shipping' 		=> 			$order->getBaseShippingAmount(),
							'coupon'		=> (string) $order->getCouponCode(),
							'affiliation' 	=> (string) $this->getStore()->getName()
						),
						'products' => array()
					)
				)
			);
			
			if ($order->getIsVirtual())
			{
				$address = $order->getBillingAddress();
			}
			else
			{
				$address = $order->getShippingAddress();
			}

			foreach ($order->getAllVisibleItems() as $item)
			{
				$variant = array();
				
				$category = $this->registry->registry('current_category');
				
				if (!$category)
				{
					/**
					 * Get all product categories
					 */
					$categories = $item->getProduct()->getCategoryIds();
						
					/**
					 * Load last category
					*/
					$category = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Category')->load
					(
						end($categories)
					);
				}
				
				$data = new \Magento\Framework\DataObject(array
				(
					'id' 		=> 		 $item->getSku(),
					'name' 		=> 		 $item->getName(),
					'price' 	=> 		 $item->getBasePrice(),
					'quantity' 	=> (int) $item->getQtyOrdered(),
					'category'	=> 		 $this->getCategory($category),
					'brand'		=> 		 $this->getBrand
					(
						$item->getProduct()
					)
				));
				
				if ('configurable' == $item->getProduct()->getTypeId())
				{
					$variant = array();

					/**
					 * Get info buy request 
					 * 
					 * @var \Magento\Framework\DataObject
					 */
            		$info = new \Magento\Framework\DataObject($item->getProductOptionByCode('info_buyRequest'));
            			
            		/**
            		 * Construct variant
            		 */
					foreach ($info->getSuperAttribute() as $id => $option)
					{
						/**
						 * @todo: Pull attribute data
						 */
						$attribute = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($id);
						
						if ($attribute->usesSource())
						{
							$variant[] = join(':', array
							(
								$attribute->getFrontendLabel(),
								$attribute->getSource()->getOptionText($option)
							));
						}
					}
					
					$data->setId
					(
						$item->getProduct()->getSku()
					);
					
					$data->setName
					(
						$item->getProduct()->getName()
					);
					
					$product = $this->productRepository->getById
					(
						$item->getProductId()
					);

					if (false)
					{
						/**
						 * Get parents
						 *
						 * @var array
						 */
						$parents = (array) \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild
						(
							$product->getId()
						);
							
						$configurable = $this->productRepository->getById
						(
							end($parents)
						);
					}
					
					/**
					 * Push variant to data
					 *
					 * @var array
					*/
					$data->setVariant(join(chr(44), $variant));
				}
			
				$response['ecommerce']['purchase']['products'][] = $data->getData();
			}				
		}
		
		return json_encode($response, JSON_PRETTY_PRINT);
	}
	
	public function getPurchaseGoogleTagParams($block)
	{
		$google_tag_params = (object) array
		(
			'ecomm_prodid' 			=> array(),
			'ecomm_pvalue' 			=> array(),
			'ecomm_pname' 			=> array(),
			'ecomm_totalvalue' 		=> 0
		);
		
		foreach ($this->getOrders($block) as $order)
		{
			foreach ($order->getAllVisibleItems() as $item)
			{
				$data = new \Magento\Framework\DataObject(array
				(
					'id'  	=> $this->escape($item->getSku()),
					'name' 	=> $this->escape($item->getName()),
					'price' => $item->getPrice()
				));
				
				/**
				 * Change values if configurable
				 */
				if ('configurable' == $item->getProduct()->getTypeId())
				{
					$data->setId
					(
						$this->escape($item->getProduct()->getSku())
					);
					
					$data->setName
					(
						$this->escape($item->getProduct()->getName())
					);
				}
				
				$google_tag_params->ecomm_prodid[] 		= $data->getId();
				$google_tag_params->ecomm_pvalue[] 		= $data->getPrice();
				$google_tag_params->ecomm_pname[] 		= $data->getName();
			}
			
			/**
			 * Set total value
			 */
			$google_tag_params->ecomm_totalvalue = $order->getBaseGrandTotal();
		}
		
		return $google_tag_params;
	}
	
	public function getOrders($block)
	{
		if (!$this->_orders)
		{
			$orderIds = $block->getOrderIds();
					
			if (empty($orderIds) || !is_array($orderIds))
			{
				return null;
			}
				
			$collection = $block->getSalesOrderCollection()->create();
				
			/**
			 * Filter applicable order ids
			*/
			$collection->addFieldToFilter('entity_id', ['in' => $orderIds]);
				
			foreach ($collection as $order)
			{
				$this->_orders[] = $order;
			}
		}
		
		return $this->_orders;
	}
	
	public function getSearchPush($block)
	{
		
		try 
		{
			$list = $block->getLayout()->getBlock('search_result_list');
			
			if ($list)
			{
				$response = array
				(
					'ecommerce' => array
					(
						'actionField' => array
						(
							'list' => __('Search Results')
						),
						'impressions' => array()
					)
				);
				
				$position = 1;
	
				$data = array();
				
				
				foreach ($this->getLoadedCollection($list) as $product)
				{
					$response['ecommerce']['impressions'][] = array
					(
						'list' 			=> __('Search Results')->__toString(),
						'category'		=> __('Search Results')->__toString(),
						'id'			=> $product->getSku(),
						'name'			=> $product->getName(),
						'brand'			=> $this->getBrand
						(
							$product
						),
						'price'			=> $this->getPrice($product),
						'position'		=> $position++
					);
				}
			}

			return (object) 
			[
				'push' 				=> json_encode($response, JSON_PRETTY_PRINT),
				'google_tag_params' => array
				(
					'ecomm_pagetype' 	=> 'category',
					'ecomm_category'	=> __('Search Results')
				)
			];
		}
		catch (\Exception $e)
		{
			
		}
		
		return false;
	}

	public function getImpressionPush($block, $category)
	{
		if (self::USE_ASYNC_EVENTS)
		{
			$response = array
			(
				'ecommerce' => array
				(
					'impressions' => array()	
				)
			);
	
			$position = 1;
			
			foreach ($block->getLoadedProductCollection() as $product)
			{
				$response['ecommerce']['impressions'][] = array
				(
					'list' 			=> $this->getCategoryList($category),
					'id'			=> $product->getSku(),
					'name'			=> $product->getName(),
					'category'		=> $this->getCategory($category),
					'brand'			=> $this->getBrand
					(
						$product
					),
					'price'			=> $this->getPrice($product),
					'position'		=> $position++
				);
			}
			
			if ($this->afterBody())
			{
				$response['event'] = 'impression';
			}
			
			return json_encode($response, JSON_PRETTY_PRINT);
		}
		
		return false;
	}
	
	/**
	 * Get visitor push
	 * 
	 * @param unknown $block
	 */
	public function getVisitorPush($block)
	{
		/**
		 * Get customer group
		 */
		
		$data = array
		(
			'visitorLoginState' 		=> $this->isLogged() ? __('Logged in') : __('Logged out'),
			
			'visitorLifetimeValue' 		=> 0,
			'visitorExistingCustomer' 	=> __('No')
		);
		
		if ($this->isLogged())
		{
			$data['visitorId'] = $this->getCustomer()->getId();
			
			/**
			 * Get customer order(s)
			 * 
			 * @var array
			 */
			$orders = $this->orderCollectionFactory->create()->addFieldToSelect('*')->addFieldToFilter('customer_id', $this->getCustomer()->getId())->addFieldToFilter('status',
			[
				'in' => $this->orderConfig->getVisibleOnFrontStatuses()
			]
			)->setOrder('created_at','desc');
			
			$total = 0;
			
			foreach ($orders as $order)
			{
				$total += $order->getGrandTotal();
			}
	
			$data['visitorLifetimeValue'] = $total;
			
			if ($total > 0)
			{
				$data['visitorExistingCustomer'] = __('Yes');
			}
			
			$group = $this->groupRegistry->retrieve
			(
				$this->getCustomer()->getGroupId()
			);

			$data['visitorType'] = $group->getCustomerGroupCode();
		}
		else 
		{
			$group = $this->groupRegistry->retrieve(0);
			
			$data['visitorType'] = $group->getCustomerGroupCode();
		}
		
		return json_encode($data, JSON_PRETTY_PRINT);
	}
	
	/**
	 * Check if customer is logged in
	 */
	public function isLogged()
	{
		if ($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH))
		{
			return true;
		}
		else if($this->session->isLoggedIn())
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get customer
	 */
	public function getCustomer()
	{
		if (!$this->customer)
		{
			if ($this->registry->registry('cache_session_customer_id') > 0)
			{
				$this->customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Customer')->load($this->registry->registry('cache_session_customer_id'));
			}
		}
	
		return $this->customer;
	}
	
	/**
	 * Get Super Attributes
	 */
	public function getSuper()
	{
		$super = array();
		
		if ($this->registry->registry('current_product'))
		{
			$product = $this->registry->registry('current_product');
			
			if ('configurable' == $product->getTypeId())
			{
				$attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
								 	
			 	foreach($attributes as $attribute)
			 	{
			 		$object = $attribute->getProductAttribute();
			 		 
			 		$super[] = array
			 		(
			 			'id' 	=> $object->getAttributeId(),
			 			'label' => $object->getStoreLabel(),
			 			'code'	=> $object->getAttributeCode()
			 		);
			 	}
			}
		}
	 	
	 	return json_encode($super, JSON_PRETTY_PRINT);
	}
	
	/**
	 * Get final price of product 
	 * 
	 * @param \Magento\Catalog\Model\Product $product
	 */
	public function getPrice(\Magento\Catalog\Model\Product $product)
	{
		$price = $this->catalogData->getTaxPrice($product, $product->getPriceInfo()->getPrice('final_price')->getValue(), true,null,null,null, null,null,false);
		
		return number_format($price,2);
	}
	
	/**
	 * Get category 
	 * 
	 * @param \Magento\Catalog\Model\Category $category
	 */
	public function getCategory(\Magento\Catalog\Model\Category $category)
	{
		return $category->getName();
	}
	
	/**
	 * Get category list name
	 * 
	 * @param \Magento\Catalog\Model\Category $category
	 */
	public function getCategoryList(\Magento\Catalog\Model\Category $category)
	{
		return $category->getName();
	}
	
	/**
	 * Get product brand 
	 * 
	 * @param \Magento\Catalog\Model\Product $product
	 */
	public function getBrand(\Magento\Catalog\Model\Product $product)
	{
		switch ($product->getTypeId())
		{
			case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
			case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL: return (string) $product->getAttributeText('manufacturer'); 
		}
		
		/**
		 * Return empty brand
		 */
		return '';
	}
	
	public function getObjectManager()
	{
		return \Magento\Framework\App\ObjectManager::getInstance();
	}
	
	public function getStore()
	{
		return $this->getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')->getStore();
	}
	
	public function afterBody()
	{
		return $this->getConfig('ec/general/code');
	}

	public function isActive()
	{
		return 0 !== (int) $this->getConfig('ec/general/active');
	}
	
	/**
	 * Escape quotes
	 * 
	 * @param string $string
	 * @return string
	 */
	public function escape($data)
	{
		return addcslashes($data, '\'');
	}
	
	/**
	 * Escape string for HTML5 data attribute 
	 * 
	 * @param string $data
	 * @return string
	 */
	public function escapeDataArgument($data)
	{
		return str_replace(array('"','\''), array('&quot;','&apos;'), $data);
	}
}
