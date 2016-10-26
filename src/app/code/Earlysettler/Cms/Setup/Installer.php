<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Earlysettler\Cms\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Earlysettler\Cms\Model\Page
     */
    private $page;

    /**
     * @var \Earlysettler\Cms\Model\Block
     */
    private $block;

    /**
     * @param \Earlysettler\Cms\Model\Page  $page
     * @param \Earlysettler\Cms\Model\Block $block
     */
    public function __construct(
        \Earlysettler\Cms\Model\Page $page,
        \Earlysettler\Cms\Model\Block $block
    ) {
        $this->page = $page;
        $this->block = $block;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->page->install(['Earlysettler_Cms::fixtures/pages/pages.csv']);
        $this->block->install(
            [
                'Earlysettler_Cms::fixtures/blocks/pages_static_blocks.csv'
            ]
        );
    }
}