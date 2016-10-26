<?php

namespace Balance\Box\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $groupTable = $installer->getConnection()
            ->newTable($installer->getTable('balance_box_group'))
            ->addColumn(
                'group_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Group ID'
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Group Title')
            ->addColumn('identifier', Table::TYPE_TEXT, 100, ['nullable' => false], 'Group Identifier')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->addIndex($installer->getIdxName('box_group', ['identifier']), ['identifier'])
            ->setComment('Balance Box Group');

        $installer->getConnection()->createTable($groupTable);

        $boxTable = $installer->getConnection()
            ->newTable($installer->getTable('balance_box_single'))
            ->addColumn(
                'box_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Box ID'
            )
            ->addColumn('identifier', Table::TYPE_TEXT, 100, ['nullable' => false], 'Box Identifier')
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Box Title')
            ->addColumn('group_id', Table::TYPE_INTEGER, null, ['unsigned' => true], 'Group ID')
            ->addColumn('desktop_image', Table::TYPE_TEXT, 255, [], 'Box Desktop Image')
            ->addColumn('mobile_image', Table::TYPE_TEXT, 255, [], 'Box Mobile Image')
            ->addColumn('alt_text', Table::TYPE_TEXT, 255, [], 'Box Alt Text')
            ->addColumn('heading', Table::TYPE_TEXT, 255, [], 'Box Heading')
            ->addColumn('content', Table::TYPE_TEXT, '2M', [], 'Box Content')
            ->addColumn('link', Table::TYPE_TEXT, '64K', [], 'Box Link')
            ->addColumn('button_text', Table::TYPE_TEXT, 255, [], 'Box Button Text')
            ->addColumn('layout', Table::TYPE_TEXT, 255, [], 'Box Layout')
            ->addColumn('from_date', Table::TYPE_DATETIME, null, array(), 'Box Enabled From Date')
            ->addColumn('to_date', Table::TYPE_DATETIME, null, [], 'Box Enabled To Date')
            ->addColumn('position', Table::TYPE_INTEGER, null, array('nullable' => false, 'default'  => 0), 'Box Position')
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Box Active?')
            ->addColumn('creation_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => false], 'Update Time')
            ->addIndex($installer->getIdxName('box_single', ['identifier']), ['identifier'])
            ->addIndex($installer->getIdxName('box_single', ['group_id']), ['group_id'])
            ->addForeignKey(
                $installer->getFkName('balance_box_single', 'group_id', 'balance_box_group', 'group_id'),
                'group_id',
                $installer->getTable('balance_box_group'),
                'group_id',
                Table::ACTION_SET_NULL
            )
            ->setComment('Balance Box');

        $installer->getConnection()->createTable($boxTable);

        $storeTable = $installer->getConnection()
            ->newTable($installer->getTable('balance_box_store'))
            ->addColumn(
                'box_id', Table::TYPE_INTEGER,
                null,
                [
                    'unsigned'       => true,
                    'nullable'       => false,
                    'primary'        => true,
                ],
                'Box Id'
            )
            ->addColumn(
                'store_id', Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned'       => true,
                    'nullable'       => false,
                    'primary'        => true,
                ],
                'Store Id'
            )
            ->addIndex($installer->getIdxName('balance_box_store', ['store_id']), ['store_id'])
            ->addForeignKey(
                $installer->getFkName('balance_box_store', 'box_id', 'balance_box_single', 'box_id'),
                'box_id',
                $installer->getTable('balance_box_single'),
                'box_id', Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('balance_box_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id', Table::ACTION_CASCADE
            )
            ->setComment('Box To Store Linkage Table');

        $installer->getConnection()->createTable($storeTable);

        $installer->endSetup();
    }
}