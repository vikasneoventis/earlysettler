<?php

namespace Balance\Box\Model;

use Balance\Box\Api\Data\BoxInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Box extends \Magento\Framework\Model\AbstractModel implements BoxInterface, IdentityInterface
{
    /**#@+
     * Box's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'box_single';

    /**
     * @var string
     */
    protected $_cacheTag = 'box_single';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'box_single';

    /**
     * @var \Balance\Box\Model\Box\Image
     */
    protected $_boxImageModel;

    /**
     * @var \Balance\Box\Model\Box\Layout
     */
    protected $_boxLayoutModel;


    /**
     * @param \Balance\Box\Model\Box\Image $boxImageModel
     * @param \Balance\Box\Model\Box\Layout $boxLayoutModel
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Balance\Box\Model\Box\Image $boxImageModel
     * @param array $data
     */
    public function __construct(
        \Balance\Box\Model\Box\Image $boxImageModel,
        \Balance\Box\Model\Box\Layout $boxLayoutModel,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_boxImageModel = $boxImageModel;
        $this->_boxLayoutModel = $boxLayoutModel;
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Balance\Box\Model\ResourceModel\Box');
    }

    /**
     * Check if box identifier exists
     * return box id if box exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier) {
        return $this->_getResource()->checkIdentifier($identifier);
    }

    /**
     * Prepare box's statuses.
     * Available event box_single_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses() {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
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
        return $this->getData(self::BOX_ID);
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
     * Get group ID
     *
     * @return int|null
     */
    public function getGroupId() {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * Get desktop image
     *
     * @return string|null
     */
    public function getDesktopImage() {
        return $this->getData(self::DESKTOP_IMAGE);
    }

    /**
     * Get mobile image
     *
     * @return string|null
     */
    public function getMobileImage() {
        return $this->getData(self::MOBILE_IMAGE);
    }

    /**
     * Get alt text
     *
     * @return string|null
     */
    public function getAltText() {
        return $this->getData(self::ALT_TEXT);
    }

    /**
     * Get heading
     *
     * @return string|null
     */
    public function getHeading() {
        return $this->getData(self::HEADING);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent() {
        return $this->getData(self::CONTENT);
    }

    /**
     * Get link
     *
     * @return string|null
     */
    public function getLink() {
        return $this->getData(self::LINK);
    }

    /**
     * Get button text
     *
     * @return string|null
     */
    public function getButtonText() {
        return $this->getData(self::BUTTON_TEXT);
    }

    /**
     * Get layout
     *
     * @return string|null
     */
    public function getLayout() {
        return $this->getData(self::LAYOUT);
    }

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate() {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate() {
        return $this->getData(self::TO_DATE);
    }

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition() {
        return $this->getData(self::POSITION);
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
     * Is active
     *
     * @return bool|null
     */
    public function isActive() {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setId($id) {
        return $this->setData(self::BOX_ID, $id);
    }

    /**
     * Set title
     *
     * @param int $title
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setTitle($title) {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set identifier
     *
     * @param int $identifier
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setIdentifier($identifier) {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set group ID
     *
     * @param $group_id
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setGroupId($group_id) {
        return $this->setData(self::GROUP_ID, $group_id);
    }

    /**
     * Set desktop image
     *
     * @param $desktop_image
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setDesktopImage($desktop_image) {
        return $this->setData(self::DESKTOP_IMAGE, $desktop_image);
    }

    /**
     * Set mobile image
     *
     * @param $mobile_image
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setMobileImage($mobile_image) {
        return $this->setData(self::MOBILE_IMAGE, $mobile_image);
    }

    /**
     * Set alt text
     *
     * @param $alt_text
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setAltText($alt_text) {
        return $this->setData(self::ALT_TEXT, $alt_text);
    }

    /**
     * Set heading
     *
     * @param $heading
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setHeading($heading) {
        return $this->setData(self::HEADING, $heading);
    }

    /**
     * Set content
     *
     * @param $content
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setContent($content) {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set link
     *
     * @param $link
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setLink($link) {
        return $this->setData(self::LINK, $link);
    }

    /**
     * Set button text
     *
     * @param $button_text
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setButtonText($button_text) {
        return $this->setData(self::BUTTON_TEXT, $button_text);
    }

    /**
     * Set layout
     *
     * @param $layout
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setLayout($layout) {
        return $this->setData(self::LAYOUT, $layout);
    }

    /**
     * Set from date
     *
     * @param $from_date
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setFromDate($from_date) {
        return $this->setData(self::FROM_DATE, $from_date);
    }

    /**
     * Set to date
     *
     * @param $to_date
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setToDate($to_date) {
        return $this->setData(self::TO_DATE, $to_date);
    }

    /**
     * Set position
     *
     * @param $position
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setPosition($position) {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Set creation time
     *
     * @param string $creation_time
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setCreationTime($creation_time) {
        return $this->setData(self::CREATION_TIME, $creation_time);
    }

    /**
     * Set update time
     *
     * @param string $update_time
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setUpdateTime($update_time) {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }

    /**
     * Set is active
     *
     * @param int|bool $is_active
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setIsActive($is_active) {
        return $this->setData(self::IS_ACTIVE, $is_active);
    }

    public function getImageUrl ($field = self::DESKTOP_IMAGE) {
        if ($name = $this->getData($field)) {
            return $this->_boxImageModel->getBaseUrl() . $name;
        }
    }

    public function getDesktopImageUrl () {
        return $this->getImageUrl(self::DESKTOP_IMAGE);
    }

    public function getMobileImageUrl () {
        return $this->getImageUrl(self::MOBILE_IMAGE);
    }

    public function getLayoutClass () {
        return $this->_boxLayoutModel->getClass($this->getData(self::LAYOUT));
    }

}
