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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->startSetup();

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */

/**
 * Create table xcom_mapping_product_class
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/product_class'))
    ->addColumn('mapping_product_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Mapping Product Class Id')
    ->addColumn('product_class_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Product Class Id')
    ->addColumn('parent_product_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Parent Product Class Id')
    ->addIndex('UNQ_TARGET_PRODUCT_CLASS_ID',
        array('product_class_id'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Product Class');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'mapping_product_class_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_product_class_locale
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/product_class_locale'))
    ->addColumn('locale_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Locale Id')
    ->addColumn('mapping_product_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Product Class Id')
    ->addColumn('locale_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8, array(
    'nullable'  => false,
), 'Locale Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Name')
    ->addIndex('UNQ_PRODUCT_CLASS_ID_CHANNEL_CODE',
        array('mapping_product_class_id', 'locale_code'), array('unique' => true))
    ->addIndex('IDX_PRODUCT_CLASS_ID',
        array('mapping_product_class_id'))
    ->addForeignKey('FK_L_XCOM_MAPPING_PRODUCT_CLASS_MAPPING_PRODUCT_CLASS_ID',
        'mapping_product_class_id', $this->getTable('xcom_mapping/product_class'), 'mapping_product_class_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Product Class Locale');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'locale_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_product_class_type
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/product_class_type'))
    ->addColumn('relation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Relation Id')
    ->addColumn('mapping_product_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Product Class Id')
    ->addColumn('mapping_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Product Type Id')
    ->addIndex('UNQ_PRODUCT_CLASS_ID_TYPE_ID',
        array('mapping_product_class_id', 'mapping_product_type_id'), array('unique' => true))
    ->addIndex('FK_XCOM_MAPPING_PRODUCT_TYPE_ID',
        array('mapping_product_type_id'))
    ->addForeignKey('FK_XCOM_MAPPING_PRODUCT_CLASS_ID',
        'mapping_product_class_id', $this->getTable('xcom_mapping/product_class'), 'mapping_product_class_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_XCOM_MAPPING_PRODUCT_TYPE_ID',
        'mapping_product_type_id', $this->getTable('xcom_mapping/product_type'), 'mapping_product_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Product Class Type');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'relation_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_product_type
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/product_type'))
    ->addColumn('mapping_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Mapping Product Type Id')
    ->addColumn('version', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8, array(
    'nullable'  => false,
), 'Version')
    ->addColumn('product_type_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Product Type Id')
    ->addIndex('UNQ_TARGET_PRODUCT_TYPE_ID',
        array('product_type_id'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Product Type');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'mapping_product_type_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute'))
    ->addColumn('mapping_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Mapping Attribute Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Attribute Id')
    ->addColumn('mapping_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Product Type Id')
    ->addColumn('is_multiselect', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Multiselect')
    ->addColumn('attribute_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 16, array(
    'nullable'  => false,
), 'Attribute Type')
    ->addColumn('is_restricted', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Restricted')
    ->addIndex('UNQ_PRODUCT_TYPE_ID_ATTRIBUTE_ID',
        array('mapping_product_type_id', 'attribute_id'), array('unique' => true))
    ->addForeignKey('FK_XCOM_MAPPING_PRODUCT_TYPE_PRODUCT_TYPE_ID',
        'mapping_product_type_id', $this->getTable('xcom_mapping/product_type'), 'mapping_product_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'mapping_attribute_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute_value
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute_value'))
    ->addColumn('mapping_value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Mapping Value Id')
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Value Id')
    ->addColumn('mapping_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Attribute Id')
    ->addColumn('channel_codes', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Channel Codes')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Default')
    ->addIndex('UNQ_MAPPING_ATTRIBUTE_ID_VALUE_ID',
        array('mapping_attribute_id', 'value_id'), array('unique' => true))
    ->addForeignKey('FK_XCOM_MAPPING_ATTRIBUTE_ATTRIBUTE_ID',
        'mapping_attribute_id', $this->getTable('xcom_mapping/attribute'), 'mapping_attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute Value');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'mapping_value_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_product_type_locale
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/product_type_locale'))
    ->addColumn('locale_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Locale Id')
    ->addColumn('mapping_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Product Type Id')
    ->addColumn('locale_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Locale Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
), 'Description')
    ->addIndex('UNQ_PRODUCT_TYPE_ID_CHANNEL_CODE',
        array('mapping_product_type_id', 'locale_code'), array('unique' => true))
    ->addIndex('IDX_PRODUCT_TYPE_ID',
        array('mapping_product_type_id'))
    ->addForeignKey('FK_L_XCOM_MAPPING_PRODUCT_TYPE_MAPPING_PRODUCT_TYPE_ID',
        'mapping_product_type_id', $this->getTable('xcom_mapping/product_type'), 'mapping_product_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Product Type Locale');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'locale_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute_locale
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute_locale'))
    ->addColumn('locale_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Locale Id')
    ->addColumn('mapping_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Attribute Id')
    ->addColumn('locale_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Locale Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
), 'Description')
    ->addIndex('UNQ_MAPPING_ATTRIBUTE_ID_CHANNEL_CODE',
        array('mapping_attribute_id', 'locale_code'), array('unique' => true))
    ->addIndex('IDX_MAPPING_ATTRIBUTE_ID',
        array('mapping_attribute_id'))
    ->addForeignKey('FK_L_XCOM_MAPPING_ATTRIBUTE_MAPPING_ATTRIBUTE_ID',
        'mapping_attribute_id', $this->getTable('xcom_mapping/attribute'), 'mapping_attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute Locale');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'locale_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute_value_locale
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute_value_locale'))
    ->addColumn('locale_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Locale Id')
    ->addColumn('mapping_value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Value Id')
    ->addColumn('locale_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Locale Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Name')
    ->addIndex('UNQ_MAPPING_VALUE_ID_CHANNEL_CODE',
        array('mapping_value_id', 'locale_code'), array('unique' => true))
    ->addIndex('IDX_MAPPING_VALUE_ID',
        array('mapping_value_id'))
    ->addForeignKey('FK_L_XCOM_MAPPING_ATTRIBUTE_VALUE_MAPPING_ATTRIBUTE_VALUE_ID',
        'mapping_value_id', $this->getTable('xcom_mapping/attribute_value'), 'mapping_value_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute Value Locale');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'locale_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_product_type_relation
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/product_type_relation'))
    ->addColumn('relation_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Relation Product Type Id')
    ->addColumn('mapping_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
), 'Mapping Product Type Id')
    ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Attribute Set Id')
    ->addIndex('UNQ_PRODUCT_TYPE_RELATION_ATTRIBUTE_SET_ID',
        array('attribute_set_id'), array('unique' => true))
    ->addIndex('IDX_PRODUCT_TYPE_ID',
        array('mapping_product_type_id'))
    ->addForeignKey('FK_R_EAV_ATTRIBUTE_SET_ATTRIBUTE_SET_ID',
        'attribute_set_id', $this->getTable('eav/attribute_set'), 'attribute_set_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_R_XCOM_MAPPING_PRODUCT_TYPE_PRODUCT_TYPE_ID',
        'mapping_product_type_id', $this->getTable('xcom_mapping/product_type'), 'mapping_product_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Product Type Relation');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'relation_product_type_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute_relation
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute_relation'))
    ->addColumn('relation_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Relation Attribute Id')
    ->addColumn('relation_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Relation Product Type Id')
    ->addColumn('mapping_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
), 'Mapping Attribute Id')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Attribute Id')
    ->addIndex('UNQ_ATTRIBUTE_RELATION_RELATION_ATTRIBUTE_ID',
        array('relation_product_type_id', 'attribute_id'), array('unique' => true))
    ->addIndex('IDX_RELATION_PRODUCT_TYPE_ID',
        array('relation_product_type_id'))
    ->addIndex('IDX_MAPPING_ATTRIBUTE_ID',
        array('mapping_attribute_id'))
    ->addIndex('IDX_ATTRIBUTE_ID',
        array('attribute_id'))
    ->addForeignKey('FK_R_EAV_ATTRIBUTE_ATTRIBUTE_ID',
        'attribute_id', $this->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_R_XCOM_MAPPING_ATTRIBUTE_MAPPING_ATTRIBUTE_ID',
        'mapping_attribute_id', $this->getTable('xcom_mapping/attribute'), 'mapping_attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_R_XCOM_MAPPING_PRODUCT_TYPE_RELATION_RELATION_PRODUCT_TYPE_ID',
        'relation_product_type_id', $this->getTable('xcom_mapping/product_type_relation'), 'relation_product_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute Relation');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'relation_attribute_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute_value_relation
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute_value_relation'))
    ->addColumn('relation_value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Relation Value Id')
    ->addColumn('relation_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Relation Attribute Id')
    ->addColumn('mapping_value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
), 'Mapping Value Id')
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
), 'Value Id')
    ->addColumn('hash_value', Varien_Db_Ddl_Table::TYPE_VARCHAR, 40, array(
), 'Hash Value')
    ->addIndex('UNQ_ATTRIBUTE_RELATION_RELATION_VALUE_ID',
        array('relation_attribute_id', 'value_id'), array('unique' => true))
    ->addIndex('UNQ_ATTRIBUTE_RELATION_RELATION_HASH_VALUE',
        array('relation_attribute_id', 'hash_value'), array('unique' => true))
    ->addIndex('IDX_RELATION_ATTRIBUTE_ID',
        array('relation_attribute_id'))
    ->addIndex('IDX_MAPPING_VALUE_ID',
        array('mapping_value_id'))
    ->addIndex('IDX_VALUE_ID',
        array('value_id'))
    ->addForeignKey('FK_R_XCOM_MAPPING_ATTRIBUTE_RELATION_RELATION_ATTRIBUTE_ID',
        'relation_attribute_id', $this->getTable('xcom_mapping/attribute_relation'), 'relation_attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_R_XCOM_MAPPING_ATTRIBUTE_VALUE_MAPPING_VALUE_ID',
        'mapping_value_id', $this->getTable('xcom_mapping/attribute_value'), 'mapping_value_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute Value Relation');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'relation_value_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table xcom_mapping_attribute_channel
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mapping/attribute_channel'))
    ->addColumn('channel_attribue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Channel Attribue Id')
    ->addColumn('mapping_attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Attribute Id')
    ->addColumn('channel_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 8, array(
    'nullable'  => false,
), 'Channel Code')
    ->addColumn('is_required', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Required')
    ->addColumn('is_variation', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Variation')
    ->addIndex('UNQ_MAPPING_ATTRIBUTE_ID_CHANNEL_CODE',
        array('mapping_attribute_id', 'channel_code'), array('unique' => true))
    ->addIndex('IDX_MAPPING_ATTRIBUTE_ID',
        array('mapping_attribute_id'))
    ->addForeignKey('FK_C_XCOM_MAPPING_ATTRIBUTE_MAPPING_ATTRIBUTE_ID',
        'mapping_attribute_id', $this->getTable('xcom_mapping/attribute'), 'mapping_attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Mapping Attribute Channel');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(),
    'channel_attribue_id', 'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
