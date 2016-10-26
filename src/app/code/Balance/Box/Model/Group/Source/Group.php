<?php

namespace Balance\Box\Model\Group\Source;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Balance\Box\Model\Group
     */
    protected $group;

    /**
     * Constructor
     *
     * @param \Balance\Box\Model\Group $group
     */
    public function __construct(\Balance\Box\Model\Group $group) {
        $this->group = $group;
    }

    /**
     * Get Groups
     *
     * @return array
     */
    public function getAllOptions() {
        $groups = $this->group->getCollection();
        foreach($groups as $group) {
            $this->_options[] = array(
                'label' => $group->getTitle(),
                'value' => $group->getGroupId()
            );
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }
}