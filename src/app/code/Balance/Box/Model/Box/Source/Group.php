<?php

namespace Balance\Box\Model\Box\Source;

class Group implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Balance\Box\Model\Group
     */
    protected $group;

    /**
     * @var array
     */
    protected $_options = [];

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
        if (!$this->_options) {
            $groups = $this->group->getCollection();
            foreach($groups as $group) {
                $this->_options[] = [
                    'label' => $group->getData(\Balance\Box\Model\Group::TITLE),
                    'value' => $group->getData(\Balance\Box\Model\Group::GROUP_ID),
                ];
            }
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

    public function toFlatArray($nullValue = false) {
        $flat = [];
        if($nullValue) {
            $flat[''] = __('None');
        }
        foreach($this->toOptionArray() as $option) {
            $flat[$option['value']] = $option['label'];
        }
        return $flat;
    }
}
