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
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Listener
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Centralized entry point for handling PHPUnit built-in and custom events
 */
class Mage_Listener_EventListener implements PHPUnit_Framework_TestListener
{
    /**
     * Registered event observers
     *
     * @var array
     */
    protected static $_observers = array();

    /**
     * @var PHPUnit_Framework_TestCase
     */
    protected $_currentTest;

    /**
     * @var PHPUnit_Framework_TestSuite
     */
    protected $_currentSuite;

    /**
     * Register observer class
     *
     * @static
     * @param Mage_Listener_EmptyObserver $observerInstance
     */
    public static function attach($observerInstance)
    {
        self::$_observers[] = $observerInstance;
    }

    /**
     * Register observer class
     *
     * @static
     * @param mixed $observerInstance
     *
     * @return bool
     */
    public static function detach($observerInstance)
    {
        foreach (self::$_observers as $oKey => $oVal) {
            if ($oVal === $observerInstance) {
                unset(self::$_observers[$oKey]);
                return true;
            }
        }
        return false;
    }

    /**
     * Get registered observers
     *
     * @static
     * @return array
     */
    public static function getObservers()
    {
        return self::$_observers;
    }

    /**
     * Load all available Listeners from folder
     *
     * @static
     * @param string $path Path to Folder with Listener classes
     */
    public static function autoAttach($path)
    {
        $files = glob($path);
        if (is_array($files)) {
            foreach ($files as $file) {
                $className = 'Mage_Listener_Observers_' . basename($file, '.php');
                if (class_exists($className)) {
                    static::attach(new $className());
                }
            }
        }
    }

    /**
     * Retrieve currently running test
     *
     * @return PHPUnit_Framework_TestSuite|null
     */
    public function getCurrentSuite()
    {
        return $this->_currentSuite;
    }

    /**
     * Retrieve currently running test
     *
     * @return PHPUnit_Framework_TestCase|null
     */
    public function getCurrentTest()
    {
        return $this->_currentTest;
    }

    /**
     * Notify registered observers that are interested in event
     *
     * @param string $eventName
     * @param bool $reverseOrder
     */
    protected function _notifyObservers($eventName, $reverseOrder = false)
    {
        $observers = ($reverseOrder ? array_reverse(self::$_observers) : self::$_observers);
        foreach ($observers as $observerInstance) {
            $callback = array($observerInstance, $eventName);
            if (is_callable($callback)) {
                call_user_func($callback, $this);
            }
        }
    }

    /**
     * An error occurred.
     * Method is required by implemented interface, but is not needed by the class.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->_currentTest = $test;
        $this->_notifyObservers('testFailed');
    }

    /**
     * A failure occurred.
     * Method is required by implemented interface, but is not needed by the class.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->_currentTest = $test;
        $this->_notifyObservers('testFailed');
    }

    /**
     * Incomplete test.
     * Method is required by implemented interface, but is not needed by the class.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->_currentTest = $test;
        $this->_notifyObservers('testSkipped');
    }
    /**
     * Skipped test.
     * Method is required by implemented interface, but is not needed by the class.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->_currentTest = $test;
        $this->_notifyObservers('testSkipped');
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        /* PHPUnit runs tests with data provider in own test suite for each test, so just skip such test suites */
        if ($suite instanceof PHPUnit_Framework_TestSuite_DataProvider) {
            return;
        }
        if (!$this->_currentSuite) {
            $this->_currentSuite = $suite;
            $this->_notifyObservers('startTestSuite');
        }
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($suite instanceof PHPUnit_Framework_TestSuite_DataProvider) {
            return;
        }
        if ($this->_currentSuite == $suite) {
            $this->_notifyObservers('endTestSuite');
        }
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!($test instanceof PHPUnit_Framework_TestCase) || ($test instanceof PHPUnit_Framework_Warning)) {
            return;
        }
        $this->_currentTest = $test;
        $this->_notifyObservers('startTest');
    }

    /**
     * A test ended.
     * Method signature is implied by implemented interface, not all parameters are needed.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!($test instanceof PHPUnit_Framework_TestCase) || ($test instanceof PHPUnit_Framework_Warning)) {
            return;
        }
        $this->_notifyObservers('endTest', true);
    }
}