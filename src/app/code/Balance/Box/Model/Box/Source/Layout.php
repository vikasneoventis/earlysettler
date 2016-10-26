<?php
namespace Balance\Box\Model\Box\Source;

class Layout
    implements \Magento\Framework\Data\OptionSourceInterface
{


    /**
     * @var \Balance\Box\Model\Box\Layout
     */
    protected $_layoutModel;


    /**
     * @param \Magento\Framework\View\Element\Context $context
     */
    public function __construct (
        \Balance\Box\Model\Box\Layout $layoutModel
    ) {
        $this->_layoutModel = $layoutModel;
    }


    /**
     * @return array
     */
    protected function getAllOptions () {
        $options = [];

        foreach ($this->_layoutModel->getAll() as $code => $layout) {
            $options[] = [
                'value' => $code,
                'label' => $layout['label'],
            ];
        }

        return $options;
    }


    /**
     * @return array
     */
    public function toOptionArray () {
        return $this->getAllOptions();
    }


    /**
     * @return array
     */
    public function toFlatArray ($empty = false) {
        $flat = [];

        if ($empty) {
            $flat[''] = '';
        }

        foreach($this->toOptionArray() as $option) {
            $flat[$option['value']] = $option['label'];
        }

        return $flat;
    }


}
