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
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the Selenium RC client/server protocol.
 * Extension: logging of all client/server protocol transactions to the 'selenium-rc-DATE.log' file.
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Driver extends PHPUnit_Extensions_SeleniumTestCase_Driver
{
    /**
     * If the flag is set to True, browser connection is not restarted after each test
     * @var bool
     */
    protected $_contiguousSession = false;

    /**
     * Handle to log file
     * @var null|resource
     */
    protected static $_logHandle = null;

    /**
     * @var array
     */
    private static $_currentBrowser = array();

    /**
     * @var string
     */
    private static $_currentSessionId;

    /**
     * @var bool
     */
    private static $_currentContiguousSession;

    /**
     * @var bool
     */
    private static $_currentTestClassName;

    /**
     * Sends a command to the Selenium RC server.
     * Extension: transaction logging to opened file stream in view: TIME,REQUEST,RESPONSE or TIME,EXCEPTION
     *
     * @param string $command Command for send to Selenium RC server
     * @param array $arguments Array of arguments to command
     *
     * @return string
     * @throws RuntimeException
     */
    protected function doCommand($command, array $arguments = array())
    {
        try {
            $response = parent::doCommand($command, $arguments);
            //Fixed bug for PHPUnit_Selenium 1.2.0(1)
            if (!preg_match('/^OK/', $response)) {
                throw new RuntimeException($response);
            }
            // Add command logging
            if (!empty(self::$_logHandle)) {
                fputs(self::$_logHandle, self::udate('H:i:s.u') . "\n");
                fputs(self::$_logHandle, "\tRequest: " . $command . "\n");
                fputs(self::$_logHandle, "\tResponse: " . $response . "\n\n");
                fflush(self::$_logHandle);
            }
            return $response;
        } catch (RuntimeException $e) {
            if (!empty(self::$_logHandle)) {
                fputs(self::$_logHandle, self::udate('H:i:s.u') . "\n");
                fputs(self::$_logHandle, "\tRequest: " . $command . "\n");
                fputs(self::$_logHandle, "\tException: " . $e->getMessage() . "\n\n");
                fflush(self::$_logHandle);
            }
            throw $e;
        }
    }

    /**
     * Sets the flag to restart browser connection or not after each test
     *
     * @param bool $flag Flag to restart browser after each test or not (TRUE - do restart, FALSE - do not restart)
     *
     * @return Mage_Selenium_Driver
     */
    public function setContiguousSession($flag)
    {
        $this->_contiguousSession = $flag;
        return $this;
    }

    /**
     * Gets the flag to restart browser connection or not after each test
     * @return bool
     */
    public function getContiguousSession()
    {
        return $this->_contiguousSession;
    }

    /**
     * Set log file
     * @param $dirPath Path to directory for the log file
     */
    public function setLogHandle($dirPath)
    {
        if (is_null(self::$_logHandle)) {
            self::$_logHandle = fopen($dirPath . DIRECTORY_SEPARATOR
                                      . 'selenium-rc-' . date('d-m-Y-H-i-s') . '.log', 'a+');
        }
    }

    /**
     * Get browser settings
     * @return array
     */
    public function getBrowserSettings()
    {
        return array(
            'name'           => $this->name,
            'browser'        => $this->browser,
            'host'           => $this->host,
            'port'           => $this->port,
            'timeout'        => $this->seleniumTimeout,
            'restartBrowser' => $this->_contiguousSession,
        );
    }

    /**
     * @param string $testClassName
     *
     * @return bool
     */
    public function driverSetUp($testClassName)
    {
        $isFirst = false;

        if (self::$_currentTestClassName == null) {
            self::$_currentTestClassName = $testClassName;
        }
        if (self::$_currentBrowser == null) {
            self::$_currentBrowser = $this->getBrowserSettings();
        }
        if (self::$_currentContiguousSession === null) {
            $config = $this->getBrowserSettings();
            self::$_currentContiguousSession = $config['restartBrowser'];
        }
        if (array_diff($this->getBrowserSettings(), self::$_currentBrowser)) {
            self::$_currentBrowser = $this->getBrowserSettings();
            $this->setSessionId(self::$_currentSessionId);
            if (self::$_currentContiguousSession === false) {
                $this->setContiguousSession(true);
                $this->stop();
                $this->setContiguousSession(false);
                self::$_currentContiguousSession = null;
            }
        }
        if (self::$_currentContiguousSession === true && self::$_currentSessionId !== null) {
            $this->setSessionId(self::$_currentSessionId);
            $this->stop();
        }
        if (self::$_currentSessionId === null) {
            $this->start();
        } else {
            $this->setSessionId(self::$_currentSessionId);
        }
        $currentSession = $this->getSessionId();
        if (($currentSession != self::$_currentSessionId)) {
            self::$_currentSessionId = $currentSession;
            $isFirst = true;
        }
        if (self::$_currentTestClassName != $testClassName) {
            self::$_currentTestClassName = $testClassName;
            $isFirst = true;
        }
        return $isFirst;
    }

    /**
     * Stops browser connection if the session is not marked as contiguous
     * @return mixed
     */
    public function stop()
    {
        if (!$this->_contiguousSession) {
            return;
        }
        self::$_currentSessionId = null;
        self::$_currentBrowser = null;
        self::$_currentContiguousSession = null;
        self::$_currentTestClassName = null;
        parent::stop();
    }

    /**
     * Get current time for logging (e.g. 15:18:43.244768)
     *
     * @static
     *
     * @param string $format A composite format string
     * @param mixed $uTimeStamp Timestamp (by default = null)
     *
     * @return string A formatted date string.
     */
    public static function udate($format, $uTimeStamp = null)
    {
        if (is_null($uTimeStamp)) {
            $uTimeStamp = microtime(true);
        }

        $timestamp = floor($uTimeStamp);
        $milliseconds = round(($uTimeStamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

    /**
     * @return string
     */
    public static function getCurrentBrowser()
    {
        return self::$_currentBrowser['browser'];
    }

}
