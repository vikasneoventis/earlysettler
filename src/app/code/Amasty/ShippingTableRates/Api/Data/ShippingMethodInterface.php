<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Api\Data;

/**
 * Interface ShippingMethodInterface
 * @api
 */
interface ShippingMethodInterface extends \Magento\Quote\Api\Data\ShippingMethodInterface
{
    /**
     * Sets the shipping carrier comment.
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Sets the shipping carrier comment.
     *
     * @return $this
     */
    public function getComment();
}
