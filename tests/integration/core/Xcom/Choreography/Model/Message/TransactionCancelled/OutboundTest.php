<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rhoq
 * Date: 7/13/12
 * Time: 4:43 PM
 * To change this template use File | Settings | File Templates.
 */
class Xcom_Choreography_Model_Message_TransactionCancelled_OutboundTest extends Xcom_Messaging_TestCase
{
    public function testTransactionCancelled()
    {
        $data = Mage::getModel('xcom_choreography/message_messageReceived',null);
        Mage::helper('xcom_xfabric')->send('xcom_choreography/message_messageReceived_outbound', $data->getData());
        sleep(5);
        $expectedMsg = array(0 => array("topic" => "/com.x.core.v1/MessageReceived"));
        $generatedMsg = $this->_get2dXMessages();
        $this->verifyXMessage($expectedMsg, $generatedMsg);
    }
}
