<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Resource\Method;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Amasty\ShippingTableRates\Model\Method',
            'Amasty\ShippingTableRates\Model\Resource\Method'
        );
    }

    public function addStoreFilter($storeId)
    {
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,' . $storeId . ',%"');

        return $this;
    }

    public function addCustomerGroupFilter($groupId)
    {
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,' . $groupId . ',%"');

        return $this;
    }

    public function hashMinRate()
    {
        return $this->_toOptionHash('id', 'min_rate');
    }

    public function hashMaxRate()
    {
        return $this->_toOptionHash('id', 'max_rate');
    }

}
