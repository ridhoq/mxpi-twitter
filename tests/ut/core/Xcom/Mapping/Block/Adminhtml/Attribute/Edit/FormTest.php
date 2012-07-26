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
class Xcom_Mapping_Block_Adminhtml_Attribute_Edit_FormTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Block_Adminhtml_Attribute_Edit_Form */
    protected $_object;

    public function setUp()
    {
        parent::setUp();

        $this->_object = new Xcom_Mapping_Block_Adminhtml_Attribute_Edit_Form();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }


    /**
     * Test for method getAttributesOptionArray
     */
    public function testGetAttributesOptionArray()
    {
        $options = array(
            new Varien_Object(array(
                'id'                => 2,
                'attribute_id'      => 2,
                'frontend_label'    => 'Label 2'
            )),
            new Varien_Object(array(
                'id'                => 1,
                'attribute_id'      => 1,
                'frontend_label'    => 'Label 1'
            ))
        );
        $mockCollection = $this->mockCollection('catalog/product_attribute', $options,
            array('setAttributeSetFilter', 'addVisibleFilter', 'addStoreLabel', 'addFilter', 'isLoaded',
                'unshiftOrder'));
        $mockCollection->expects($this->once())
            ->method('addVisibleFilter')
            ->will($this->returnValue($mockCollection));
        $mockCollection->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($this->equalTo(11))
            ->will($this->returnValue($mockCollection));
        $mockCollection->expects($this->once())
            ->method('addStoreLabel')
            ->with($this->equalTo(Mage_Core_Model_App::ADMIN_STORE_ID))
            ->will($this->returnValue($mockCollection));
        $mockCollection->expects($this->once())
            ->method('addFilter')
            ->with($this->equalTo('is_user_defined'), $this->equalTo(1))
            ->will($this->returnValue($mockCollection));
        $mockCollection->expects($this->any())
            ->method('isLoaded')
            ->will($this->returnValue(true));
        $mockCollection->expects($this->once())
            ->method('unshiftOrder')
            ->will($this->returnValue($options));

        $mockRelation   = $this->mockModel('xcom_mapping/relation', array('addFilterOnlyMappedAttributes', 'load'));
        $mockRelation->expects($this->any())
            ->method('addFilterOnlyMappedAttributes')
            ->will($this->returnValue($mockCollection));

        $actualOptions      = $this->_object->getAttributesOptionArray(11);
        $expectedOptions = array(
            array('value' => 2, 'label' => 'Label 2'),
            array('value' => 1, 'label' => 'Label 1')
        );
        $this->assertEquals($expectedOptions, $actualOptions);
    }
}
