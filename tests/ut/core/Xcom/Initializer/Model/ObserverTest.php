<?php
class Xcom_Initializer_Model_ObserverTest extends Xcom_TestCase
{
    /* @var Xcom_Initializer_Model_Observer */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Initializer_Model_Observer();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testGeneral()
    {
        $this->assertTrue(true);
    }

    /**
     * Test Observer::updateJobStatus
     *
     * @dataProvider dataProviderForUpdateJobStatus
     * @param $value
     * @param $expect
     */
    public function testUpdateJobStatus($value, $expect)
    {
        $eventObserver = new Varien_Event_Observer(array(
            'event' => new Varien_Object(array(
                'message' => new Varien_Object(array(
                    'correlation_id' => $value))
            ))
        ));

        if ($expect) {
            $resourceMock = $this->mockResource('xcom_initializer/job', array('updateStatusByCorrelationId'));
            $this->_object->updateJobStatus($eventObserver);
            $resourceMock->expects($this->any())
                         ->method('updateStatusByCorrelationId');
        } else {
            $result = $this->_object->updateJobStatus($eventObserver);
            $this->assertInternalType('null', $result);
        }

    }

    public function dataProviderForUpdateJobStatus()
    {
        return array(
            array(123, true),
            array(null, false)
        );
    }

    /**
     * Test saveJobParams
     *
     * @dataProvider dataProviderTopic
     * @param $topic
     * @param $correlationId
     * @param $isProcessed
     * @param $expected
     */
    public function testsaveJobParams($topic, $correlationId, $isProcessed, $expected)
    {
        $messageResponce = $this->getMock('Xcom_Xfabric_Model_Message_Response', array(
        'getTopic', 'getCorrelationId', 'getJobIdByCorrelationId', 'getIsProcessed'
        ));
        $messageResponce->expects($this->any())
                        ->method('getTopic')
                        ->will($this->returnValue($topic));
        $messageResponce->expects($this->any())
                        ->method('getCorrelationId')
                        ->will($this->returnValue($correlationId));
        $messageResponce->dependedMessageData = array(1);
        $messageResponce->expects($this->any())
            ->method('getIsProcessed')
            ->will($this->returnValue($isProcessed));

        $jobResourceMock = $this->mockResource('xcom_initializer/job', array(
            'addSavedJob', 'updateStatusByCorrelationId', 'getJobIdByCorrelationId'
        ));
        $jobResourceMock->expects($this->any())
                        ->method('addSavedJob')
                        ->will($this->returnValue(1));

        $jobParamsResourceMock = $this->mockResource('xcom_initializer/job_params', array(
            'addSavedJobParam'
        ));
        $jobParamsResourceMock->expects($this->any())
                        ->method('addSavedJobParam');

        if($correlationId) {
            $jobResourceMock->expects($this->any())
                            ->method('updateStatusByCorrelationId')
                            ->will($this->returnValue($jobResourceMock));
            $jobResourceMock->expects($this->any())
                            ->method('getJobIdByCorrelationId')
                            ->will($this->returnValue(1));
        }

        $eventObject = new Varien_Event_Observer(array(
            'data_object' => $messageResponce
        ));

        $result = $this->_object->saveJobParams($eventObject);

        if(false === $expected) {
            $this->assertInternalType('null', $result);
        }
    }

    public function dataProviderTopic()
    {
        return array(
            array('marketplace/environment/searchSucceeded', null, 1, true),
            array('wrong/test/searchSucceeded', null, 1, false),
            array('marketplace/environment/searchSucceeded', 123, 1, true),
            array('marketplace/environment/searchFailed', null, 1, false),
            array('marketplace/environment/searchFailed', 123, 1, true),
            array('marketplace/environment/searchFailed', 123, 1, true),
            array('productTaxonomy/productType/get', 123, 0, false),
        );
    }

    /**
     * @dataProvider dataProviderControllerPreDispatch
     * @param $isAllowed
     */
    public function testControllerPreDispatch($isAllowed)
    {
        $initializerMock = $this->mockModel('xcom_initializer/initializer', array('isAllowed'));
        $initializerMock->expects($this->any())->method('isAllowed')->will($this->returnValue($isAllowed));

        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(
            'initForward',
            'setControllerName',
            'setModuleName',
            'setActionName',
            'setDispatched',
            'isDispatched',
        ));

        $request->expects($isAllowed ? $this->never() : $this->once())
            ->method('initForward')->will($this->returnSelf());
        $request->expects($isAllowed ? $this->never() : $this->once())
            ->method('setControllerName')->will($this->returnSelf());
        $request->expects($isAllowed ? $this->never() : $this->once())
            ->method('setModuleName')->will($this->returnSelf());
        $request->expects($isAllowed ? $this->never() : $this->once())
            ->method('setActionName')->will($this->returnSelf());
        $request->expects($isAllowed ? $this->never() : $this->once())
            ->method('setDispatched')->will($this->returnSelf());
        $request->expects($this->any())->method('isDispatched')->will($this->returnValue(true));

        $controller = $this->getMock('Mage_Core_Controller_Varien_Action', array('getRequest'));
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        $eventObject = new Varien_Event_Observer(array('controller_action' => $controller));
        $this->_object->controllerPreDispatch($eventObject);
    }

    public function dataProviderControllerPreDispatch()
    {
        return array(true, false);
    }
}
