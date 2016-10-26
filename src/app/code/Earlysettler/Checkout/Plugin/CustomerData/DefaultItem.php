<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Earlysettler\Checkout\Plugin\CustomerData;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Magento\Checkout\Model\Session;
/**
 * Default item
 */
class DefaultItem
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_productRepository;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Catalog\\Product\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $session,
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->_session = $session;
        $this->_productRepository = $productRepository;
        $this->_checkoutHelper = $checkoutHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function afterGetItemData($subject, $result)
    {
        if (isset($result['item_id'])) {
            /* Get item */
            $item = $this->_session->getQuote()->getItemById($result['item_id']);

            /* Get product */
            $product = $this->_productRepository->getById($item->getProduct()->getId());

            if ($product->getPrice() > $result['product_price_value']) {
                $result['product_price_original'] = $this->_checkoutHelper->formatPrice($product->getPrice());
            }
        }
        return $result;
    }
}
