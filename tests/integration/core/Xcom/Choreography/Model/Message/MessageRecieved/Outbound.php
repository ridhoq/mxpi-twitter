<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rhoq
 * Date: 7/6/12
 * Time: 4:49 PM
 * To change this template use File | Settings | File Templates.
 */
class Xcom_Xfabric_Model_MessagingTest extends Xcom_Database_TestCase
{

    public function testMessageRecieved()
    {
        //parent::setUp();
        $data = Mage::getModel('xcom_choreography/message_messageReceived',null);
//        $data = array("type" =>"record",
//                      "name" => "MessageReceived",
//                      "namespace" => "com.x.core.v1",
//        );
        Mage::helper('xcom_xfabric')->send('xcom_choreography/message_messageReceived_outbound', $data->getData());
        //Mage::helper('xcom_xfabric')->send('com.x.ordermanagement.v2/ProcessSalesChannelOrder/OrderShipped', array('shipment' => ''));
        //parent::tearDown();
    }

}
