<?php
namespace Balance\Box\Model\ResourceModel\Box;

class Collection
    extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Balance\Box\Model\Box', 'Balance\Box\Model\ResourceModel\Box');
    }

}
