<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jack
 * Date: 2/16/12
 * Time: 7:19 PM
 * To change this template use File | Settings | File Templates.
 */
/**
 * Implementation of the
 */
class Mage_Listener_Observers_EmptyObserver
{
    /**
     * @var Mage_Listener_EventListener
     */
    protected $_listener;

    public function startTestSuite(Mage_Listener_EventListener $listener)
    {
        $listener->getCurrentSuite();
    }

    public function endTestSuite(Mage_Listener_EventListener $listener)
    {
        $listener->getCurrentSuite();
    }

    public function testFailed(Mage_Listener_EventListener $listener)
    {
        $listener->getCurrentTest();
    }
 }