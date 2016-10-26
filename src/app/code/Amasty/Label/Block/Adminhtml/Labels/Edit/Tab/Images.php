<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Amasty\Label\Block\Adminhtml\Labels\Edit\Tab;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\System\Store;

/**
 * Cart Price Rule General Information Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Images extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_salesRule;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleFactory $salesRule
     * @param ObjectConverter $objectConverter
     * @param Store $systemStore
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleFactory $salesRule,
        ObjectConverter $objectConverter,
        Store $systemStore,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Amasty\Label\Helper\Data $helper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_objectConverter = $objectConverter;
        $this->_salesRule = $salesRule;
        $this->groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Images');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Images');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_label');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('labels_');

        $fldProduct = $form->addFieldset('product_page', array('legend'=> __('Product Page')));
        // {ATTR:code} - attribute value, {STOCK_QTY} - quantity in stock
        $note = 'Variables: {ATTR:code} - attribute value, {SAVE_PERCENT} - save percents, {SAVE_AMOUNT} - save amount, {PRICE} - price, {SPECIAL_PRICE} special price, {BR} - new line, {NEW_FOR} - how may days ago the product was added, {SKU} - product SKU';

        $fldProduct->addField('prod_txt', 'text', array(
            'label'     => __('Text'),
            'name'      => 'prod_txt',
            'note'      => __($note),
        ));

        $fldProduct->addField('prod_img', 'file', array(
            'label'     => __('Image'),
            'name'      => 'prod_img',
            'after_element_html' => $this->getImageHtml('prod_img', $model->getProdImg()),
        ));

        $fldProduct->addField('prod_pos', 'select', array(
            'label'     => __('Position'),
            'name'      => 'prod_pos',
            'values'    => $model->getAvailablePositions(),
        ));
        $fldProduct->addField('prod_style', 'text', array(
            'label'     => __('Label Block Styles'),
            'name'      => 'prod_style',
        ));

        $fldProduct->addField('prod_text_style', 'text', array(
            'label'     => __('Text Style'),
            'name'      => 'prod_text_style',
            'note'      => __('Example: color:red;font-size:12px;'),
        ));

        $fldProduct->addField('prod_image_size', 'text', array(
            'label'     => __('Label Size'),
            'name'      => 'prod_image_size',
            'note'      => __('Percent of the product image.'),
        ));

        $fldCat = $form->addFieldset('category_page', array('legend'=> __('Category Page')));
        $fldCat->addField('cat_txt', 'text', array(
            'label'     => __('Text'),
            'name'      => 'cat_txt',
            'note'      => __($note),
        ));
        $fldCat->addField('cat_img', 'file', array(
            'label'     => __('Image'),
            'name'      => 'cat_img',
            'after_element_html' => $this->getImageHtml('cat_img', $model->getCatImg()),
        ));
        $fldCat->addField('cat_pos', 'select', array(
            'label'     => __('Position'),
            'name'      => 'cat_pos',
            'values'    => $model->getAvailablePositions(),
        ));
        $fldCat->addField('cat_style', 'text', array(
            'label'     => __('Label Block Styles'),
            'name'      => 'cat_style',
        ));

        $fldCat->addField('cat_text_style', 'text', array(
            'label'     => __('Text Style'),
            'name'      => 'cat_text_style',
            'note'      => __('Example: color:red;font-size:12px;'),
        ));

        $fldCat->addField('cat_image_size', 'text', array(
            'label'     => __('Label Size'),
            'name'      => 'cat_image_size',
            'note'      => __('Percent of the product image.'),
        ));

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function getImageHtml($field, $img)
    {
        $html = '';
        if ($img) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<img style="max-width:300px" src="' . $this->_helper->getImageUrl($img) . '" />';
            $html .= '<br/><input type="checkbox" value="1" name="remove_' . $field . '"/> ' . __('Remove');
            $html .= '<input type="hidden" value="' . $img . '" name="old_' . $field . '"/>';
            $html .= '</p>';
        }
        return $html;
    }
}
