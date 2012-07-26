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

/**
 * @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
 */
$installer      = $this;

$attributeSets = array('Shoes-Her', 'Shoes-Him', 'Sport Equipment', 'Shirts-Him', 'Shirts-Her');
$categoryProductentityTypeId = 4;
$entityType = Mage::getModel('eav/entity_type')->load($categoryProductentityTypeId);

foreach($attributeSets as $attributeSet) {
    $model = Mage::getModel('eav/entity_attribute_set')->load($attributeSet, 'attribute_set_name');
    if (!$model->getId()) {
        $model->setEntityTypeId($categoryProductentityTypeId);
        $model->setAttributeSetName($attributeSet);
        $model->validate();
        $model->save();
        $model->initFromSkeleton($entityType->getDefaultAttributeSetId());
        $model->save();
    }
}

$attributeCode  = 'xcom_condition';
$defaultStoreId = 0;
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode, array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Condition',
    'input'             => 'select',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'option'            => array(
        'value' => array(
            'new'  => array($defaultStoreId => 'New'),
            'used' => array($defaultStoreId => 'Used')),
        'order' => array(
            'new'  => 10,
            'used' => 20,
        ),
    )
));
$entityTypeId   = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attributeId    = $installer->getAttributeId($entityTypeId, $attributeCode);
$attributeSets  = $installer->getAllAttributeSetIds($entityTypeId);

//add new attribute to every attribute set and default attribute group
foreach ($attributeSets as $attributeSetId) {
    $attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        $attributeId
    );
}
//get attribute option id for 'new' value
$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attributeId)
                ->load();
$defaultOptionId   = $collection->getFirstItem()->getOptionId();

//save this attribute with default value to every product
$collection = Mage::getModel('catalog/product')->getCollection();
foreach ($collection as $product) {
    $product->addAttributeUpdate($attributeCode, $defaultOptionId, 0);
}

// Initializer

Mage::getModel('xcom_mapping/observer')->createTaxonomyMessages();
