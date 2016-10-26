<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Earlysettler\Cms\Model;

use Balance\Core\Helper\Theme as ThemeHelper;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class Page
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ThemeHelper
     */
    protected $theme;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param PageFactory       $pageFactory
     * @param ThemeHelper       $helper
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        PageFactory $pageFactory,
        ThemeHelper $helper
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->pageFactory = $pageFactory;
        $this->theme = $helper;
    }

    /**
     * @param array $fixtures
     *
     * @throws \Exception
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                $row['content'] = $this->theme->getFixtureContent('Earlysettler_Cms::fixtures/pages/'
                    . $row['content']);
                $this->pageFactory->create()
                    ->load($row['identifier'], 'identifier')
                    ->addData($row)
                    ->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID])
                    ->save();
            }
        }
    }
}
