<?php
namespace Balance\Box\Block;

use \Balance\Box\Model\Box as BoxModel;

class Box
    extends \Magento\Framework\View\Element\Template
    implements \Magento\Framework\DataObject\IdentityInterface
{


    const DEFAULT_TEMPLATE = 'Balance_Box::single.phtml';


    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Balance\Box\Model\BoxFactory $boxFactory
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Balance\Box\Model\BoxFactory $boxFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_boxFactory = $boxFactory;

        if (!$this->hasData('template')) {
            $this->setTemplate(self::DEFAULT_TEMPLATE);
        }
    }


    public function _prepareLayout() {
        return parent::_prepareLayout();
    }


    /**
     * @return \Balance\Box\Model\Box
     */
    public function getBox() {
        if (!$this->hasData('box')) {
            /** @var \Balance\Box\Model\Box $box */
            $box = $this->_boxFactory->create();
            if ($identifier = $this->getData(BoxModel::IDENTIFIER)) {
                $box->load($identifier, BoxModel::IDENTIFIER);
                $this->setData('box', $box);
            } elseif ($id = $this->getData(BoxModel::BOX_ID)) {
                $box->load($id, BoxModel::BOX_ID);
                $this->setData('box', $box);
            }
        }
        return $this->getData('box');
    }


    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities() {
        $cacheTag = BoxModel::CACHE_TAG;
        if ($box = $this->getBox()) {
            $cacheTag .= '_' . $box->getId();
        }
        return [$cacheTag];
    }

}
