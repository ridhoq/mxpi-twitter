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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mmp_Model_Message_Marketplace_CategoryForProductType_Search_SucceededTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Model_Message_Marketplace_CategoryForProductType_Search_Succeeded */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mmp_Model_Message_Marketplace_CategoryForProductType_Search_Succeeded';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/categoryForProductType/searchSucceeded');
        $this->_object->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testProcess()
    {
        $testSource = array(
            'marketplace'       => 'MP',
            'siteCode'          => 'SC',
            'environmentName'   => 'EN',
            'xProductTypeId'    => 'XPTI',
            'categories'        => array(
                array('id' => '1'),
                array('id' => '3'),
                array('id' => '5')
            ),
        );
        $testResult = array(
            $testSource['categories'][0]['id'],
            $testSource['categories'][1]['id'],
            $testSource['categories'][2]['id'],
        );

        $this->_object->setBody($testSource);

        // Mock type
        $productType = $this->mockModel('xcom_mapping/product_type', array('load', 'getId'));
        $productType->expects($this->once())
            ->method('load')
            ->with($this->equalTo('XPTI'), $this->equalTo('product_type_id'))
            ->will($this->returnValue($productType));
        $productType->expects($this->any())
            ->method('getId')
            ->with()
            ->will($this->returnValue('some_map_type_id'));

        // Mock Resource
        $categoryResource = $this->mockResource('xcom_listing/category', array('importRelations'));
        $categoryResource->expects($this->once())
            ->method('importRelations')
            ->with($this->equalTo('some_map_type_id'), $this->equalTo($testResult), 'MP', 'SC', 'EN')
            ->will($this->returnValue($categoryResource));

        $this->_object->process();
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testProcessException()
    {
        $this->_object->process();
    }
}
