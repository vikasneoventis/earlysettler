<?php

namespace Balance\Box\Model\Box\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
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
    public function __construct(\Balance\Box\Model\Box $box)
    {
        $this->box = $box;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->box->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}