<?php

namespace Balance\Box\Model\Box\Source;

class Box implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Balance\Box\Model\Box
     */
    protected $box;

    /**
     * Constructor
     *
     * @param \Balance\Box\Model\Box $box
     */
    public function __construct(\Balance\Box\Model\Box $box) {
        $this->box = $box;
    }

    /**
     * Get Boxes
     *
     * @return array
     */
    public function getAllOptions() {
        $boxes = $this->box->getCollection();
        foreach($boxes as $box) {
            $this->_options[] = [
                'label' => $box->getData(\Balance\Box\Model\Box::TITLE),
                'value' => $box->getData(\Balance\Box\Model\Box::BOX_ID),
            ];
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }
}
