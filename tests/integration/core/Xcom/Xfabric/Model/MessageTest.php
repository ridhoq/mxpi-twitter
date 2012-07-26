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
class Xcom_Xfabric_Model_MessageTest extends Xcom_Database_TestCase
{
    protected $_object = null;

    protected $_options = array();

    public function setUp()
    {
        parent::setUp();

        $headers = array(
            'HEADER1' => 'test',
            'AUTHORIZATION' => 'test_2lkj4234'
        );
        $topic = 'com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderCancelled';
        $body = array('test'=>'test');
        $messageData = array('test1'=>'test1');
        $this->_options = array(
            'db_adapter' => Mage::getModel('xcom_xfabric/message_response'),
            'headers' => $headers,
            'topic' => $topic,
            'body' => $body,
            'message_data' => $messageData,
            'direction' => Xcom_Xfabric_Model_Message::DIRECTION_OUTBOUND,
            'status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_INVALID
        );
    }

    public function testSave()
    {
        $this->_object = Mage::getModel('xcom_xfabric/message', $this->_options);
        $this->_object->save();
        $columns = array('headers', 'topic', 'body', 'direction', 'status');
        $lastInsertedRow = $this->_getRow(
            $this->_resource->getTableName('xcom_xfabric/message'), $columns,
            'message_id=' . $this->_object->getId());
        $expected = array(
            'headers' => serialize($this->_options['headers']),
            'topic' => $this->_options['topic'],
            'body' => serialize($this->_options['message_data']),
            'direction' => (string)$this->_options['direction'],
            'status' => (string)$this->_options['status']
        );
        $this->assertEquals($expected, $lastInsertedRow);
    }

    public function testSetValidated()
    {
        $this->_object = Mage::getModel('xcom_xfabric/message', $this->_options);
        $this->_object->save();
        $this->_object->setValidated(true);
        $databaseRow = $this->_getRow(
            $this->_resource->getTableName('xcom_xfabric/message'),
            array('status'),
            'message_id=' . $this->_object->getId());
        $this->assertEquals(array('status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_VALID), $databaseRow);

        $this->_object->setValidated(false);
        $databaseRow = $this->_getRow(
            $this->_resource->getTableName('xcom_xfabric/message'),
            array('status'),
            'message_id=' . $this->_object->getId());
        $this->assertEquals(array('status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_INVALID), $databaseRow);
    }

    public function testSetReceived()
    {
        $this->_object = Mage::getModel('xcom_xfabric/message', $this->_options);
        $this->_object->save();
        $this->_object->setReceived();
        $databaseRow = $this->_getRow(
            $this->_resource->getTableName('xcom_xfabric/message'),
            array('status'),
            'message_id=' . $this->_object->getId());
        $this->assertEquals(array('status' => Xcom_Xfabric_Model_Message::MESSAGE_STATUS_RECEIVED), $databaseRow);
    }


}
