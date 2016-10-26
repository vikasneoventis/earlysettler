<?php
namespace Balance\Box\Api\Data;

interface BoxInterface
{
    const BOX_ID        = 'box_id';
    const TITLE         = 'title';
    const IDENTIFIER    = 'identifier';
    const GROUP_ID      = 'group_id';
    const DESKTOP_IMAGE = 'desktop_image';
    const MOBILE_IMAGE  = 'mobile_image';
    const ALT_TEXT      = 'alt_text';
    const HEADING       = 'heading';
    const CONTENT       = 'content';
    const LINK          = 'link';
    const BUTTON_TEXT   = 'button_text';
    const LAYOUT        = 'layout';
    const FROM_DATE     = 'from_date';
    const TO_DATE       = 'to_date';
    const POSITION      = 'position';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';
    const IS_ACTIVE     = 'is_active';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier();

    /**
     * Get group ID
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get desktop image
     *
     * @return string|null
     */
    public function getDesktopImage();

    /**
     * Get mobile image
     *
     * @return string|null
     */
    public function getMobileImage();

    /**
     * Get alt text
     *
     * @return string|null
     */
    public function getAltText();

    /**
     * Get heading
     *
     * @return string|null
     */
    public function getHeading();

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Get link
     *
     * @return string|null
     */
    public function getLink();

    /**
     * Get button text
     *
     * @return string|null
     */
    public function getButtonText();

    /**
     * Get layout
     *
     * @return string|null
     */
    public function getLayout();

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate();

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate();

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setId($id);

    /**
     * Set title
     *
     * @param int $title
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setTitle($title);

    /**
     * Set identifier
     *
     * @param int $identifier
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set group ID
     *
     * @param $group_id
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setGroupId($group_id);

    /**
     * Set desktop image
     *
     * @param $desktop_image
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setDesktopImage($desktop_image);

    /**
     * Set mobile image
     *
     * @param $mobile_image
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setMobileImage($mobile_image);

    /**
     * Set alt text
     *
     * @param $alt_text
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setAltText($alt_text);

    /**
     * Set heading
     *
     * @param $heading
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setHeading($heading);

    /**
     * Set content
     *
     * @param $content
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setContent($content);

    /**
     * Set link
     *
     * @param $link
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setLink($link);

    /**
     * Set button text
     *
     * @param $button_text
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setButtonText($button_text);

    /**
     * Set layout
     *
     * @param $layout
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setLayout($layout);

    /**
     * Set from date
     *
     * @param $from_date
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setFromDate($from_date);

    /**
     * Set to date
     *
     * @param $to_date
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setToDate($to_date);

    /**
     * Set position
     *
     * @param $position
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setPosition($position);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Balance\Box\Api\Data\BoxInterface
     */
    public function setIsActive($isActive);
}
