<?php

namespace Balance\Box\Model\ResourceModel;

/**
 * Group mysql resource
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('balance_box_group', 'group_id');
    }

    /**
     * Process group data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {

        if (!$this->isValidGroupIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The box identifier contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericGroupIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The box identifier cannot be made of only numbers.')
            );
        }

        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null) {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Balance\Box\Model\Box $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);

        return $select;
    }

    /**
     * Retrieve load select with filter by identifier
     *
     * @param string $identifier
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadByIdentifierSelect($identifier) {
        $select = $this->getConnection()->select()->from(
            ['bbg' => $this->getMainTable()]
        )->where(
            'bbg.identifier = ?',
            $identifier
        );

        return $select;
    }

    /**
     *  Check whether group identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericGroupIdentifier(\Magento\Framework\Model\AbstractModel $object) {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether group identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidGroupIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9_-]+$/', $object->getData('identifier'));
    }

    /**
     * Check if group identifier exists
     * return group id if box exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier)
    {
        $select = $this->_getLoadByIdentifierSelect($identifier, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('bbg.box_id')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}
