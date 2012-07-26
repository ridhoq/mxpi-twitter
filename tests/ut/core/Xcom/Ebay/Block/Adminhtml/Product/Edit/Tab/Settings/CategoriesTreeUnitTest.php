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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree.
 */
class Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTreeUnitTest extends Xcom_TestCase
{
    /**
     * Work object
     *
     * @var Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree
     */
    protected $_object;

    /**
     * Init object
     *
     * @return void
     */
    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree();
    }

    /**
     * Reset object
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    /**
     * Get fixture data
     *
     * @return array
     */
    protected function _getFixture()
    {
        return require dirname(__FILE__) . '/_fixtures/categories.php';
    }

    /**
     * Test getting recommended JSON tree
     *
     * In this test tested building rightly tree by unsorted list
     *
     * @return void
     */
    public function testGetRecommendedTreeJson()
    {
        $data = $this->_getFixture();

        $select = Mage::getResourceModel('xcom_listing/category_collection')->getSelect();
        $collection = $this->mockCollection('xcom_listing/category', new ArrayIterator($data['items']), array(
            'setOptions', 'toOptionArray', 'getSelect', 'addFilter',
            'addFieldToFilter', 'load', 'getIterator', 'getItems'
        ));
        $collection->expects($this->any())
                             ->method('getSelect')
                             ->will($this->returnValue($select));
        $collection->expects($this->any())
                             ->method('reset')
                             ->will($this->returnValue($collection));
        $collection->expects($this->any())
                             ->method('addFieldToFilter')
                             ->will($this->returnValue($collection));
        $collection->expects($this->any())
                             ->method('addFilter')
                             ->will($this->returnValue($collection));

        /** @var $stub Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree */
        $stub = $this->mockModel('Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree',
            array(
                'getSiteCode',
                'getEnvironmentName',
                '_getCategoryCollection',
                '_setDefaultCollectionFilters',
                '_getRecommendedCategories',
            )
        );
        $stub->expects($this->any())
             ->method('_getCategoryCollection')
             ->will($this->returnValue($collection));

        $stub->expects($this->any())
             ->method('getChannel')
             ->will($this->returnValue(new Varien_Object()));

        $stub->expects($this->any())
             ->method('_setDefaultCollectionFilters')
             ->will($this->returnValue($stub));

        /**
         * In first time return not empty array and collection return full tree,
         * but in second time return empty recommended list for empty result
         */
        $stub->expects($this->any())
             ->method('_getRecommendedCategories')
             ->will($this->onConsecutiveCalls(
                array(1),
                array(1),
                array(),
                array(),
                array($data['selected_category_id'] => array()),
                array($data['selected_category_id'] => array()))
             );

        $code = 'code';
        $stub->expects($this->any())
             ->method('getSiteCode')
             ->will($this->returnValue($code));

        $env = 'env';
        $stub->expects($this->any())
             ->method('getEnvironmentName')
             ->will($this->returnValue($env));

        $returnKey = array(
            'siteCode'        => $code,
            'environmentName' => $env
        );

        $collection->expects($this->any())
                   ->method('setOptions')
                   ->will($this->returnValue($returnKey));

        $stub->setData('selected_category_id', $data['selected_category_id']);

        $treeJson = $stub->getTreeJson(true);
        $decode = Zend_Json::decode($treeJson, Zend_Json::TYPE_ARRAY);

        $this->assertEquals($data['expected'], $decode);
        $this->assertEquals('[]', $stub->getTreeJson(true));

        $stub->setData('selected_category_id', null);
        $treeJsonRecommendedOne = $stub->getTreeJson(true);
        $decodeRecommendedOne = Zend_Json::decode($treeJsonRecommendedOne, Zend_Json::TYPE_ARRAY);
        $this->assertEquals($data['expected'], $decodeRecommendedOne);
    }

    public function testGetSelectedCategoryOneCategory()
    {
        $recommendedCategories = array(
            1 => 'test_category_1',
        );
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree',
            array('_getRecommendedCategories'));
        $objectMock->expects($this->any())
            ->method('_getRecommendedCategories')
            ->will($this->returnValue($recommendedCategories));

        $result = $objectMock->getSelectedCategory();
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result);
    }

    public function testGetSelectedCategoryMultipleCategories()
    {
        $recommendedCategories = array(
            1 => 'test_category_1',
            2 => 'test_category_2',
        );
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree',
            array('_getRecommendedCategories'));
        $objectMock->expects($this->any())
            ->method('_getRecommendedCategories')
            ->will($this->returnValue($recommendedCategories));

        $result = $objectMock->getSelectedCategory();
        $this->assertEmpty($result);
    }

    public function testGetSelectedCategoryDataSet()
    {
        $recommendedCategories = array(
            1 => 'test_category_1',
        );
        $objectMock = $this->getMock('Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree',
            array('_getRecommendedCategories'));
        $objectMock->expects($this->any())
            ->method('_getRecommendedCategories')
            ->will($this->returnValue($recommendedCategories));

        $objectMock->setData('selected_category_id', 1);
        $result = $objectMock->getSelectedCategory();
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result);
    }
}
