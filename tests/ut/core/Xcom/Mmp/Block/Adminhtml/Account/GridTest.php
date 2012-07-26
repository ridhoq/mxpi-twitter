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
class Xcom_Mmp_Block_Adminhtml_Account_GridTest extends Xcom_TestCase
{
    /** @var $_object Xcom_Mmp_Block_Adminhtml_Account_Grid */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Block_Adminhtml_Account_Grid();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testConstructorMassactionBlockName()
    {
        $result = $this->_object->getMassactionBlockName();
        $this->assertEquals('xcom_mmp/adminhtml_widget_grid_massaction', $result);
    }

    public function testPrepareMassactionDisableItem()
    {
        $object = new Xcom_Mmp_Block_Adminhtml_Account_Grid_Fixture();
        $objectMock = $this->_mockObjectCollection($object);

        $objectMock->prepareGrid();
        $disableItem = $objectMock->getMassactionBlock()->getItem('disable');

        $this->assertArrayHasKey('validate_url', $disableItem->getData());
        $this->assertArrayHasKey('url', $disableItem->getData());
    }

    protected function _mockObjectCollection($object)
    {
        $collectionMock = $this->mockCollection('xcom_mmp/account');
        $objectMock = $this->getMock(get_class($object), array('getCollection', '_prepareCollection'));
        $objectMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($collectionMock));
        return $objectMock;
    }
}

class Xcom_Mmp_Block_Adminhtml_Account_Grid_Fixture extends Xcom_Mmp_Block_Adminhtml_Account_Grid
{
    public function prepareGrid()
    {
        $this->setLayout(Mage::app()->getLayout());
        return parent::_prepareGrid();
    }
}
