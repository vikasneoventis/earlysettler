<?php
namespace Balance\Box\Block;

use \Balance\Box\Model\Group as GroupModel;
use \Balance\Box\Model\Box as BoxModel;

class Group
    extends \Magento\Framework\View\Element\Template
    implements \Magento\Framework\DataObject\IdentityInterface
{


    const DEFAULT_TEMPLATE = 'Balance_Box::group.phtml';


    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Balance\Box\Model\GroupFactory $groupFactory
     * @param \Balance\Box\Model\ResourceModel\Box\CollectionFactory $boxCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Balance\Box\Model\GroupFactory $groupFactory,
        \Balance\Box\Model\ResourceModel\Box\CollectionFactory $boxCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_groupFactory = $groupFactory;
        $this->_boxCollectionFactory = $boxCollectionFactory;

        if (!$this->hasData('template')) {
            $this->setTemplate(self::DEFAULT_TEMPLATE);
        }
    }


    /**
     * @return \Balance\Box\Model\Group
     */
    public function getGroup() {
        if (!$this->hasData('group')) {
            /** @var \Balance\Box\Model\Group $group */
            $group = $this->_groupFactory->create();
            if ($identifier = $this->getData(GroupModel::IDENTIFIER)) {
                $group->load($identifier, GroupModel::IDENTIFIER);
                $this->setData('group', $group);
            } elseif ($id = $this->getData(GroupModel::GROUP_ID)) {
                $group->load($id, GroupModel::GROUP_ID);
                $this->setData('group', $group);
            }
        }
        return $this->getData('group');
    }


    /**
     * @return \Balance\Box\Model\ResourceModel\Box\Collection
     */
    public function getBoxes() {
        if (!$this->hasData('boxes')) {
            if ($group = $this->getGroup()) {
                $boxes = $this->_boxCollectionFactory
                    ->create()
                    ->addFieldToFilter(BoxModel::GROUP_ID, $group->getId())
                    ->addOrder(BoxModel::POSITION, \Balance\Box\Model\ResourceModel\Box\Collection::SORT_ORDER_ASC)
                ;
                if ($boxes->count()) {
                    $this->setData('boxes', $boxes);
                }
            }
        }
        return $this->getData('boxes');
    }


    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities() {
        $cacheTag = GroupModel::CACHE_TAG;
        if ($group = $this->getGroup()) {
            $cacheTag .= '_' . $group->getId();
        }
        if ($boxes = $this->getBoxes()) {
            foreach ($boxes as $box) {
                $cacheTag .= '_' . $box->getId();
            }
        }
        return [$cacheTag];
    }

}
