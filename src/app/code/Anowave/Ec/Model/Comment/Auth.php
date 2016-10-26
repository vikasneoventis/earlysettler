<?php
/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2016 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */

namespace Anowave\Ec\Model\Comment;

use Anowave\Ec\Model\Comment;

/**
 * Google Analytics module observer
 *
 */
 
class Auth extends Comment
{
	public function getCommentText($currentValue)
	{
		return $this->blockFactory->createBlock('Anowave\Ec\Block\Comment')->setTemplate('auth.phtml')->setData
		(
			array
			(
				'api' => $this->getApi()
			)
		)->toHtml();
	}
}