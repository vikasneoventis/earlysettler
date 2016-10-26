<?php
/**
 * Copyright © 2016 Balance Internet Pty., Ltd. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Balance\Core\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;

class Theme extends Data
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvReader;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**#@+
     * Constants defined for keys of data array
     */
    const MODULE_NAME = 'Earlysettler_Cms';

    /**
     * Theme helper constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface  $storeManager
     * @param StringUtils            $stringUtils
     * @param SampleDataContext      $sampleDataContext
     * @param ModuleListInterface    $moduleList
     * @param File                   $file
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        StringUtils $stringUtils,
        ModuleListInterface $moduleList,
        SampleDataContext $sampleDataContext,
        File $file
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->file = $file;
        parent::__construct($context, $objectManager, $storeManager, $stringUtils, $moduleList);
    }

    /**
     * Retrieve file id content from fixture
     *
     * @param string $fixture
     *
     * @return string|bool
     */
    public function getFixtureContent($fixture)
    {
        $fileContent = false;
        $fileId = $this->fixtureManager->getFixture($fixture);
        if ($this->file->isExists($fileId)) {
            //$fileNameParts = explode('/', $fileId);
            //$fileNameParts[3] = $fileNameParts[2];
            //$fileNameParts[2] = $this->getVersion();
            //$fileName = implode('/', $fileNameParts);
            //$fileContent = $this->file->fileGetContents($fileName);
            $fileContent = $this->file->fileGetContents($fileId);
        }

        return $fileContent;
    }
}
