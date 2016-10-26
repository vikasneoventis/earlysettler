<?php
namespace Balance\Box\Model;

use Balance\Box\Api\Data\GroupInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Group
    extends \Magento\Framework\Model\AbstractModel
    implements
        \Balance\Box\Api\Data\GroupInterface,
        \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'box_group';

    /**
     * @var string
     */
    protected $_cacheTag = 'box_group';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'box_group';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Balance\Box\Model\ResourceModel\Group');
    }

    /**
     * Check if group identifier exists
     * return group id if group exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier) {
        return $this->_getResource()->checkIdentifier($identifier);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId() {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle() {
        return $this->getData(self::TITLE);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier() {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime() {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime() {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Balance\Box\Api\Data\GroupInterface
     */
    public function setId($id) {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * Set title
     *
     * @param int $title
     * @return \Balance\Box\Api\Data\GroupInterface
     */
    public function setTitle($title) {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set identifier
     *
     * @param int $identifier
     * @return \Balance\Box\Api\Data\GroupInterface
     */
    public function setIdentifier($identifier) {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set creation time
     *
     * @param string $creation_time
     * @return \Balance\Box\Api\Data\GroupInterface
     */
    public function setCreationTime($creation_time) {
        return $this->setData(self::CREATION_TIME, $creation_time);
    }

    /**
     * Set update time
     *
     * @param string $update_time
     * @return \Balance\Box\Api\Data\GroupInterface
     */
    public function setUpdateTime($update_time) {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }
}
