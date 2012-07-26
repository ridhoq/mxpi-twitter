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
class Xcom_Listing_Model_Resource_Channel_ProductTest extends Xcom_TestCase
{
    /** @var Xcom_Listing_Model_Resource_Channel_Product */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Listing_Model_Resource_Channel_Product();
    }

    public function testGetPublishedListingIds()
    {
        $returnData = array(
            array('listing_id' => 1, 'product_id' => 1, 'channel_id' => 1),
            array('listing_id' => 1, 'product_id' => 2, 'channel_id' => 1),
            array('listing_id' => 2, 'product_id' => 3, 'channel_id' => 1),
            array('listing_id' => 2, 'product_id' => 4, 'channel_id' => 1),
        );

        $adaptorMock = $this->_getAdapterMock($returnData);

        $objectMock = $this->getMock('Xcom_Listing_Model_Resource_Channel_Product',
            array('_getReadAdapter'));
        $objectMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($adaptorMock));

        $result = $objectMock->getPublishedListingIds();
        $etalon = array(
            1 => array('product_ids' => array(1,2), 'channel_id' => 1),
            2 => array('product_ids' => array(3,4), 'channel_id' => 1)
        );

        $this->assertEquals($etalon, $result);
    }

    public function testIsProductsInChannel()
    {
        $returnData = false;
        $adaptorMock = $this->_getAdapterMock($returnData);
        $objectMock = $this->getMock('Xcom_Listing_Model_Resource_Channel_Product',
            array('_getReadAdapter'));
        $objectMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($adaptorMock));
        $result = $objectMock->isProductsInChannel(1,2);
        $this->assertFalse($result);

        $returnData = 25;
        $adaptorMock = $this->_getAdapterMock($returnData);
        $objectMock = $this->getMock('Xcom_Listing_Model_Resource_Channel_Product',
            array('_getReadAdapter'));
        $objectMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($adaptorMock));
        $result = $objectMock->isProductsInChannel(1,2);
        $this->assertTrue($result);

    }

    protected function _getAdapterMock($returnData)
    {
        $adaptorMock = $this->getMock('Varien_Db_Adapter_Pdo_Mysql',
            array('fetchAll', 'select', 'quoteIdentifier', 'fetchOne'), array(), '', false);
        $adaptorMock->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue($returnData));

        $adaptorMock->expects($this->any())
            ->method('fetchOne')
            ->will($this->returnValue($returnData));

        $adaptorMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnValue(null));

        $selectMock = $this->getMock('Varien_Db_Select',
                    array('where', 'from', 'having'));
        $selectMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnValue($selectMock));
        $selectMock->expects($this->any())
            ->method('having')
            ->will($this->returnValue($selectMock));

        $adaptorMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));

        return $adaptorMock;
    }
}
