<?php
namespace Balance\Box\Model\Box;

class Layout
    extends \Magento\Framework\Model\AbstractModel
{


    /**
     * @var array
     */
    protected $_layouts;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getAll () {
        if (!$this->_layouts) {
            $this->_layouts = $this->_scopeConfig->getValue('balance_box/layouts');
        }
        return $this->_layouts;
    }


    /**
     * @return mixed
     */
    protected function getLayoutAttribute ($code, $attribute) {
        $layouts = $this->getAll();
        if (isset($layouts[$code]) && isset($layouts[$code][$attribute])) {
            return $layouts[$code][$attribute];
        }
    }


    /**
     * @return string
     */
    public function getLabel ($code) {
        return $this->getLayoutAttribute($code, 'label');
    }


    /**
     * @return string
     */
    public function getClass ($code) {
        return $this->getLayoutAttribute($code, 'class');
    }


}
