<?php

namespace Balance\Box\Api\Data;

interface GroupInterface
{
    const GROUP_ID      = 'group_id';
    const TITLE         = 'title';
    const IDENTIFIER    = 'identifier';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME   = 'update_time';

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
}