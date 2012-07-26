<?php
/**
 * Created by JetBrains PhpStorm.
 * User: otoolec
 * Date: 6/14/12
 * Time: 3:12 PM
 * To change this template use File | Settings | File Templates.
 */
class Xcom_Chronicle_Model_Message_Customer_Get_InboundTest extends Xcom_TestCase
{

    function testProcessFailure()
    {
        $mockData = array(
            'customer'  => null,
            'errors'    => array('error'),
        );

        $inbound = new Class_Under_Test();
        $inbound->setMockData($mockData);

        $inbound->process();

        $responses = $inbound->getResponses();

        $this->assertEquals(1, count($responses));
        $this->assertEquals('failure', $responses[0]['type']);
    }


    function testProcessSuccess()
    {
        $mockData = array(
            'customer'  => 'the_customer',
        );

        $inbound = new Class_Under_Test();
        $inbound->setMockData($mockData);

        $inbound->process();

        $responses = $inbound->getResponses();

        $this->assertEquals(1, count($responses));
        $this->assertEquals('success', $responses[0]['type']);
    }
}

class Class_Under_Test extends Xcom_Chronicle_Model_Message_Customer_Get_Inbound
{

    var $mockData;
    var $responses;

    public function setMockData($mockData)
    {
        $this->mockData = $mockData;
    }

    /**
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    protected function _processLookup(&$data)
    {
        return $this->mockData;
    }

    protected function _sendFailure($response)
    {
        $this->responses[] = array(
            'response' => $response,
            'type' => 'failure',
        );
    }

    protected function _sendSuccess($response)
    {
        $this->responses[] = array(
            'response' => $response,
            'type' => 'success',
        );
    }
}