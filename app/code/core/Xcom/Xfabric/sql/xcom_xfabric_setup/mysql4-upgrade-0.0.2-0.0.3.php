<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Xcom
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->startSetup();

/**
 * Create table 'xcom_xfabric/debug'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_xfabric/debug'))
    ->addColumn('debug_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Debug Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_CHAR, 255, array(
        ), 'Debug Name')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Started At')
    ->addColumn('completed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Completed At')
    ->addColumn('started_microtime', Varien_Db_Ddl_Table::TYPE_FLOAT, 12, array(
            'nullable'  => true,
            'length'    => 12,
            'PRECISION' => 12,
            'SCALE'     => 4,
        ), 'Started Microtime')
    ->addColumn('completed_microtime', Varien_Db_Ddl_Table::TYPE_FLOAT, 12, array(
            'nullable'  => true,
            'PRECISION' => 12,
            'SCALE'     => 4,
        ), 'Completed Microtime')
    ->addColumn('has_error', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Has Options')
    ->setOption('collate', null)
    ->setOption('comment', 'XFabric Debug Table');
$this->getConnection()->createTable($table);
$this->getConnection()->modifyColumn($table->getName(), 'debug_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_xfabric/debug_node'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_xfabric/debug_node'))
    ->addColumn('node_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Node Id')
    ->addColumn('debug_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Debug Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Id')
    ->addColumn('topic', Varien_Db_Ddl_Table::TYPE_CHAR, 255, array(
        ), 'Topic Name')
    ->addColumn('headers', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '32k', array(
        ), 'Message Headers')
    ->addColumn('body', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
        ), 'Message Data')
    ->addColumn('memory_usage_before', Varien_Db_Ddl_Table::TYPE_CHAR, 255, array(
        ), 'Memory Usage Before')
    ->addColumn('memory_usage_after', Varien_Db_Ddl_Table::TYPE_CHAR, 255, array(
        ), 'Memory Usage After')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Started At')
    ->addColumn('completed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Completed At')
    ->addColumn('started_microtime', Varien_Db_Ddl_Table::TYPE_FLOAT, 12, array(
            'nullable'  => true,
            'length'    => 12,
            'PRECISION' => 12,
            'SCALE'     => 4,
        ), 'Started Microtime')
    ->addColumn('completed_microtime', Varien_Db_Ddl_Table::TYPE_FLOAT, 12, array(
            'nullable'  => true,
            'PRECISION' => 12,
            'SCALE'     => 4,
        ), 'Completed Microtime')
    ->addColumn('has_error', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Has Options')
    ->setOption('collate', null)
    ->setOption('comment', 'XFabric Debug Node Table');
$this->getConnection()->createTable($table);
$this->getConnection()->modifyColumn($table->getName(), 'node_id', 'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
