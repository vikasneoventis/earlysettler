<?php

namespace Balance\Box\Model\ResourceModel;

/**
 * Box mysql resource
 */
class Box extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('balance_box_single', 'box_id');
    }

    /**
     * Process box data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {

        if (!$this->isValidBoxIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The box identifier contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericBoxIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The box identifier cannot be made of only numbers.')
            );
        }

        if (!$object->getGroupId()) {
            $object->setGroupId(null);
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

        $select->where(
            'is_active = ?',
            1
        )->limit(
            1
        );

        return $select;
    }

    /**
     * Retrieve load select with filter by identifier and activity
     *
     * @param string $identifier
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $isActive = null) {
        $select = $this->getConnection()->select()->from(
            ['bbs' => $this->getMainTable()]
        )->where(
            'bbs.identifier = ?',
            $identifier
        );

        if (!is_null($isActive)) {
            $select->where('bbs.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether box identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericBoxIdentifier(\Magento\Framework\Model\AbstractModel $object) {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether box identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidBoxIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9_-]+$/', $object->getData('identifier'));
    }

    /**
     * Check if box identifier exists
     * return box id if box exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier)
    {
        $select = $this->_getLoadByIdentifierSelect($identifier, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('bbs.box_id')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}
