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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Model_Message_Marketplace_Category_Search_SucceededTest extends Xcom_TestCase
{
    /** @var Xcom_Listing_Model_Message_Marketplace_Category_Search_Succeeded */
    protected $_object;
    protected $_instanceOf  = 'Xcom_Listing_Model_Message_Marketplace_Category_Search_Succeeded';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('marketplace/category/searchSucceeded');
        $this->_object->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testProcess()
    {
        $testSource = array(
            'marketplace'       => 'MP',
            'siteCode'          => 'SC',
            'environmentName'   => 'EN',
            'categories'        => array(
                array(
                    'id'                => '1',
                    'name'              => 'Cat_1',
                    'parentId'          => '1',
                    'leafCategory'      => '1',
                    'categoryLevel'     => '3',
                    'catalogEnabled'    => '1'
                ),
                array(
                    'id'                => '3',
                    'name'              => 'Cat_3',
                    'parentId'          => '1',
                    'leafCategory'      => '1',
                    'categoryLevel'     => '4',
                    'catalogEnabled'    => '1'
                ),
                array(
                    'id'                => '5',
                    'name'              => 'Cat_5',
                    'parentId'          => '3',
                    'leafCategory'      => '1',
                    'categoryLevel'     => '5',
                    'catalogEnabled'    => '1'
                ),
                array(
                    'id'                => '7',
                    'name'              => 'Cat_7',
                    'parentId'          => '5',
                    'leafCategory'      => '1',
                    'categoryLevel'     => '6',
                    'catalogEnabled'    => '1'
                ),
                array(
                    'id'                => '9',
                    'name'              => 'Cat_9',
                    'parentId'          => '5',
                    'leafCategory'      => '1',
                    'categoryLevel'     => '6',
                    'catalogEnabled'    => '1'
                )
            ),
        );
        $testResult = array(
            $testSource['categories'][0]['id'] => array(
                'id'                => $testSource['categories'][0]['id'],
                'name'              => 'Cat_1',
                'path'              => '1',
                'children_count'    => 1,
                'parent_id'         => $testSource['categories'][0]['parentId'],
                'leaf_category'     => $testSource['categories'][0]['leafCategory'],
                'level'             => $testSource['categories'][0]['categoryLevel'],
                'catalog_enabled'   => $testSource['categories'][0]['catalogEnabled'],
                'marketplace'       => $testSource['marketplace'],
                'site_code'         => $testSource['siteCode'],
                'environment_name'  => $testSource['environmentName'],
             ),
            $testSource['categories'][1]['id'] => array(
                'id'                => $testSource['categories'][1]['id'],
                'name'              => 'Cat_3',
                'path'              => '1/3',
                'children_count'    => 1,
                'parent_id'         => $testSource['categories'][1]['parentId'],
                'leaf_category'     => $testSource['categories'][1]['leafCategory'],
                'level'             => $testSource['categories'][1]['categoryLevel'],
                'catalog_enabled'   => $testSource['categories'][1]['catalogEnabled'],
                'marketplace'       => $testSource['marketplace'],
                'site_code'         => $testSource['siteCode'],
                'environment_name'  => $testSource['environmentName'],
             ),
            $testSource['categories'][2]['id'] => array(
                'id'                => $testSource['categories'][2]['id'],
                'name'              => 'Cat_5',
                'path'              => '1/3/5',
                'children_count'    => 2,
                'parent_id'         => $testSource['categories'][2]['parentId'],
                'leaf_category'     => $testSource['categories'][2]['leafCategory'],
                'level'             => $testSource['categories'][2]['categoryLevel'],
                'catalog_enabled'   => $testSource['categories'][2]['catalogEnabled'],
                'marketplace'       => $testSource['marketplace'],
                'site_code'         => $testSource['siteCode'],
                'environment_name'  => $testSource['environmentName'],
            ),
            $testSource['categories'][3]['id'] => array(
                'id'                => $testSource['categories'][3]['id'],
                'name'              => 'Cat_7',
                'path'              => '1/3/5/7',
                'children_count'    => 0,
                'parent_id'         => $testSource['categories'][3]['parentId'],
                'leaf_category'     => $testSource['categories'][3]['leafCategory'],
                'level'             => $testSource['categories'][3]['categoryLevel'],
                'catalog_enabled'   => $testSource['categories'][3]['catalogEnabled'],
                'marketplace'       => $testSource['marketplace'],
                'site_code'         => $testSource['siteCode'],
                'environment_name'  => $testSource['environmentName'],
            ),
            $testSource['categories'][4]['id'] => array(
                'id'                => $testSource['categories'][4]['id'],
                'name'              => 'Cat_9',
                'path'              => '1/3/5/9',
                'children_count'    => 0,
                'parent_id'         => $testSource['categories'][4]['parentId'],
                'leaf_category'     => $testSource['categories'][4]['leafCategory'],
                'level'             => $testSource['categories'][4]['categoryLevel'],
                'catalog_enabled'   => $testSource['categories'][4]['catalogEnabled'],
                'marketplace'       => $testSource['marketplace'],
                'site_code'         => $testSource['siteCode'],
                'environment_name'  => $testSource['environmentName'],
            ),
        );

        $this->_object->setBody($testSource);

        // Mock Resource
        $categoryResource = $this->mockResource('xcom_listing/category', array('import', 'clean'));
        $categoryResource->expects($this->once())
            ->method('import')
            ->with($this->equalTo($testResult))
            ->will($this->returnValue($categoryResource));
        $categoryResource->expects($this->once())
            ->method('clean')
            ->with($this->equalTo(array_keys($testResult)), $this->equalTo('MP'), $this->equalTo('SC'), $this->equalTo('EN'))
            ->will($this->returnValue($categoryResource));

        $result = $this->_object->process();
        $this->assertInstanceOf($this->_instanceOf, $result);
    }
}
