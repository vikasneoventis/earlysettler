<?php namespace Balance\Box\Model\ResourceModel\Group;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Balance\Box\Model\Group', 'Balance\Box\Model\ResourceModel\Group');
    }

}