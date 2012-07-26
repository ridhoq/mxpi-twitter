<?php
/**
 * Base test case for XCom
 */
class Xcom_TestCase_Abstract extends PHPUnit_Framework_TestCase
{
    public function assertEventCalledTimes($eventName, $times)
    {
        $events = Mage::registry('test_framework_dispatched_event');
        $this->assertArrayHasKey($eventName, $events, 'Assertion failed: event ' . $eventName . ' wasn\'t dispatched');
        $this->assertEquals($times, count($events[$eventName]['data']), 'Assertion Failed: event ' .
            $eventName . ' was dispatched ' . $times . ' times');

    }

    public function assertEventCalledAtWithData($eventName, $at, $eventData)
    {
        $events = Mage::registry('test_framework_dispatched_event');
        $this->assertArrayHasKey($eventName, $events, 'Assertion failed: event ' . $eventName . ' wasn\'t dispatched');
        $this->assertEquals($eventData, $events[$eventName]['data'][$at], 'Assertion Failed: event ' .
            $eventName . ' was dispatched at ' . $at . ' with data');
    }

}

