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
 * @package     Xcom_Initializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */

$this->startSetup();

/**
 * Create table xcom_initializer_job
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_initializer/job'))
    ->addColumn('job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Job Id')
    ->addColumn('correlation_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Correlation Id')
    ->addColumn('topic', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Topic')
    ->addColumn('message_params', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
), 'Message Params')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Updated At')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
), 'Status')
    ->addIndex('UNQ_XCOM_INITIALIZER_JOB_CORRELATION_ID',
    array('job_id'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Initializer Job');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'job_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_initializer_job_params
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_initializer/job_params'))
    ->addColumn('param_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Param Id')
    ->addColumn('job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Job Id')
    ->addColumn('message_params', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
), 'Message Params')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
), 'Status')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Updated At')
    ->addIndex('IDX_XCOM_INSTALLER_JOB_JOBPARAMS_ID',
    array('job_id'))
    ->addForeignKey('FK_XCOM_INSTALLER_JOB_JOBPARAMS_ID',
    'job_id', $this->getTable('xcom_initializer/job'), 'job_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Initializer Job Params');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'param_id', 'int(10) unsigned NOT NULL auto_increment');
$this->getConnection()->modifyColumn($table->getName(), 'updated_at',
    'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

$this->endSetup();
