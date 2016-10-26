<?php

/**
 * Product:       Xtento_ProductExport (2.1.0)
 * ID:            G7CD++h4QdnedP40rkoUgTXd092YAO8jXoxYTRFnMyk=
 * Packaged:      2016-10-16T09:33:21+00:00
 * Last Modified: 2016-04-17T12:35:36+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Condition/Product.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Condition;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $catalogProductType;

    /**
     * Product constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        array $data = []
    ) {
        $this->catalogProductType = $catalogProductType;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
        $this->setType('Xtento\ProductExport\Model\Export\Condition\Product');
    }

    public function getValueSelectOptions()
    {
        switch ($this->getAttribute()) {
            case 'type_id':
                return $this->catalogProductType->getOptions();
        }
        return parent::getValueSelectOptions();
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'type_id':
                return 'select';
        }
        return parent::getInputType();
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'type_id':
                return 'select';
        }
        return parent::getValueElementType();
    }

    /**
     * Load attribute options
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute->isAllowedForRuleCondition(
            ) /* || !$attribute->getDataUsingMethod($this->_isUsedForRuleProperty)*/
            ) {
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        // Add custom attributes
        $attributes['qty'] = __('Quantity in stock');
        $attributes['type_id'] = __('Product Type');

        // Remove certain attributes
        foreach ($attributes as $attributeCode => $label) {
            if (preg_match('/^quote_item_/', $attributeCode)) {
                unset($attributes[$attributeCode]);
            }
        }

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        #Zend_Debug::dump($object->getData());
        #Zend_Debug::dump("result: ".$this->validateAttribute($object->getData($this->getAttribute())), "expected: ".$object->getData($this->getAttribute()));
        if ($this->getAttribute() == 'category_ids') {
            // Load category_ids before validation
            $object->getCategoryIds();
        }
        return $this->validateAttribute($object->getData($this->getAttribute()));
    }
}
