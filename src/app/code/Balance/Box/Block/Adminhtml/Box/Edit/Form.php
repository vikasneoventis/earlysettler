<?php

namespace Balance\Box\Block\Adminhtml\Box\Edit;

/**
 * Adminhtml box edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Balance\Box\Model\Box\Source\Group
     */
    protected $_groupSource;

    /**
     * @var \Balance\Box\Model\Box\Source\Layout
     */
    protected $_layoutSource;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Balance\Box\Model\Box\Source\Group $groupSource
     * @param \Balance\Box\Model\Box\Source\Layout $layoutSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        \Balance\Box\Model\Box\Source\Group $groupSource,
        \Balance\Box\Model\Box\Source\Layout $layoutSource,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        $this->_groupSource = $groupSource;
        $this->_layoutSource = $layoutSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('box_form');
        $this->setTitle(__('Box Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Balance\Box\Model\Box $model */
        $model = $this->_coreRegistry->registry('box_box');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $form->setHtmlIdPrefix('box_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldsetImages = $form->addFieldSet(
            'images_fieldset',
            [
                'legend' => __('Images'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldsetImages->addType('image', '\Balance\Box\Block\Adminhtml\Box\Helper\Image');

        $fieldsetContent = $form->addFieldset(
            'content_fieldset',
            [
                'legend' => __('Content'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldsetPublishing = $form->addFieldset(
            'publishing_fieldset',
            [
                'legend' => __('Publishing'),
                'class' => 'fieldset-wide'
            ]
        );

        if ($model->getBoxId()) {
            $fieldset->addField('box_id', 'hidden', ['name' => 'box_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Box Title'),
                'title' => __('Box Title'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name' => 'identifier',
                'label' => __('Identifier'),
                'title' => __('Identifier'),
                'required' => true,
                'class' => 'validate-xml-identifier'
            ]
        );

        $fieldset->addField(
            'group_id',
            'select',
            [
                'name' => 'group_id',
                'label' => __('Group'),
                'title' => __('Group'),
                'options' => $this->_groupSource->toFlatArray(true),
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name'  => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'type'  => 'number',
                'class' => 'validate-number'
            ]
        );

        $fieldsetImages->addField(
            'desktop_image',
            'image',
            [
                'name' => 'desktop_image',
                'label' => __('Desktop Image'),
                'title' => __('Desktop Image')
            ]
        );

        $fieldsetImages->addField(
            'mobile_image',
            'image',
            [
                'name' => 'mobile_image',
                'label' => __('Mobile Image'),
                'title' => __('Mobile Image')
            ]
        );

        $fieldsetImages->addField(
            'alt_text',
            'text',
            [
                'name'  => 'alt_text',
                'label' => __('Alt Text'),
                'title' => __('Alt Text')
            ]
        );


        $fieldsetContent->addField(
            'layout',
            'select',
            [
                'name'    => 'layout',
                'label'   => __('Layout'),
                'title'   => __('Layout'),
                'options' => $this->_layoutSource->toFlatArray(true),
            ]
        );

        $fieldsetContent->addField(
            'heading',
            'text',
            [
                'name'  => 'heading',
                'label' => __('Heading'),
                'title' => __('Heading')
            ]
        );

        $fieldsetContent->addField(
            'link',
            'text',
            [
                'name'  => 'link',
                'label' => __('Link'),
                'title' => __('Link'),
                'note'  => 'Excluding base URL'
            ]
        );

        $fieldsetContent->addField(
            'button_text',
            'text',
            [
                'name'  => 'button_text',
                'label' => __('Button Text'),
                'title' => __('Button Text')
            ]
        );

        $fieldsetContent->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'style' => 'height:36em',
                'config' => $this->_wysiwygConfig->getConfig()
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldsetPublishing->addField(
            'from_date',
            'date',
            [
                'name'   => 'from_date',
                'label'  => __('From Date'),
                'title'  => __('From Date'),
                'time'   => true,
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        $fieldsetPublishing->addField(
            'to_date',
            'date',
            [
                'name'   => 'to_date',
                'label'  => __('To Date'),
                'title'  => __('To Date'),
                'time'   => true,
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        $fieldsetPublishing->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
