<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Label\Controller\Adminhtml\Labels;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Cms\Controller\Adminhtml\Block\MassDelete
{
    /**
     * Field id
     */
    const ID_FIELD = 'label_id';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Amasty\Label\Model\Labels\Collection';

    /**
     * Block model
     *
     * @var string
     */
    protected $model = 'Amasty\Label\Model\Labels';
}
