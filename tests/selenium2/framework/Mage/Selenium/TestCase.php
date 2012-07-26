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
 * An extended test case implementation that adds useful helper methods
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_TestCase extends PHPUnit_Extensions_SeleniumTestCase
{
    ################################################################################
    #              Framework variables and constant                                #
    ################################################################################
    /**
     * Configuration object instance
     * @var Mage_Selenium_TestConfiguration
     */
    protected $_testConfig;

    /**
     * Config helper instance
     * @var Mage_Selenium_Helper_Config
     */
    protected $_configHelper;

    /**
     * UIMap helper instance
     * @var Mage_Selenium_Helper_Uimap
     */
    protected $_uimapHelper;

    /**
     * Data helper instance
     * @var Mage_Selenium_Helper_Data
     */
    protected $_dataHelper;

    /**
     * Params helper instance
     * @var Mage_Selenium_Helper_Params
     */
    protected $_paramsHelper;

    /**
     * Data Generator helper instance
     * @var Mage_Selenium_Helper_DataGenerator
     */
    protected $_dataGeneratorHelper;

    /**
     * Array of Test Helper instances
     * @var array
     */
    protected static $_testHelpers = array();

    /**
     * Saves HTML content of the current page if the test failed
     * @var bool
     */
    protected $_saveHtmlPageOnFailure = false;

    /**
     * Timeout in ms
     * @var int
     */
    protected $_browserTimeoutPeriod = 40000;

    /**
     * Name of the first page after logging into the back-end
     * @var string
     */
    protected $_firstPageAfterAdminLogin = 'dashboard';

    /**
     * Array of messages on page
     * @var array
     */
    protected static $_messages = array();

    /**
     * Additional params for navigation URL
     * @var string
     */
    private $_urlPostfix = '';

    /**
     * Testcase error
     * @var boolean
     * @deprecated
     */
    protected $_error = false;

    /**
     * Type of uimap elements
     * @var string
     */
    const FIELD_TYPE_MULTISELECT = 'multiselect';

    /**
     * Type of uimap elements
     * @var string
     */
    const FIELD_TYPE_DROPDOWN = 'dropdown';

    /**
     * Type of uimap elements
     * @var string
     */
    const FIELD_TYPE_CHECKBOX = 'checkbox';

    /**
     * Type of uimap elements
     * @var string
     */
    const FIELD_TYPE_RADIOBUTTON = 'radiobutton';

    /**
     * Type of uimap elements
     * @var string
     */
    const FIELD_TYPE_INPUT = 'field';

    ################################################################################
    #                      Selenium variables(do not rename)                       #
    ################################################################################
    /**
     * @var PHPUnit_Extensions_SeleniumTestCase_Driver[]
     */
    protected $drivers = array();

    /**
     * @var string
     */
    protected $coverageScriptUrl = '';

    /**
     * @var bool
     */
    protected $captureScreenshotOnFailure = false;

    ################################################################################
    #                             Else variables                                   #
    ################################################################################
    /**
     * Success message Xpath
     * @staticvar string
     */
    protected static $xpathSuccessMessage = "//*/descendant::*[normalize-space(@class)='success-msg'][string-length(.)>1]";

    /**
     * Error message Xpath
     * @staticvar string
     */
    protected static $xpathErrorMessage = "//*/descendant::*[normalize-space(@class)='error-msg'][string-length(.)>1]";

    /**
     * Notice message Xpath
     * @staticvar string
     */
    protected static $xpathNoticeMessage = "//*/descendant::*[normalize-space(@class)='notice-msg'][string-length(.)>1]";

    /**
     * Validation message Xpath
     * @staticvar string
     */
    protected static $xpathValidationMessage = "//*/descendant::*[normalize-space(@class)='validation-advice' and not(contains(@style,'display: none;'))][string-length(.)>1]";

    /**
     * Field Name xpath with ValidationMessage
     * @staticvar string
     */
    protected static $xpathFieldNameWithValidationMessage = "/ancestor::*[2]//label/descendant-or-self::*[string-length(text())>1]";

    /**
     * Loading holder XPath
     * @staticvar string
     */
    protected static $xpathLoadingHolder = "//div[@id='loading-mask' and not(contains(@style,'display: none'))]";

    /**
     * Log Out link
     * @staticvar string
     */
    protected static $xpathLogOutAdmin = "//div[@class='header-right']//a[@class='link-logout']";

    /**
     * Admin Logo Xpath
     * @staticvar string
     */
    protected static $xpathAdminLogo = "//img[@class='logo' and contains(@src,'logo.gif')]";

    /**
     * Incoming Message 'Close' button Xpath
     * @staticvar string
     */
    protected static $xpathIncomingMessageClose = "//*[@id='message-popup-window' and @class='message-popup show']//a[span='close']";

    /**
     * 'Go to notifications' xpath in 'Latest Message' block
     * @staticvar string
     */
    protected static $xpathGoToNotifications = "//a[text()='Go to notifications']";

    /**
     * 'Cache Management' xpath link when cache are invalided
     * @staticvar string
     */
    protected static $xpathCacheInvalidated = "//a[text()='Cache Management']";

    /**
     * 'Index Management' xpath link when indexes are invalided
     * @staticvar string
     */
    protected static $xpathIndexesInvalidated = "//a[text()='Index Management']";

    /**
     * Qty elements in Table
     * @staticvar string
     */
    protected static $qtyElementsInTable = "//td[@class='pager']//span[contains(@id,'total-count')]";

    /**
     * Constructs a test case with the given name and browser to test execution
     *
     * @param  string $name Test case name(by default = null)
     * @param  array  $data Test case data array(by default = array())
     * @param  string $dataName Name of Data set(by default = '')
     * @param  array  $browser Array of browser configuration settings: 'name', 'browser', 'host', 'port', 'timeout',
     * 'httpTimeout' (by default = array())
     */
    public function __construct($name = null, array $data = array(), $dataName = '', array $browser = array())
    {
        $this->_testConfig = Mage_Selenium_TestConfiguration::getInstance();
        $this->_configHelper = $this->_testConfig->getHelper('config');
        $this->_uimapHelper = $this->_testConfig->getHelper('uimap');
        $this->_dataHelper = $this->_testConfig->getHelper('data');
        $this->_paramsHelper = $this->_testConfig->getHelper('params');
        $this->_dataGeneratorHelper = $this->_testConfig->getHelper('dataGenerator');

        parent::__construct($name, $data, $dataName, $browser);
        if (isset($browser['timeout'])) {
            $this->_browserTimeoutPeriod = $browser['timeout'] * 1000;
        }
        $config = $this->_configHelper->getConfigFramework();
        $this->captureScreenshotOnFailure = $config['captureScreenshotOnFailure'];
        $this->screenshotPath = $this->screenshotUrl = $this->_configHelper->getScreenshotDir();
        $this->_saveHtmlPageOnFailure = $config['saveHtmlPageOnFailure'];
        $this->coverageScriptUrl = $config['coverageScriptUrl'];
    }

    public function __destruct()
    {
        if (!isset($this->drivers)) {
            return;
        }
        foreach ($this->drivers as $driver) {
            if (!$driver->getContiguousSession()) {
                $driver->setContiguousSession(true);
                $driver->stop();
            }
        }
    }

    /**
     * Delegate method calls to the driver. Overridden to load test helpers
     *
     * @param string $command Command (method) name to call
     * @param array $arguments Arguments to be sent to the called command (method)
     *
     * @return mixed
     */
    public function __call($command, $arguments)
    {
        $helper = substr($command, 0, strpos($command, 'Helper'));
        if ($helper) {
            $helper = $this->_loadHelper($helper);
            if ($helper) {
                return $helper;
            }
        }
        return parent::__call($command, $arguments);
    }

    /**
     * Loads a specific driver for the specified browser
     *
     * @param array $browser Defines what kind of driver, for a what browser will be loaded
     *
     * @return Mage_Selenium_Driver
     */
    protected function getDriver(array $browser)
    {
        if (!empty($browser)) {
            $driver = $this->_testConfig->addDriverConnection($browser);
            $driver->setTestCase($this);
            $driver->setTestId($this->testId);

            $this->drivers[] = $driver;

            return $driver;
        }
    }

    /**
     * Implementation of setUpBeforeClass() method in the object context, called as setUpBeforeTests()<br>
     * Used ONLY one time before execution of each class (tests in test class)
     * @staticvar $error Identifies if an error happend during setup. In case of an error, the tests won't be run.
     */
    public function setUp()
    {
        // Clear messages before running test
        $this->clearMessages();
        $isFirst = $this->drivers[0]->driverSetUp(get_class($this));
        static $error = null;
        if ($isFirst) {
            $browser = $this->drivers[0]->getBrowserSettings();
            if (strstr($browser['browser'],'*ie') !== false) {
                $this->useXpathLibrary('javascript-xpath');
                $this->allowNativeXpath(true);
            }
            try {
                $error = null;
                $this->setUpBeforeTests();
            } catch (Exception $e) {
                $error = $e;
            }
        }
        if($error) {
            throw $error;
        }
    }

    /**
     * Function is called before all tests in a test class
     * and can be used for some precondition(s) for all tests
     */
    public function setUpBeforeTests()
    {
    }

    /**
     * Access/load helpers from the tests. Helper class name should be like "TestScope_HelperName"
     *
     * @param string $testScope Part of the helper class name which refers to the file with the needed helper
     * @param string $helperName Name Suffix that describes helper name (default = 'Helper')
     *
     * @return object
     * @throws UnexpectedValueException
     */
    protected function _loadHelper($testScope, $helperName = 'Helper')
    {
        if (empty($testScope)) {
            throw new UnexpectedValueException('Helper name can\'t be empty');
        }

        $helpers = $this->_testConfig->getTestHelperClassNames();

        if (!isset($helpers[ucwords($testScope)])) {
            throw new UnexpectedValueException('Cannot load helper "' . $testScope . '"');
        }

        $helperClassName = $helpers[ucwords($testScope)];
        if (!isset(self::$_testHelpers[$helperClassName])) {
            if (class_exists($helperClassName)) {
                self::$_testHelpers[$helperClassName] = new $helperClassName();
            } else {
                return false;
            }
        }

        if (self::$_testHelpers[$helperClassName] instanceof Mage_Selenium_TestCase) {
            foreach (get_object_vars($this) as $name => $value) {
                self::$_testHelpers[$helperClassName]->$name = $value;
            }
        }

        return self::$_testHelpers[$helperClassName];
    }

    /**
     * Retrieve instance of helper
     * @deprecated
     * @see _loadHelper()
     *
     * @param  string $className
     *
     * @return Mage_Selenium_TestCase
     */
    public function helper($className)
    {
        $className = str_replace('/', '_', $className);
        if (strpos($className, '_Helper') === false) {
            $className .= '_Helper';
        }

        if (!isset(self::$_testHelpers[$className])) {
            if (class_exists($className)) {
                self::$_testHelpers[$className] = new $className;
            } else {
                return false;
            }
        }

        if (self::$_testHelpers[$className] instanceof Mage_Selenium_TestCase) {
            self::$_testHelpers[$className]->appendParamsDecorator($this->_paramsHelper);
        }

        return self::$_testHelpers[$className];
    }

    /**
     * Checks if there was error during last operations
     * @return boolean
     * @deprecated
     */
    public function hasError()
    {
        return $this->_error;
    }
    ################################################################################
    #                                                                              #
    #                               Assertions Methods                             #
    #                                                                              #
    ################################################################################
    /**
     * Asserts that $condition is true. Reports an error $message if $condition is false.
     * @static
     *
     * @param bool $condition Condition to assert
     * @param string|array $message Message to report if the condition is false (by default = '')
     */
    public static function assertTrue($condition, $message = '')
    {
        if (is_array($message) && $message) {
            $message = implode("\n", call_user_func_array('array_merge', $message));
        }

        if (is_object($condition)) {
            $condition = (false === $condition->hasError());
        }

        self::assertThat($condition, self::isTrue(), $message);
    }

    /**
     * Asserts that $condition is false. Reports an error $message if $condition is true.
     * @static
     *
     * @param bool $condition Condition to assert
     * @param string $message Message to report if the condition is true (by default = '')
     */
    public static function assertFalse($condition, $message = '')
    {
        if (is_array($message) && $message) {
            $message = implode("\n", call_user_func_array('array_merge', $message));
        }

        if (is_object($condition)) {
            $condition = (false === $condition->hasError());
        }

        self::assertThat($condition, self::isFalse(), $message);
    }

    ################################################################################
    #                                                                              #
    #                            Parameter helper methods                          #
    #                                                                              #
    ################################################################################
    /**
     * Append parameters decorator object
     *
     * @param Mage_Selenium_Helper_Params $paramsHelperObject Parameters decorator object
     *
     * @return Mage_Selenium_TestCase
     */
    public function appendParamsDecorator($paramsHelperObject)
    {
        $this->_paramsHelper = $paramsHelperObject;

        return $this;
    }

    /**
     * Add parameter to params object instance
     *
     * @param string $name
     * @param string $value
     *
     * @return Mage_Selenium_Helper_Params
     */
    public function addParameter($name, $value)
    {
        $this->_paramsHelper->setParameter($name, $value);
        return $this;
    }

    /**
     * Get  parameter from params object instance
     *
     * @param string $name
     *
     * @return string
     */
    public function getParameter($name)
    {
        return $this->_paramsHelper->getParameter($name);
    }

    /**
     * Define parameter %$paramName% from URL
     *
     * @param string $paramName
     * @param null|string $url
     *
     * @return null|string
     */
    public function defineParameterFromUrl($paramName, $url = null)
    {
        if (is_null($url)) {
            $url = self::_getMcaFromCurrentUrl($this->_configHelper->getConfigAreas(), $this->getLocation());
        }
        $title_arr = explode('/', $url);
        if (in_array($paramName, $title_arr) && isset($title_arr[array_search($paramName, $title_arr) + 1])) {
            return $title_arr[array_search($paramName, $title_arr) + 1];
        }
        foreach ($title_arr as $key => $value) {
            if (preg_match("#$paramName$#i", $value) && isset($title_arr[$key + 1])) {
                return $title_arr[$key + 1];
            }
        }
        return null;
    }

    /**
     * Define parameter %id% from attribute @title by XPath
     *
     * @param string $xpath
     *
     * @return null|string
     */
    public function defineIdFromTitle($xpath)
    {
        $urlFromTitleAttribute = $this->getValue($xpath . '/@title');
        if (is_numeric($urlFromTitleAttribute)) {
            return $urlFromTitleAttribute;
        }

        return $this->defineIdFromUrl($urlFromTitleAttribute);
    }

    /**
     * Define parameter %id% from URL
     *
     * @param null|string $url
     *
     * @return null|string
     */
    public function defineIdFromUrl($url = null)
    {
        return $this->defineParameterFromUrl('id', $url);
    }

    /**
     * Adds field ID to Message Xpath (sets %fieldId% parameter)
     *
     * @param string $fieldType Field type
     * @param string $fieldName Field name from UIMap
     */
    public function addFieldIdToMessage($fieldType, $fieldName)
    {
        $fieldXpath = $this->_getControlXpath($fieldType, $fieldName);
        if ($this->isElementPresent($fieldXpath . '/@id')) {
            $fieldId = $this->getAttribute($fieldXpath . '/@id');
            $fieldId = empty($fieldId) ? $this->getAttribute($fieldXpath . '/@name') : $fieldId;
        } else {
            $fieldId = $this->getAttribute($fieldXpath . '/@name');
        }
        $this->addParameter('fieldId', $fieldId);
    }

    ################################################################################
    #                                                                              #
    #                               Data helper methods                            #
    #                                                                              #
    ################################################################################
    /**
     * Generates random value as a string|text|email $type, with specified $length.<br>
     * Available $modifier:
     * <li>if $type = string - alnum|alpha|digit|lower|upper|punct
     * <li>if $type = text - alnum|alpha|digit|lower|upper|punct
     * <li>if $type = email - valid|invalid
     *
     * @param string $type Available types are 'string', 'text', 'email' (by default = 'string')
     * @param int $length Generated value length (by default = 100)
     * @param null|string $modifier Value modifier, e.g. PCRE class (by default = null)
     * @param null|string $prefix Prefix to prepend the generated value (by default = null)
     *
     * @return string
     */
    public function generate($type = 'string', $length = 100, $modifier = null, $prefix = null)
    {
        $result = $this->_dataGeneratorHelper->generate($type, $length, $modifier, $prefix);
        return $result;
    }

    /**
     * Loads test data.
     *
     * @param string $dataFile - File name or full path to file in fixture folder
     * (for example: 'default\core\Mage\AdminUser\data\AdminUsers') in which DataSet is specified
     * @param string $dataSource - DataSet name(for example: 'test_data')
     * or part of DataSet (for example: 'test_data/product')
     * @param array|null $overrideByKey
     * @param array|null $overrideByValueParam
     *
     * @return array
     * @throws PHPUnit_Framework_Exception
     */
    public function loadDataSet($dataFile, $dataSource, $overrideByKey = null, $overrideByValueParam = null)
    {
        $data = $this->_dataHelper->getDataValue($dataSource);

        if ($data === false) {
            $dataSetName = array_shift(explode('/', $dataSource));
            $this->_dataHelper->loadTestDataSet($dataFile, $dataSetName);
            $data = $this->_dataHelper->getDataValue($dataSource);
        }

        if (!is_array($data)) {
            throw new PHPUnit_Framework_Exception('Data "' . $dataSource . '" is not specified.');
        }

        if ($overrideByKey) {
            foreach ($overrideByKey as $fieldKey => $fieldValue) {
                if (!$this->overrideDataByCondition($fieldKey, $fieldValue, $data, 'byValueKey')) {
                    throw new PHPUnit_Framework_Exception("Value for '" . $fieldKey
                        . "' field is not changed: [There is no this key in dataset '" . $dataSource . "']");
                }
            }
        }

        if ($overrideByValueParam) {
            foreach ($overrideByValueParam as $fieldKey => $fieldValue) {
                if (!$this->overrideDataByCondition($fieldKey, $fieldValue, $data, 'byValueParam')) {
                    throw new PHPUnit_Framework_Exception("Value for '" . $fieldKey
                        . "' value parameter is not changed: [There is no this value parameter in dataset '"
                        . $dataSource . "']");
                }
            }
        }

        array_walk_recursive($data, array($this, 'setDataParams'));

        return $this->clearDataArray($data);
    }

    /**
     * Change in array value by condition.
     *
     * @param string $overrideKey
     * @param string $overrideValue
     * @param array $overrideArray
     * @param string $condition   byValueKey|byValueParam
     *
     * @return bool
     * @throws OutOfRangeException
     */
    public function overrideDataByCondition($overrideKey, $overrideValue, &$overrideArray, $condition)
    {
        $isOverridden = false;
        foreach ($overrideArray as $currentKey => &$currentValue) {
            switch ($condition) {
                case 'byValueKey':
                    $isFound = ($currentKey === $overrideKey);
                    break;
                case 'byValueParam':
                    $isFound = ($currentValue === '%' . $overrideKey . '%');
                    break;
                default:
                    throw new OutOfRangeException('Wrong condition');
                    break;
            }
            if ($isFound) {
                $currentValue = $overrideValue;
                $isOverridden = true;
            } elseif (is_array($currentValue)) {
                $isOverridden = $this->overrideDataByCondition($overrideKey, $overrideValue, $currentValue,
                                                              $condition) || $isOverridden;
            }
        }
        return $isOverridden;
    }

    /**
     * Set data params
     *
     * @param string $value
     * @param string $key Index of the target to randomize
     */
    public function setDataParams(&$value, $key)
    {
        if (preg_match('/%randomize%/', $value)) {
            $value = preg_replace('/%randomize%/', $this->generate('string', 5, ':lower:'), $value);
        }
        if (preg_match('/^%longValue[0-9]+%$/', $value)) {
            $length = preg_replace('/[^0-9]/', '', $value);
            $value = preg_replace('/%longValue[0-9]+%/', $this->generate('string', $length, ':alpha:'), $value);
        }
        if (preg_match('/^%specialValue[0-9]+%$/', $value)) {
            $length = preg_replace('/[^0-9]/', '', $value);
            $value = preg_replace('/%specialValue[0-9]+%/', $this->generate('string', $length, ':punct:'), $value);
        }
        if (preg_match('/%currentDate%/', $value)) {
            $value = preg_replace('/%currentDate%/', date("n/j/y"), $value);
        }
    }

    /**
     * Delete field in array with special values(for example: %noValue%)
     *
     * @param array $dataArray
     *
     * @return array|bool
     */
    public function clearDataArray($dataArray)
    {
        if (!is_array($dataArray)) {
            return false;
        }

        foreach ($dataArray as $key => $value) {
            if (is_array($value)) {
                $dataArray[$key] = $this->clearDataArray($value);
                if (count($dataArray[$key]) == false) {
                    unset($dataArray[$key]);
                }
            } elseif (preg_match('/^\%(\w)+\%$/', $value)) {
                unset($dataArray[$key]);
            }
        }

        return $dataArray;
    }

    ################################################################################
    #                    Deprecated data helper methods                            #
    ################################################################################
    /**
     * Loads test data from DataSet, specified in the $dataSource
     *
     * @deprecated
     * @see loadDataSet()
     *
     * @param string $dataSource Data source (e.g. filename in ../data without .yml extension)
     * @param null|array $override value to override in original data from data source
     * @param null|array|string $randomize Value to randomize
     *
     * @return array
     */
    public function loadData($dataSource, $override = null, $randomize = null)
    {
        $data = $this->_dataHelper->getDataValue($dataSource);

        if (!is_array($data)) {
            $this->fail('Data \'' . $dataSource . '\' is not loaded');
        }

        array_walk_recursive($data, array($this, 'setDataParams'));

        if (!empty($randomize)) {
            $randomize = (!is_array($randomize)) ? array($randomize) : $randomize;
            array_walk_recursive($data, array($this, 'randomizeData'), $randomize);
        }

        if (!empty($override) && is_array($override)) {
            $withSubArray = array();
            $withOutSubArray = array();
            foreach ($override as $key => $value) {
                if (preg_match('|/|', $key)) {
                    $withSubArray[$key]['subArray'] = preg_replace('#/[a-z0-9_]+$#i', '', $key);
                    $withSubArray[$key]['name'] = preg_replace('#^[a-z0-9_]+/#i', '', $key);
                    $withSubArray[$key]['value'] = $value;
                } else {
                    $withOutSubArray[$key] = $value;
                }
            }
            foreach ($withOutSubArray as $key => $value) {
                if (!$this->overrideData($key, $value, $data)) {
                    $data[$key] = $value;
                }
            }
            foreach ($withSubArray as $value) {
                if (!$this->overrideDataInSubArray($value['subArray'], $value['name'], $value['value'], $data)) {
                    $data[$value['subArray']][$value['name']] = $value['value'];
                }
            }
        }

        return $data;
    }

    /**
     * Remove array elements that have '%noValue%' value
     *
     * @deprecated
     * @see clearDataArray()
     *
     * @param array $array
     *
     * @return array
     */
    public function arrayEmptyClear(array $array)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = $this->arrayEmptyClear($v);
                if (count($array[$k]) == false) {
                    unset($array[$k]);
                }
            } else {
                if ($v === '%noValue%') {
                    unset($array[$k]);
                }
            }
        }

        return $array;
    }

    /**
     * Override data with index $key on-fly in the $overrideArray by new value (&$value)
     * @deprecated
     * @see overrideDataByCondition()
     *
     * @param string $overrideKey Index of the target to override
     * @param string $overrideValue Value for override
     * @param array $overrideArray Target array, which contains some index(es) to override
     *
     * @return bool
     */
    public function overrideData($overrideKey, $overrideValue, &$overrideArray)
    {
        $overrideResult = false;
        foreach ($overrideArray as $key => &$value) {
            if ($key === $overrideKey) {
                $overrideArray[$key] = $overrideValue;
                $overrideResult = true;
            } elseif (is_array($value)) {
                $result = $this->overrideData($overrideKey, $overrideValue, $value);
                if ($result || $overrideResult) {
                    $overrideResult = true;
                }
            }
        }

        return $overrideResult;
    }

    /**
     * @deprecated
     * @see overrideDataByCondition()
     *
     * @param string $subArray
     * @param string $overrideKey
     * @param string $overrideValue
     * @param array $overrideArray
     *
     * @return bool
     */
    public function overrideDataInSubArray($subArray, $overrideKey, $overrideValue, &$overrideArray)
    {
        $overrideResult = false;
        foreach ($overrideArray as $key => &$value) {
            if (is_array($value)) {
                if ($key === $subArray) {
                    foreach ($value as $k => $v) {
                        if ($k === $overrideKey) {
                            $value[$k] = $overrideValue;
                            $overrideResult = true;
                        }
                        if (is_array($v)) {
                            $result = $this->overrideDataInSubArray($subArray, $overrideKey, $overrideValue, $value);
                            if ($result || $overrideResult) {
                                $overrideResult = true;
                            }
                        }
                    }
                } else {
                    $result = $this->overrideDataInSubArray($subArray, $overrideKey, $overrideValue, $value);
                    if ($result || $overrideResult) {
                        $overrideResult = true;
                    }
                }
            }
        }
        return $overrideResult;
    }

    /**
     * Randomize data with index $key on-fly in the $randomizeArray by new value (&$value)
     *
     * @deprecated
     * @see setDataParams()
     *
     * @param string $value Value for randomization (in this case - value will be as a suffix)
     * @param string $key Index of the target to randomize
     * @param array $randomizeArray Target array, which contains some index(es) to randomize
     */
    public function randomizeData(&$value, $key, $randomizeArray)
    {
        foreach ($randomizeArray as $randomizeField) {
            if ($randomizeField === $key) {
                $value = $this->generate('string', 5, ':lower:') . '_' . $value;
            }
        }
    }

    ################################################################################
    #                                                                              #
    #                               Messages helper methods                        #
    #                                                                              #
    ################################################################################

    /**
     * Removes all added messages
     *
     * @param null|string $type
     */
    public function clearMessages($type = null)
    {
        if ($type && array_key_exists($type, self::$_messages)) {
            unset(self::$_messages[$type]);
        } elseif ($type == null) {
            self::$_messages = null;
        }
    }

    /**
     * Gets all messages on the pages
     */
    protected function _parseMessages()
    {
        self::$_messages['success'] = $this->getElementsByXpath(self::$xpathSuccessMessage);
        self::$_messages['error'] = $this->getElementsByXpath(self::$xpathErrorMessage);
        self::$_messages['validation'] = $this->getElementsByXpath(self::$xpathValidationMessage,
                                                                   'text', self::$xpathFieldNameWithValidationMessage);
    }

    /**
     * Returns all messages (or messages of the specified type) on the page
     *
     * @param null|string $type Message type: validation|error|success
     *
     * @return array
     */
    public function getMessagesOnPage($type = null)
    {
        $this->_parseMessages();
        if ($type) {
            return self::$_messages[$type];
        }

        return self::$_messages;
    }


    /**
     * Returns all parsed messages (or messages of the specified type)
     *
     * @param null|string $type Message type: validation|error|success (default = null, for all messages)
     *
     * @return array|null
     */
    public function getParsedMessages($type = null)
    {
        if ($type) {
            return (isset(self::$_messages[$type]))
                ? self::$_messages[$type]
                : null;
        }
        return self::$_messages;
    }

    /**
     * Adds validation|error|success message(s)
     *
     * @param string $type Message type: validation|error|success
     * @param string|array $message Message text
     */
    public function addMessage($type, $message)
    {
        if (is_array($message)) {
            foreach ($message as $value) {
                self::$_messages[$type][] = $value;
            }
        } else {
            self::$_messages[$type][] = $message;
        }
    }

    /**
     * Adds a verification message
     *
     * @param string|array $message Message text
     */
    public function addVerificationMessage($message)
    {
        $this->addMessage('verification', $message);
    }


    /**
     * Verifies messages count
     *
     * @param int $count Expected number of message(s) on the page
     * @param null|string $xpath XPath of a message(s) that should be evaluated (default = null)
     *
     * @return int Number of nodes that match the specified $xpath
     */
    public function verifyMessagesCount($count = 1, $xpath = null)
    {
        if ($xpath === null) {
            $xpath = self::$xpathValidationMessage;
        }
        $this->_parseMessages();
        return $this->getXpathCount($xpath) == $count;
    }

    /**
     * Check if the specified message exists on the page
     *
     * @param string $message Message ID from UIMap
     *
     * @return bool
     */
    public function checkMessage($message)
    {
        $messageLocator = $this->_getMessageXpath($message);
        return $this->checkMessageByXpath($messageLocator);
    }

    /**
     * Checks if  message with the specified XPath exists on the page
     *
     * @param string $xpath XPath of message to checking
     *
     * @return bool
     */
    public function checkMessageByXpath($xpath)
    {
        $this->_parseMessages();
        if ($xpath && $this->isElementPresent($xpath)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if any 'error' message exists on the page
     *
     * @param null|string $message Error message ID from UIMap OR XPath of the error message (by default = null)
     *
     * @return bool
     */
    public function errorMessage($message = null)
    {
        return (!empty($message))
            ? $this->checkMessage($message)
            : $this->checkMessageByXpath(self::$xpathErrorMessage);
    }

    /**
     * Checks if any 'success' message exists on the page
     *
     * @param null|string $message Success message ID from UIMap OR XPath of the success message (by default = null)
     *
     * @return bool
     */
    public function successMessage($message = null)
    {
        return (!empty($message))
            ? $this->checkMessage($message)
            : $this->checkMessageByXpath(self::$xpathSuccessMessage);
    }

    /**
     * Checks if any 'validation' message exists on the page
     *
     * @param null|string $message Validation message ID from UIMap OR XPath of the validation message (by default = null)
     *
     * @return bool
     */
    public function validationMessage($message = null)
    {
        return (!empty($message))
            ? $this->checkMessage($message)
            : $this->checkMessageByXpath(self::$xpathValidationMessage);
    }

    /**
     * Asserts that the specified message of the specified type is present on the current page
     *
     * @param string $type success|validation|error
     * @param null|string $message Message ID from UIMap
     */
    public function assertMessagePresent($type, $message = null)
    {
        $method = strtolower($type) . 'Message';
        $this->assertTrue($this->$method($message), $this->getMessagesOnPage());
    }

    /**
     * Assert there are no verification errors
     */
    public function assertEmptyVerificationErrors()
    {
        $verificationErrors = $this->getParsedMessages('verification');
        if ($verificationErrors) {
            $this->fail(implode("\n", $verificationErrors));
        }
    }

    ################################################################################
    #                                                                              #
    #                               Navigation helper methods                      #
    #                                                                              #
    ################################################################################
    /**
     * Set additional params for navigation
     * @param string $params your params to add to URL (?paramName1=paramValue1&paramName2=paramValue2)
     */
    public function setUrlPostfix($params)
    {
        $this->_urlPostfix = $params;
    }

    /**
     * Navigates to the specified page in specified area.<br>
     * Page identifier must be described in the UIMap.
     *
     * @param string $area Area identifier (by default = 'frontend')
     * @param string $page Page identifier (by default = 'home')
     * @param bool $validatePage
     *
     * @return Mage_Selenium_TestCase
     */
    public function goToArea($area = 'frontend', $page = 'home', $validatePage = true)
    {
        $this->_configHelper->setArea($area);
        $this->navigate($page, $validatePage);
        return $this;
    }

    /**
     * Navigates to the specified page in the current area.<br>
     * Page identifier must be described in the UIMap.
     *
     * @param string $page Page identifier
     * @param bool $validatePage
     *
     * @return Mage_Selenium_TestCase
     */
    public function navigate($page, $validatePage = true)
    {
        $area = $this->_configHelper->getArea();
        $clickXpath = $this->_uimapHelper->getPageClickXpath($area, $page, $this->_paramsHelper);
        if ($clickXpath && $this->isElementPresent($clickXpath)) {
            $this->click($clickXpath);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        } elseif (isset($this->_urlPostfix)) {
            $this->open($this->_uimapHelper->getPageUrl($area, $page, $this->_paramsHelper) . $this->_urlPostfix);
        } else {
            $this->open($this->_uimapHelper->getPageUrl($area, $page, $this->_paramsHelper));
        }
        if ($validatePage) {
            $this->validatePage($page);
        }

        return $this;
    }

    /**
     * Navigate to the specified admin page.<br>
     * Page identifier must be described in the UIMap. Opens "Dashboard" page by default.
     *
     * @param string $page Page identifier (by default = 'dashboard')
     * @param bool $validatePage
     *
     * @return Mage_Selenium_TestCase
     */
    public function admin($page = 'dashboard', $validatePage = true)
    {
        $this->goToArea('admin', $page, $validatePage);
        return $this;
    }

    /**
     * Navigate to the specified frontend page<br>
     * Page identifier must be described in the UIMap. Opens "Home page" by default.
     *
     * @param string $page Page identifier (by default = 'home')
     * @param bool $validatePage
     *
     * @return Mage_Selenium_TestCase
     */
    public function frontend($page = 'home', $validatePage = true)
    {
        $this->goToArea('frontend', $page, $validatePage);
        return $this;
    }

    ################################################################################
    #                                                                              #
    #                                Area helper methods                           #
    #                                                                              #
    ################################################################################
    /**
     * Gets current location area<br>
     * Usage: define area currently operating.
     * <li>Possible areas: frontend | admin
     * @return string
     */
    public function getCurrentLocationArea()
    {
        $currentArea = self::_getAreaFromCurrentUrl($this->_configHelper->getConfigAreas(),
                                                    $this->getLocation());
        $this->_configHelper->setArea($currentArea);
        return $currentArea;
    }

    /**
     * Find area in areasConfig using full page URL
     * @static
     *
     * @param array $areasConfig Full area config
     * @param string $currentUrl Full URL to page
     *
     * @return string
     */
    protected static function _getAreaFromCurrentUrl($areasConfig, $currentUrl)
    {
        $currentArea = '';
        $currentUrl = preg_replace('|^http([s]{0,1})://|', '', preg_replace('|/index.php/?|', '/', $currentUrl));

        foreach ($areasConfig as $area => $areaConfig) {
            $areaUrl = preg_replace('|^http([s]{0,1})://|', '',
                                    preg_replace('|/index.php/?|', '/', $areaConfig['url']));
            if (strpos($currentUrl, $areaUrl) === 0) {
                $currentArea = $area;
                break;
            }
        }

        return $currentArea;
    }

    /**
     * Set current area
     *
     * @param string $name
     *
     * @return Mage_Selenium_TestCase
     */
    public function setArea($name)
    {
        $this->_configHelper->setArea($name);
        return $this;
    }

    /**
     * Return current area name
     * @return string
     * @throws OutOfRangeException
     */
    public function getArea()
    {
        return $this->_configHelper->getArea();
    }
    ################################################################################
    #                                                                              #
    #                       UIMap of Page helper methods                           #
    #                                                                              #
    ################################################################################
    /**
     * Retrieves Page data from UIMap by $pageKey
     *
     * @param string $area Area identifier
     * @param string $pageKey UIMap page key
     *
     * @return Mage_Selenium_Uimap_Page
     */
    public function getUimapPage($area, $pageKey)
    {
        return $this->_uimapHelper->getUimapPage($area, $pageKey, $this->_paramsHelper);
    }

    /**
     * Retrieves current Page data from UIMap.
     * Gets current page name from an internal variable.
     * @return Mage_Selenium_Uimap_Page
     */
    public function getCurrentUimapPage()
    {
        return $this->getUimapPage($this->_configHelper->getArea(), $this->getCurrentPage());
    }

    /**
     * Retrieves current Page data from UIMap.
     * Gets current page name from the current URL.
     * @return Mage_Selenium_Uimap_Page
     */
    public function getCurrentLocationUimapPage()
    {
        $areasConfig = $this->_configHelper->getConfigAreas();
        $currentUrl = $this->getLocation();
        $mca = self::_getMcaFromCurrentUrl($areasConfig, $currentUrl);
        $area = self::_getAreaFromCurrentUrl($areasConfig, $currentUrl);
        return $this->_uimapHelper->getUimapPageByMca($area, $mca, $this->_paramsHelper);
    }

    ################################################################################
    #                                                                              #
    #                             Page ID helper methods                           #
    #                                                                              #
    ################################################################################
    /**
     * Change current page
     *
     * @param string $page
     *
     * @return Mage_Selenium_TestCase
     */
    public function setCurrentPage($page)
    {
        $this->_configHelper->setCurrentPageId($page);
        return $this;
    }

    /**
     * Get current page
     * @return string
     */
    public function getCurrentPage()
    {
        return $this->_configHelper->getCurrentPageId();
    }

    /**
     * Find PageID in UIMap in the current area using full page URL
     *
     * @param string|null $url Full URL
     *
     * @return string
     */
    protected function _findCurrentPageFromUrl($url = null)
    {
        if (is_null($url)) {
            $url = str_replace($this->_urlPostfix, '', $this->getLocation());
        }
        $areasConfig = $this->_configHelper->getConfigAreas();
        $mca = self::_getMcaFromCurrentUrl($areasConfig, $url);
        $area = self::_getAreaFromCurrentUrl($areasConfig, $url);
        $page = $this->_uimapHelper->getUimapPageByMca($area, $mca, $this->_paramsHelper);

        return $page->getPageId();
    }

    /**
     * Checks if the currently opened page is $page.<br>
     * Returns true if the specified page is the current page, otherwise returns false and sets the error message:
     * "Opened the wrong page: $currentPage (should be:$page)".<br>
     * Page identifier must be described in the UIMap.
     *
     * @param string $page Page identifier
     *
     * @return bool
     */
    public function checkCurrentPage($page)
    {
        $currentPage = $this->_findCurrentPageFromUrl();
        if ($currentPage != $page) {
            $this->addVerificationMessage("Opened the wrong page '"
                                              . $currentPage . "'(should be: '" . $page . "')");
            return false;
        }
        return true;
    }

    /**
     * Validates properties of the current page.
     *
     * @param string $page Page identifier
     */
    public function validatePage($page = '')
    {
        if ($page) {
            $this->assertTrue($this->checkCurrentPage($page), $this->getMessagesOnPage());
        } else {
            $page = $this->_findCurrentPageFromUrl();
        }
        $this->assertTextNotPresent('Fatal error', 'Fatal error on page');
        $this->assertTextNotPresent('There has been an error processing your request',
                                    'Fatal error on page: \'There has been an error processing your request\'');
        $this->assertTextNotPresent('Notice:', 'Notice error on page');
        $this->assertTextNotPresent('Parse error', 'Parse error on page');
        if (!$this->isElementPresent(self::$xpathNoticeMessage)) {
            $this->assertTextNotPresent('Warning:', 'Warning on page');
        }
        $this->assertTextNotPresent('If you typed the URL directly', 'The requested page was not found.');
        $this->assertTextNotPresent('was not found', 'Something was not found:)');
        $this->assertTextNotPresent('Service Temporarily Unavailable', 'Service Temporarily Unavailable');
        $this->assertTextNotPresent('The page isn\'t redirecting properly', 'The page isn\'t redirecting properly');
        //@TODO
        //$expectedTitle = $this->getUimapPage($this->_configHelper->getArea(), $page)->getTitle($this->_paramsHelper);
        //$this->assertSame($expectedTitle, $this->getTitle(), 'Page title is unexpected');
        $this->setCurrentPage($page);
    }

    ################################################################################
    #                                                                              #
    #                       Page Elements helper methods                           #
    #                                                                              #
    ################################################################################

    /**
     * Get MCA-part of page URL
     * @static
     *
     * @param array $areasConfig Full area config
     * @param string $currentUrl Current URL
     *
     * @return string
     */
    protected static function _getMcaFromCurrentUrl($areasConfig, $currentUrl)
    {
        $mca = '';
        $currentArea = '';
        $baseUrl = '';
        $currentUrl = preg_replace('|^http([s]{0,1})://|', '', preg_replace('|/index.php/?|', '/', $currentUrl));
        foreach ($areasConfig as $area => $areaConfig) {
            $areaUrl = preg_replace('|^http([s]{0,1})://|', '',
                                    preg_replace('|/index.php/?|', '/', $areaConfig['url']));
            if (strpos($currentUrl, $areaUrl) === 0) {
                $baseUrl = $areaUrl;
                $currentArea = $area;
                break;
            }
        }
        if (strpos($currentUrl, $baseUrl) !== false) {
            $mca = trim(substr($currentUrl, strlen($baseUrl)), " /\\");
        }

        if ($mca && $mca[0] != '/') {
            $mca = '/' . $mca;
        }

        if ($currentArea == 'admin') {
            //Removes part of url that appears after pressing "Reset Filter" or "Search" button in grid
            //(when not using ajax to reload the page)
            $mca = preg_replace('|/filter/((\S)+)?/form_key/[A-Za-z0-9]+/?|', '/', $mca);
            //Delete secret key from url
            $mca = preg_replace('|/(index/)?key/[A-Za-z0-9]+/?|', '/', $mca);
            //Delete action part of mca if it's index
            $mca = preg_replace('|/index/?$|', '/', $mca);
        } elseif ($currentArea == 'frontend') {
            //Delete action part of mca if it's index
            $mca = preg_replace('|/index/?$|', '/', $mca);
        }
        return preg_replace('|^/|', '', $mca);
    }

    /**
     * Get URL of the specified page
     *
     * @param string $area Application area
     * @param string $page UIMap page key
     *
     * @return string
     */
    public function getPageUrl($area, $page)
    {
        return $this->_uimapHelper->getPageUrl($area, $page, $this->_paramsHelper);
    }

    /**
     * Get part of UIMap for specified uimap element(does not use for 'message' element)
     *
     * @param string $elementType
     * @param string $elementName
     * @param Mage_Selenium_Uimap_Page|null $uimap
     *
     * @return mixed
     * @throw PHPUnit_Framework_Exception
     */
    protected function _findUimapElement($elementType, $elementName, $uimap = null)
    {
        if (is_null($uimap)) {
            if ($elementType == 'button') {
                $generalButtons = $this->getCurrentUimapPage()->getMainButtons();
                if (isset($generalButtons[$elementName])) {
                    return $generalButtons[$elementName];
                }
            }
            if ($elementType != 'fieldset' && $elementType != 'tab') {
                $uimap = $this->_getActiveTabUimap();
                if (is_null($uimap)) {
                    $uimap = $this->getCurrentUimapPage();
                }
            } else {
                $uimap = $this->getCurrentUimapPage();
            }
        }
        try {
            $method = 'find' . ucfirst(strtolower($elementType));
            return $uimap->$method($elementName, $this->_paramsHelper);
        } catch (Exception $e) {
            $errorMessage = 'Current location url: ' . $this->getLocation() . "\n"
                . 'Current page "' . $this->getCurrentPage() . '": '
                . $e->getMessage() . ' - "' . $elementName . '"' . "\n"
                . 'Messages on current page:' . "\n"
                . implode("\n", call_user_func_array('array_merge', $this->getMessagesOnPage()));
            throw new PHPUnit_Framework_Exception($errorMessage);
        }
    }

    /**
     * Get part of UIMap for opened tab
     * @return mixed
     */
    protected function _getActiveTabUimap()
    {
        $tabData = $this->getCurrentUimapPage()->getAllTabs($this->_paramsHelper);
        foreach ($tabData as $tabUimap) {
            $isTabOpened = '';
            $tabXpath = $tabUimap->getXpath();
            if (preg_match('/^css=/', $tabXpath)) {
                if ($this->isElementPresent($tabXpath . '[class]')) {
                    $isTabOpened = $this->getAttribute($tabXpath . '@class');
                }
            } elseif ($this->isElementPresent($tabXpath . '[@class]')) {
                $isTabOpened = $this->getAttribute($tabXpath . '@class');
            } elseif ($this->isElementPresent($tabXpath . '/parent::*[@class]')) {
                $isTabOpened = $this->getAttribute($tabXpath . '/parent::*@class');
            }
            if (preg_match('/active/', $isTabOpened)) {
                return $tabUimap;
            }
        }
        return null;
    }

    /**
     * Gets XPath of a control with the specified name and type.
     *
     * @param string $controlType Type of control (e.g. button | link | radiobutton | checkbox)
     * @param string $controlName Name of a control from UIMap
     * @param mixed $uimap
     *
     * @return string
     */
    protected function _getControlXpath($controlType, $controlName, $uimap = null)
    {
        if ($controlType === 'message'){
            return $this->_getMessageXpath($controlName);
        }
        $xpath = $this->_findUimapElement($controlType, $controlName, $uimap);
        if (is_object($xpath) && method_exists($xpath, 'getXPath')) {
            $xpath = $xpath->getXPath($this->_paramsHelper);
        }

        return $xpath;
    }

    /**
     * Gets XPath of a message with the specified name.
     *
     * @param string $message Name of a message from UIMap
     * @return string
     * @throws RuntimeException
     */
    protected function _getMessageXpath($message)
    {
        $messages = $this->getCurrentUimapPage()->getAllElements('messages');
        $messageLocator = $messages->get($message, $this->_paramsHelper);
        if ($messageLocator === null) {
            $errorMessage = 'Current location url: ' . $this->getLocation() . "\n"
                . 'Current page "' . $this->getCurrentPage() . '": ' . 'Message "' . $message . '" is not found';
            throw new RuntimeException($errorMessage);
        }
        return $messageLocator;
    }

    /**
     * Gets map data values to UIPage form
     *
     * @param mixed $fieldsets Array of fieldsets to fill
     * @param array $data Array of data to fill
     *
     * @return array
     */
    protected function _getFormDataMap($fieldsets, $data)
    {
        $dataMap = array();
        $uimapFields = array();

        foreach ($data as $dataFieldName => $dataFieldValue) {
            if ($dataFieldValue == '%noValue%') {
                continue;
            }
            foreach ($fieldsets as $fieldset) {
                $uimapFields[self::FIELD_TYPE_MULTISELECT] = $fieldset->getAllMultiselects();
                $uimapFields[self::FIELD_TYPE_DROPDOWN] = $fieldset->getAllDropdowns();
                $uimapFields[self::FIELD_TYPE_RADIOBUTTON] = $fieldset->getAllRadiobuttons();
                $uimapFields[self::FIELD_TYPE_CHECKBOX] = $fieldset->getAllCheckboxes();
                $uimapFields[self::FIELD_TYPE_INPUT] = $fieldset->getAllFields();
                foreach ($uimapFields as $fieldsType => $fieldsData) {
                    foreach ($fieldsData as $uimapFieldName => $uimapFieldValue) {
                        if ($dataFieldName == $uimapFieldName) {
                            $dataMap[$dataFieldName] = array(
                                'type'  => $fieldsType,
                                'path'  => $uimapFieldValue,
                                'value' => $dataFieldValue
                            );
                            break 3;
                        }
                    }
                }
            }
        }

        return $dataMap;
    }

    ################################################################################
    #                                                                              #
    #                           Framework helper methods                           #
    #                                                                              #
    ################################################################################

    /**
     * SavesHTML content of the current page and return information about it.
     * Return an empty string if the screenshotPath property is empty.
     *
     * @param null|string $fileName
     *
     * @return string
     */
    public function saveHtmlPage($fileName = null)
    {
        if (!empty($this->screenshotPath)) {
            return '';
        }
        if ($fileName == null) {
            $fileName = date('d-m-Y-H-i-s') . '_' . $this->getName();
        }
        $filePath = $this->getScreenshotPath() . $fileName;
        $file = fopen($filePath . '.html', 'a+');
        fputs($file, $this->drivers[0]->getHtmlSource());
        fflush($file);
        fclose($file);
        return 'HTML Page: ' . $filePath . ".html\n";
    }

    /**
     * Take a screenshot and return information about it.
     * Return an empty string if the screenshotPath property is empty.
     *
     * @param null|string $fileName
     *
     * @return string
     */
    public function takeScreenshot($fileName = null)
    {
        if (empty($this->screenshotPath)) {
            return '';
        }
        try {
            $screenshotContent = base64_decode($this->drivers[0]->captureEntirePageScreenshotToString());
        } catch (Exception $e) {
            return '';
        }
        if(empty($screenshotContent)) {
            return '';
        }
        if ($fileName == null) {
            $fileName = date('d-m-Y-H-i-s') . '_' . $this->getName();
        }
        $filePath = $this->getScreenshotPath() . $fileName;
        $file = fopen($filePath . '.png', 'a+');
        fputs($file, $screenshotContent);
        fflush($file);
        fclose($file);
        return 'Screenshot: ' . $filePath . ".png\n";
    }

    /**
     * Clicks a control with the specified name and type.
     *
     * @param string $controlType Type of control (e.g. button|link|radiobutton|checkbox)
     * @param string $controlName Name of a control from UIMap
     * @param bool $willChangePage Triggers page reloading. If clicking the control doesn't result<br>
     * in page reloading, should be false (by default = true).
     *
     * @return Mage_Selenium_TestCase
     */
    public function clickControl($controlType, $controlName, $willChangePage = true)
    {
        $xpath = $this->_getControlXpath($controlType, $controlName);
        if (!$this->isVisible($xpath)) {
            $this->fail('Control "' . $controlName . '" is not present on the page "'
                            . $this->getCurrentPage() . '". ' . 'Type: ' . $controlType . ', xpath: ' . $xpath);
        }
        $this->click($xpath);
        if ($willChangePage) {
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->addParameter('id', $this->defineIdFromUrl());
            $this->validatePage();
        }
        return $this;
    }

    /**
     * Click a button with the specified name
     *
     * @param string $button Name of a control from UIMap
     * @param bool $willChangePage Triggers page reloading. If clicking the control doesn't result<br>
     * in page reloading, should be false (by default = true).
     *
     * @return Mage_Selenium_TestCase
     */
    public function clickButton($button, $willChangePage = true)
    {
        return $this->clickControl('button', $button, $willChangePage);
    }

    /**
     * Clicks a control with the specified name and type
     * and confirms the confirmation popup with the specified message.
     *
     * @param string $controlType Type of control (e.g. button|link)
     * @param string $controlName Name of a control from UIMap
     * @param string $message Confirmation message
     * @param bool $willChangePage Triggers page reloading. If clicking the control doesn't result<br>
     * in page reloading, should be false (by default = true).
     *
     * @return bool
     */
    public function clickControlAndConfirm($controlType, $controlName, $message, $willChangePage = true)
    {
        $buttonXpath = $this->_getControlXpath($controlType, $controlName);
        if ($this->isElementPresent($buttonXpath)) {
            $confirmation = $this->_getMessageXpath($message);
            $this->chooseCancelOnNextConfirmation();
            $this->click($buttonXpath);
            if ($this->isConfirmationPresent()) {
                $text = $this->getConfirmation();
                if ($text == $confirmation) {
                    $this->chooseOkOnNextConfirmation();
                    $this->click($buttonXpath);
                    $this->getConfirmation();
                    if ($willChangePage) {
                        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
                        $this->validatePage();
                    }
                    return true;
                } else {
                    $this->addVerificationMessage("The confirmation text incorrect: {$text}");
                }
            } else {
                $this->addVerificationMessage('The confirmation does not appear');
                if ($willChangePage) {
                    $this->waitForPageToLoad($this->_browserTimeoutPeriod);
                    $this->validatePage();
                }
                return true;
            }
        } else {
            $this->addVerificationMessage("There is no way to click on control(There is no '$controlName' control)");
        }

        return false;
    }

    /**
     * Submit form and confirm the confirmation popup with the specified message.
     *
     * @param string $buttonName Name of a button from UIMap
     * @param string $message Confirmation message id from UIMap
     * @param bool $willChangePage Triggers page reloading. If clicking the control doesn't result<br>
     * in page reloading, should be false (by default = true).
     *
     * @return bool
     */
    public function clickButtonAndConfirm($buttonName, $message, $willChangePage = true)
    {
        return $this->clickControlAndConfirm('button', $buttonName, $message, $willChangePage);
    }

    /**
     * Searches a control with the specified name and type on the page.
     * If the control is present, returns true; otherwise false.
     *
     * @param string $controlType Type of control (e.g. button | link | radiobutton | checkbox)
     * @param string $controlName Name of a control from UIMap
     *
     * @return bool
     */
    public function controlIsPresent($controlType, $controlName)
    {
        $xpath = $this->_getControlXpath($controlType, $controlName);
        if ($this->isElementPresent($xpath)) {
            return true;
        }

        return false;
    }

    /**
     * Searches a button with the specified name on the page.
     * If the button is present, returns true; otherwise false.
     *
     * @param string $button Name of a button from UIMap
     *
     * @return bool
     */
    public function buttonIsPresent($button)
    {
        return $this->controlIsPresent('button', $button);
    }

    /**
     * Open tab
     *
     * @param string $tabName tab id from uimap
     *
     * @throw OutOfRangeException
     */
    public function openTab($tabName)
    {
        $waitAjax = false;
        $tabXpath = $this->_getControlXpath('tab', $tabName);
        if ($this->isElementPresent($tabXpath . '[@class]')) {
            $isTabOpened = $this->getAttribute($tabXpath . '/@class');
        } elseif ($this->isElementPresent($tabXpath . '/parent::*[@class]')) {
            $isTabOpened = $this->getAttribute($tabXpath . '/parent::*/@class');
        } else {
            throw new OutOfRangeException("Wrong xpath for tab: [$tabName : $tabXpath]");
        }
        if (!preg_match('/active/', $isTabOpened)) {
            if (preg_match('/ajax/', $isTabOpened)) {
                $waitAjax = true;
            }
            $this->clickControl('tab', $tabName, false);
            if ($waitAjax) {
                $this->pleaseWait();
            }
        }
    }

    /**
     * Gets all element(s) by XPath
     *
     * @param string $xpath General XPath of looking up element(s)
     * @param string $get What to get. Allowed params: 'text' or 'value' (by default = 'text')
     * @param string $additionalXPath Additional XPath (by default= '')
     *
     * @return array
     * @throw OutOfRangeException
     */
    public function getElementsByXpath($xpath, $get = 'text', $additionalXPath = '')
    {
        $elements = array();

        if (!empty($xpath)) {
            $totalElements = $this->getXpathCount($xpath);
            $pos = stripos(trim($xpath), 'css=');
            for ($i = 1; $i < $totalElements + 1; $i++) {
                if ($pos !== false && $pos == 0) {
                    $x = $xpath . ':nth(' . ($i - 1) . ')';
                } else {
                    $x = $xpath . '[' . $i . ']';
                }
                switch ($get) {
                    case 'value' :
                        $element = $this->getValue($x);
                        break;
                    case 'text' :
                        $element = $this->getText($x);
                        break;
                    default :
                        throw new OutOfRangeException('Possible values of the variable $get only "text" and "value"');
                        break;
                }

                if (!empty($element)) {
                    if ($additionalXPath) {
                        if ($this->isElementPresent($x . $additionalXPath)) {
                            $label = trim($this->getText($x . $additionalXPath), " *\t\n\r");
                        } else {
                            $label = $this->getAttribute($x . "@id");
                            $label = strrev($label);
                            $label = strrev(substr($label, 0, strpos($label, "-")));
                        }
                        if ($label) {
                            $element = '"' . $label . '": ' . $element;
                        }
                    }

                    $elements[] = $element;
                }
            }
        }

        return $elements;
    }

    /**
     * Gets an element by XPath
     *
     * @param string $xpath XPath of an element to look up
     * @param string $get What to get. Allowed params: 'text' or 'value' (by default = 'text')
     *
     * @return mixed
     */
    public function getElementByXpath($xpath, $get = 'text')
    {
        $elements = $this->getElementsByXpath($xpath, $get);
        return array_shift($elements);
    }

    /**
     * Returns number of nodes that match the specified CSS selector,
     * eg. "table" would give number of tables.
     *
     * @param string $locator CSS selector
     *
     * @return int
     */
    public function getCssCount($locator)
    {
        $script = "this.browserbot.evaluateCssCount('" . addslashes($locator) . "', this.browserbot.getDocument())";
        return $this->getEval($script);
    }

    /**
     * Returns number of nodes that match the specified xPath selector,
     * eg. "table" would give number of tables.
     *
     * @param string $locator xPath selector
     *
     * @return int
     */
    public function getXpathCount($locator)
    {
        $pos = stripos(trim($locator), 'css=');
        if ($pos !== false && $pos == 0) {
            return $this->getCssCount($locator);
        }
        return parent::getXpathCount($locator);
    }

    /**
     * Returns table column names
     *
     * @param string $tableXpath
     *
     * @return array
     */
    public function getTableHeadRowNames($tableXpath = '//table[@id]')
    {
        $xpath = $tableXpath . "//tr[normalize-space(@class)='headings']";
        if (!$this->isElementPresent($xpath)) {
            $this->fail('Incorrect table head xpath: ' . $xpath);
        }

        $cellNum = $this->getXpathCount($xpath . '/th');
        $headNames = array();
        for ($cell = 0; $cell < $cellNum; $cell++) {
            $cellLocator = $tableXpath . '.0.' . $cell;
            $headNames[$cell] = $this->getTable($cellLocator);
        }
        return array_diff($headNames, array(''));
    }

    /**
     * Returns table column ID based on the column name.
     *
     * @param string $columnName
     * @param string $tableXpath
     *
     * @return int
     */
    public function getColumnIdByName($columnName, $tableXpath = '//table[@id]')
    {
        return array_search($columnName, $this->getTableHeadRowNames($tableXpath)) + 1;
    }

    /**
     * Waits for the element to appear
     *
     * @param string|array $locator XPath locator or array of locators
     * @param int $timeout Timeout period in seconds (by default = 40)
     *
     * @return bool
     */
    public function waitForElement($locator, $timeout = 40)
    {
        $iStartTime = time();
        while ($timeout > time() - $iStartTime) {
            if (is_array($locator)) {
                foreach ($locator as $loc) {
                    if ($this->isElementPresent($loc)) {
                        return true;
                    }
                }
            } else {
                if ($this->isElementPresent($locator)) {
                    return true;
                }
            }
            sleep(1);
        }
        return false;
    }


    /**
     * Waits for the element(s) to be visible
     *
     * @param string|array $locator XPath locator or array of locators
     * @param int $timeout Timeout period in seconds (by default = 40)
     *
     * @return bool
     */
    public function waitForElementVisible($locator, $timeout = 40)
    {
        $iStartTime = time();
        while ($timeout > time() - $iStartTime) {
            if (is_array($locator)) {
                foreach ($locator as $loc) {
                    if ($this->isVisible($loc)) {
                        return true;
                    }
                }
            } else {
                if ($this->isVisible($locator)) {
                    return true;
                }
            }
            sleep(1);
        }
        return false;
    }

    /**
     * Waits for AJAX request to continue.<br>
     * Method works only if AJAX request was sent by Prototype or JQuery framework.
     *
     * @param int $timeout Timeout period in milliseconds. If not set, uses a default period.
     */
    public function waitForAjax($timeout = null)
    {
        if (is_null($timeout)) {
            $timeout = $this->_browserTimeoutPeriod;
        }
        $jsCondition = 'var c = function(){if(typeof selenium.browserbot.getCurrentWindow().Ajax != "undefined"){'
            . 'if(selenium.browserbot.getCurrentWindow().Ajax.activeRequestCount){return false;};};'
            . 'if(typeof selenium.browserbot.getCurrentWindow().jQuery != "undefined"){'
            . 'if(selenium.browserbot.getCurrentWindow().jQuery.active){return false;};};return true;};c();';
        $this->waitForCondition($jsCondition, $timeout);
    }

    /**
     * Submits the opened form.
     *
     * @param string $buttonName Name of the button, what intended to save (submit) form (from UIMap)
     * @param bool $validate
     *
     * @return Mage_Selenium_TestCase
     */
    public function saveForm($buttonName, $validate = true)
    {
        $this->_parseMessages();
        foreach (self::$_messages as $key => $value) {
            self::$_messages[$key] = array_unique($value);
        }
        $success = self::$xpathSuccessMessage;
        $error = self::$xpathErrorMessage;
        $validation = self::$xpathValidationMessage;
        $types = array('success', 'error', 'validation');
        foreach ($types as $message) {
            if (array_key_exists($message, self::$_messages)) {
                $exclude = '';
                foreach (self::$_messages[$message] as $messageText) {
                    $exclude .= "[not(..//.='$messageText')]";
                }
                ${$message} .= $exclude;
            }
        }
        $this->clickButton($buttonName, false);
        $this->waitForElement(array($success, $error, $validation));
        $this->addParameter('id', $this->defineIdFromUrl());
        if ($validate) {
            $this->validatePage();
        }

        return $this;
    }

    /**
     * Performs scrolling to the specified element in the specified list(block) with the specified name.
     *
     * @param string $elementType Type of the element that should be visible after scrolling
     * @param string $elementName Name of the element that should be visible after scrolling
     * @param string $blockType Type of the block where to use scroll
     * @param string $blockName Name of the block where to use scroll
     */
    public function moveScrollToElement($elementType, $elementName, $blockType, $blockName)
    {
        // Getting XPath of the element what should be visible after scrolling
        $specElementXpath = $this->_getControlXpath($elementType, $elementName);
        // Getting @ID of the element what should be visible after scrolling
        $specElementId = $this->getAttribute($specElementXpath . "/@id");
        // Getting XPath of the block where scroll is using
        $specFieldsetXpath = $this->_getControlXpath($blockType, $blockName);
        // Getting @ID of the block where scroll is using
        $specFieldsetId = $this->getAttribute($specFieldsetXpath . "/@id");
        // Getting offset position of the element what should be visible after scrolling
        $destinationOffsetTop = $this->getEval("this.browserbot.findElement('id=" . $specElementId . "').offsetTop");
        // Moving scroll bar to previously defined offset
        // Position (to the element what should be visible after scrolling)
        $this->getEval("this.browserbot.findElement('id=" . $specFieldsetId
                           . "').scrollTop = " . $destinationOffsetTop);
    }

    /**
     * Moves the specified element (with type = $elementType and name = $elementName)<br>
     * over the specified JS tree (with type = $blockType and name = $blockName)<br>
     * to position = $moveToPosition
     *
     * @param string $elementType Type of the element to move
     * @param string $elementName Name of the element to move
     * @param string $blockType Type of the block that contains JS tree
     * @param string $blockName Name of the block that contains JS tree
     * @param integer $moveToPosition Index of the position where element should be after moving (default = 1)
     */
    public function moveElementOverTree($elementType, $elementName, $blockType, $blockName, $moveToPosition = 1)
    {
        // Getting XPath of the element to move
        $specElementXpath = $this->_getControlXpath($elementType, $elementName);
        // Getting @ID of the element to move
        $specElementId = $this->getAttribute($specElementXpath . "/@id");
        // Getting XPath of the block what is a JS tree
        $specFieldsetXpath = $this->_getControlXpath($blockType, $blockName);
        // Getting @ID of the block what is a JS tree
        $specFieldsetId = $this->getAttribute($specFieldsetXpath . "/@id");
        // Getting offset position of the element to move
        $destinationOffsetTop = $this->getEval("this.browserbot.findElement('id=" . $specElementId . "').offsetTop");
        // Storing of current height of the block with JS tree
        $tmpBlockHeight = (integer)$this->getEval("this.browserbot.findElement('id="
                                                      . $specFieldsetId . "').style.height");
        // If element to move situated abroad of the current height, it will be increased
        if ($destinationOffsetTop >= $tmpBlockHeight) {
            $destinationOffsetTop = $destinationOffsetTop + 50;
            $this->getEval("this.browserbot.findElement('id=" . $specFieldsetId
                               . "').style.height='" . $destinationOffsetTop . "px'");
        }
        $this->clickAt($specElementXpath, '1,1');
        $blockTo = $specFieldsetXpath . '//li[' . $moveToPosition . ']//a//span';
        $this->mouseDownAt($specElementXpath, '1,1');
        $this->mouseMoveAt($blockTo, '1,1');
        $this->mouseUpAt($blockTo, '1,1');
        $this->clickAt($specElementXpath, '1,1');
    }

    /**
     * Searches for the specified data in specific the grid and opens the found item.
     *
     * @param array $data Array of data to look up
     * @param bool $willChangePage Triggers page reloading. If clicking the control doesn't result<br>
     * in page reloading, should be false (by default = true).
     * @param string|null $fieldSetName Fieldset name that contains the grid (by default = null)
     */
    public function searchAndOpen(array $data, $willChangePage = true, $fieldSetName = null)
    {
        $this->_prepareDataForSearch($data);
        $xpathTR = $this->search($data, $fieldSetName);

        if ($xpathTR) {
            if ($willChangePage) {
                $itemId = $this->defineIdFromTitle($xpathTR);
                $this->addParameter('id', $itemId);
                $this->click($xpathTR . "/td[contains(text(),'" . $data[array_rand($data)] . "')]");
                $this->waitForPageToLoad($this->_browserTimeoutPeriod);
                $this->validatePage();
            } else {
                $this->click($xpathTR . "/td[contains(text(),'" . $data[array_rand($data)] . "')]");
                $this->waitForAjax($this->_browserTimeoutPeriod);
            }
        } else {
            $this->fail('Can\'t find item in grid for data: ' . print_r($data, true));
        }
    }

    /**
     * Searches for the specified data in specific the grid and selects the found item.
     *
     * @param array $data Array of data to look up
     * @param string|null $fieldSetName Fieldset name that contains the grid (by default = null)
     */
    public function searchAndChoose(array $data, $fieldSetName = null)
    {
        $this->_prepareDataForSearch($data);
        $xpathTR = $this->search($data, $fieldSetName);
        if ($xpathTR) {
            $xpathTR .= "//input[contains(@class,'checkbox') or contains(@class,'radio')][not(@disabled)]";
            if ($this->getValue($xpathTR) == 'off') {
                $this->click($xpathTR);
            }
        } else {
            $this->fail('Cant\'t find item in grid for data: ' . print_r($data, true));
        }
    }

    /**
     * Prepare data array to search in grid
     *
     * @param array $data Array of data to look up
     * @param array $checkFields
     *
     * @return array
     */
    protected function _prepareDataForSearch(array &$data, array $checkFields = array('dropdown' => 'website'))
    {
        $data = $this->arrayEmptyClear($data);
        foreach ($checkFields as $fieldType => $fieldName) {
            if (array_key_exists($fieldName, $data) && !$this->controlIsPresent($fieldType, $fieldName)) {
                unset($data[$fieldName]);
            }
        }

        return $data;
    }

    /**
     * Searches the specified data in the specific grid. Returns null or XPath of the found data.
     *
     * @param array $data Array of data to look up.
     * @param string|null $fieldSetName Fieldset name that contains the grid (by default = null)
     *
     * @return string|null
     */
    public function search(array $data, $fieldSetName = null)
    {
        $waitAjax = true;
        $xpath = '';
        $xpathContainer = null;
        if ($fieldSetName) {
            $xpathContainer = $this->_findUimapElement('fieldset', $fieldSetName);
            $xpath = $xpathContainer->getXpath($this->_paramsHelper);
        }
        $resetXpath = $this->_getControlXpath('button', 'reset_filter', $xpathContainer);
        $jsName = $this->getAttribute($resetXpath . '@onclick');
        $jsName = preg_replace('/\.[\D]+\(\)/', '', $jsName);
        $scriptXpath = "//script[contains(text(),\"$jsName.useAjax = ''\")]";
        if ($this->isElementPresent($scriptXpath)) {
            $waitAjax = false;
        }
        $this->click($resetXpath);
        if ($waitAjax) {
            $this->pleaseWait();
        } else {
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
        }
        //Forming xpath that contains string 'Total $number records found' where $number - number of items in table
        $totalCount = intval($this->getText($xpath . self::$qtyElementsInTable));
        $xpathPager = $xpath . self::$qtyElementsInTable . "[not(text()='" . $totalCount . "')]";

        $xpathTR = $this->formSearchXpath($data);

        if (!$this->isElementPresent($xpath . $xpathTR) && $totalCount > 20) {
            // Fill in search form and click 'Search' button
            $this->fillForm($data);
            $this->clickButton('search', false);
            $this->waitForElement($xpathPager);
        }

        if ($this->isElementPresent($xpath . $xpathTR)) {
            return $xpath . $xpathTR;
        }
        return null;
    }


    /**
     * Forming xpath that contains the data to look up
     *
     * @param array $data Array of data to look up
     *
     * @return string
     */
    public function formSearchXpath(array $data)
    {
        $xpathTR = "//table[@class='data']//tr";
        foreach ($data as $key => $value) {
            if (!preg_match('/_from/', $key) and !preg_match('/_to/', $key) and !is_array($value)) {
                if (strpos($value, "'")) {
                    $value = "concat('" . str_replace('\'', "',\"'\",'", $value) . "')";
                } else {
                    $value = "'" . $value . "'";
                }
                $xpathTR .= "[td[contains(text(),$value)]]";
            }
        }
        return $xpathTR;
    }

    /**
     * Fills any form with the provided data. Specific Tab can be filled only if $tabId is provided.
     *
     * @param array|string $data Array of data to fill or datasource name
     * @param string $tabId Tab ID from UIMap (by default = '')
     *
     * @throws OutOfRangeException|PHPUnit_Framework_Exception
     */
    public function fillForm($data, $tabId = '')
    {
        if (is_string($data)) {
            $data = $this->loadData($data);
        }

        $formData = $this->getCurrentUimapPage()->getMainForm();
        if ($tabId && $formData->getTab($tabId)) {
            $fieldsets = $formData->getTab($tabId)->getAllFieldsets($this->_paramsHelper);
        } else {
            $fieldsets = $formData->getAllFieldsets($this->_paramsHelper);
        }
        // if we have got empty UIMap but not empty dataset
        if (empty($fieldsets)) {
            throw new OutOfRangeException("Can't find main form in UIMap array for page '"
                . $this->getCurrentPage() . "', area['" . $this->_configHelper->getArea() . "']");
        }

        $formDataMap = $this->_getFormDataMap($fieldsets, $data);

        if ($tabId) {
            $this->openTab($tabId);
        }

        try {
            foreach ($formDataMap as $formFieldName => $formField) {
                switch ($formField['type']) {
                    case self::FIELD_TYPE_INPUT:
                        $this->_fillFormField($formField);
                        break;
                    case self::FIELD_TYPE_CHECKBOX:
                        $this->_fillFormCheckbox($formField);
                        break;
                    case self::FIELD_TYPE_DROPDOWN:
                        $this->_fillFormDropdown($formField);
                        break;
                    case self::FIELD_TYPE_RADIOBUTTON:
                        $this->_fillFormRadiobutton($formField);
                        break;
                    case self::FIELD_TYPE_MULTISELECT:
                        $this->_fillFormMultiselect($formField);
                        break;
                    default:
                        throw new PHPUnit_Framework_Exception('Unsupported field type');
                }
            }
        } catch (PHPUnit_Framework_Exception $e) {
            $errorMessage = isset($formFieldName)
                ? 'Problem with field \'' . $formFieldName . '\': ' . $e->getMessage()
                : $e->getMessage();
            $this->fail($errorMessage);
        }
    }

    /**
     * Verifies values on the opened form
     *
     * @param array|string $data Array of data to verify or datasource name
     * @param string $tabId Defines a specific Tab on the page that contains the form to verify (by default = '')
     * @param array $skipElements Array of elements that will be skipped during verification <br>
     * (default = array('password'))
     *
     * @return bool
     * @throws InvalidArgumentException|OutOfRangeException
     */
    public function verifyForm($data, $tabId = '', $skipElements = array('password'))
    {
        if (is_string($data)) {
            $data = $this->loadData($data);
        }

        $formData = $this->getCurrentUimapPage()->getMainForm();
        if ($tabId && $formData->getTab($tabId)) {
            $fieldsets = $formData->getTab($tabId)->getAllFieldsets($this->_paramsHelper);
        } else {
            $fieldsets = $formData->getAllFieldsets($this->_paramsHelper);
        }
        // if we have got empty UIMap but not empty dataset
        if (empty($fieldsets)) {
            throw new OutOfRangeException("Can't find main form in UIMap array for page '"
                . $this->getCurrentPage() . "', area['" . $this->_configHelper->getArea() . "']");
        }

        if ($tabId) {
            $this->openTab($tabId);
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $skipElements) || $value === '%noValue%') {
                unset($data[$key]);
            }
        }
        $formDataMap = $this->_getFormDataMap($fieldsets, $data);

        $resultFlag = true;
        foreach ($formDataMap as $formFieldName => $formField) {
            switch ($formField['type']) {
                case self::FIELD_TYPE_INPUT:
                    if ($this->isElementPresent($formField['path'])) {
                        $val = $this->getValue($formField['path']);
                        if ($val != $formField['value']) {
                            $this->addVerificationMessage($formFieldName
                                                              . ": The stored value is not equal to specified: ("
                                                              . $formField['value'] . "' != '" . $val . "')");
                            $resultFlag = false;
                        }
                    } else {
                        $this->addVerificationMessage('Can not find field (xpath:' . $formField['path'] . ')');
                        $resultFlag = false;
                    }
                    break;
                case self::FIELD_TYPE_CHECKBOX:
                case self::FIELD_TYPE_RADIOBUTTON:
                    if ($this->isElementPresent($formField['path'])) {
                        $isChecked = $this->isChecked($formField['path']);
                        $expectedVal = strtolower($formField['value']);
                        if (($isChecked && $expectedVal != 'yes')
                            || (!$isChecked && !($expectedVal == 'no' || $expectedVal == ''))
                        ) {
                            $printVal = ($isChecked) ? 'yes' : 'no';
                            $this->addVerificationMessage($formFieldName
                                                              . ": The stored value is not equal to specified: ("
                                                              . $expectedVal . "' != '" . $printVal . "')");
                            $resultFlag = false;
                        }
                    } else {
                        $this->addVerificationMessage('Can not find field (xpath:' . $formField['path'] . ')');
                        $resultFlag = false;
                    }
                    break;
                case self::FIELD_TYPE_DROPDOWN:
                    if ($this->isElementPresent($formField['path'])) {
                        $label = $this->getSelectedLabel($formField['path']);
                        if ($formField['value'] != $label) {
                            $this->addVerificationMessage($formFieldName
                                                              . ": The stored value is not equal to specified: ("
                                                              . $formField['value'] . "' != '" . $label . "')");
                            $resultFlag = false;
                        }
                    } else {
                        $this->addVerificationMessage('Can not find field (xpath:' . $formField['path'] . ')');
                        $resultFlag = false;
                    }
                    break;
                case self::FIELD_TYPE_MULTISELECT:
                    if ($this->isElementPresent($formField['path'])) {
                        $selectedLabels = $this->getSelectedLabels($formField['path']);
                        $selectedLabels = array_map('trim', $selectedLabels, array(chr(0xC2) . chr(0xA0)));
                        $expectedLabels = explode(',', $formField['value']);
                        $expectedLabels = array_map('trim', $expectedLabels);
                        foreach ($expectedLabels as $value) {
                            if (!in_array($value, $selectedLabels)) {
                                $this->addVerificationMessage($formFieldName . ": The value '" . $value
                                                                  . "' is not selected. (Selected values are: '"
                                                                  . implode(', ', $selectedLabels) . "')");
                                $resultFlag = false;
                            }
                        }
                        if (count($selectedLabels) != count($expectedLabels)) {
                            $this->addVerificationMessage("Amounts of the expected options are not equal to selected: ('"
                                                              . $formField['value'] . "' != '"
                                                              . implode(', ', $selectedLabels) . "')");
                            $resultFlag = false;
                        }
                    } else {
                        $this->addVerificationMessage('Can not find field (xpath:' . $formField['path'] . ')');
                        $resultFlag = false;
                    }
                    break;
                default:
                    $this->addVerificationMessage('Unsupported field type');
                    $resultFlag = false;
            }
        }

        return $resultFlag;
    }

    /**
     * Fills a text field of ('field' | 'input') control type by typing a value.
     *
     * @param array $fieldData Array of a 'path' to control and 'value' to type
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function _fillFormField($fieldData)
    {
        if ($this->waitForElement($fieldData['path'], 5) && $this->isEditable($fieldData['path'])) {
            $this->type($fieldData['path'], $fieldData['value']);
            $this->waitForAjax();
        } else {
            throw new PHPUnit_Framework_Exception("Can't fill in the field: {$fieldData['path']}");
        }
    }

    /**
     * Fills 'multiselect' control by selecting the specified values.
     *
     * @param array $fieldData Array of a 'path' to control and 'value' to select
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function _fillFormMultiselect($fieldData)
    {
        $valuesArray = array();
        if ($this->waitForElement($fieldData['path'], 5) && $this->isEditable($fieldData['path'])) {
            $this->removeAllSelections($fieldData['path']);
            if (strtolower($fieldData['value']) == 'all') {
                $count = $this->getXpathCount($fieldData['path'] . '//option');
                for ($i = 1; $i <= $count; $i++) {
                    $valuesArray[] = $this->getText($fieldData['path'] . "//option[$i]");
                }
            } else {
                $valuesArray = explode(',', $fieldData['value']);
                $valuesArray = array_map('trim', $valuesArray);
            }
            foreach ($valuesArray as $value) {
                if ($value != null) {
                    if ($this->isElementPresent($fieldData['path'] . "//option[text()='" . $value . "']")) {
                        $this->addSelection($fieldData['path'], 'label=' . $value);
                    } else {
                        $this->addSelection($fieldData['path'], 'regexp:' . preg_quote($value));
                    }
                }
            }
        } else {
            throw new PHPUnit_Framework_Exception("Can't fill in the multiselect field: {$fieldData['path']}");
        }
    }

    /**
     * Fills the 'dropdown' control by selecting the specified value.
     *
     * @param array $fieldData Array of a 'path' to control and 'value' to select
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function _fillFormDropdown($fieldData)
    {
        $fieldXpath = $fieldData['path'];
        if ($this->waitForElement($fieldData['path'], 5) && $this->isEditable($fieldXpath)) {
            if ($this->getSelectedValue($fieldXpath) != $fieldData['value']) {
                if ($this->isElementPresent($fieldXpath . "//option[text()='" . $fieldData['value'] . "']")) {
                    $this->select($fieldXpath, 'label=' . $fieldData['value']);
                } else {
                    $this->select($fieldXpath, 'regexp:' . preg_quote($fieldData['value']));
                }
                $this->waitForAjax();
            }
        } else {
            throw new PHPUnit_Framework_Exception("Can't fill in the dropdown field: {$fieldData['path']}");
        }
    }

    /**
     * Fills 'checkbox' control by selecting/unselecting it based on the specified value.
     *
     * @param array $fieldData Array of a 'path' to control and 'value' to select. Value can be 'Yes' or 'No'.
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function _fillFormCheckbox($fieldData)
    {
        if ($this->waitForElement($fieldData['path'], 5) && $this->isEditable($fieldData['path'])) {
            if (strtolower($fieldData['value']) == 'yes') {
                if (($this->getValue($fieldData['path']) == 'off') || ($this->getValue($fieldData['path']) == '0')) {
                    $this->click($fieldData['path']);
                    $this->waitForAjax();
                }
            } elseif (strtolower($fieldData['value']) == 'no') {
                if (($this->getValue($fieldData['path']) == 'on') || ($this->getValue($fieldData['path']) == '1')) {
                    $this->click($fieldData['path']);
                    $this->waitForAjax();
                }
            }
        } else {
            throw new PHPUnit_Framework_Exception("Can't fill in the checkbox field: {$fieldData['path']}");
        }
    }

    /**
     * Fills the 'radiobutton' control by selecting the specified value.
     *
     * @param array $fieldData Array of a 'path' to control and 'value' to select.<br>
     * Value should be 'Yes' to select the radiobutton.
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function _fillFormRadiobutton($fieldData)
    {
        if ($this->waitForElement($fieldData['path'], 5) && $this->isEditable($fieldData['path'])) {
            if (strtolower($fieldData['value']) == 'yes') {
                $this->click($fieldData['path']);
                $this->waitForAjax();
            } else {
                $this->uncheck($fieldData['path']);
                $this->waitForAjax();
            }
        } else {
            throw new PHPUnit_Framework_Exception("Can't fill in the radiobutton field: {$fieldData['path']}");
        }
    }

    ################################################################################
    #                                                                              #
    #                             Magento helper methods                           #
    #                                                                              #
    ################################################################################
    /**
     * Waits for "Please wait" animated gif to appear and disappear.
     *
     * @param integer $waitAppear Timeout in seconds to wait for the loader to appear (by default = 10)
     * @param integer $waitDisappear Timeout in seconds to wait for the loader to disappear (by default = 30)
     *
     * @return Mage_Selenium_TestCase
     */
    public function pleaseWait($waitAppear = 10, $waitDisappear = 30)
    {
        for ($second = 0; $second < $waitAppear; $second++) {
            if ($this->isElementPresent(Mage_Selenium_TestCase::$xpathLoadingHolder)) {
                break;
            }
            sleep(1);
        }

        for ($second = 0; $second < $waitDisappear; $second++) {
            if (!$this->isElementPresent(Mage_Selenium_TestCase::$xpathLoadingHolder)) {
                break;
            }
            sleep(1);
        }

        return $this;
    }

    /**
     * Logs in as a default admin user on back-end
     * @return Mage_Selenium_TestCase
     */
    public function loginAdminUser()
    {
        $this->admin('log_in_to_admin', false);
        $loginData = array(
            'user_name' => $this->_configHelper->getDefaultLogin(),
            'password'  => $this->_configHelper->getDefaultPassword()
        );
        $currentPage = $this->_findCurrentPageFromUrl($this->getLocation());
        if ($currentPage != $this->_firstPageAfterAdminLogin) {
            $this->validatePage('log_in_to_admin');
            $this->fillForm($loginData);
            $this->clickButton('login', false);
            $this->waitForElement(array(self::$xpathAdminLogo,
                                       self::$xpathErrorMessage,
                                       self::$xpathValidationMessage));
            if ($this->_findCurrentPageFromUrl($this->getLocation()) != $this->_firstPageAfterAdminLogin) {
                $this->fail('Admin was not logged in');
            }
            if ($this->isElementPresent(self::$xpathGoToNotifications)
                && $this->waitForElement(self::$xpathIncomingMessageClose, 5)
            ) {
                $this->click(self::$xpathIncomingMessageClose);
            }
            $this->validatePage($this->_firstPageAfterAdminLogin);
        }

        return $this;
    }

    /**
     * Logs out from back-end
     * @return Mage_Selenium_TestCase
     */
    public function logoutAdminUser()
    {
        if ($this->isElementPresent(self::$xpathLogOutAdmin)) {
            $this->click(self::$xpathLogOutAdmin);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        }
        $this->validatePage('log_in_to_admin');

        return $this;
    }

    /**
     * Clears invalided cache in Admin
     */
    public function clearInvalidedCache()
    {
        if ($this->isElementPresent(self::$xpathCacheInvalidated)) {
            $this->clickAndWait(self::$xpathCacheInvalidated);
            $this->validatePage('cache_storage_management');

            $invalided = array('cache_disabled', 'cache_invalided');
            foreach ($invalided as $value) {
                $xpath = $this->_getControlXpath('pageelement', $value);
                $qty = $this->getXpathCount($xpath);
                for ($i = 1; $i < $qty + 1; $i++) {
                    $fillData = array('path'  => $xpath . '[' . $i . ']//input',
                                      'value' => 'Yes');
                    $this->_fillFormCheckbox($fillData);
                }
            }
            $this->fillForm(array('cache_action' => 'Refresh'));

            $selectedItems = $this->getText($this->_getControlXpath('pageelement', 'selected_items'));
            $this->addParameter('qtySelected', $selectedItems);

            $this->clickButton('submit', false);
            $alert = $this->isAlertPresent();
            if ($alert) {
                $text = $this->getAlert();
                $this->fail($text);
            }
            $this->waitForNewPage();
            $this->validatePage('cache_storage_management');
        }
    }

    /**
     * Reindex indexes that are marked as 'reindex required' or 'update required'.
     */
    public function reindexInvalidedData()
    {
        if ($this->isElementPresent(self::$xpathIndexesInvalidated)) {
            $this->clickAndWait(self::$xpathIndexesInvalidated);
            $this->validatePage('index_management');

            $invalided = array('reindex_required', 'update_required');
            foreach ($invalided as $value) {
                $xpath = $this->_getControlXpath('pageelement', $value);
                while ($this->isElementPresent($xpath)) {
                    $this->click($xpath . "//a[text()='Reindex Data']");
                    $this->waitForNewPage();
                    $this->validatePage('index_management');
                }
            }
        }
    }

    /**
     * @throws PHPUnit_Framework_Exception
     */
    public function waitForNewPage()
    {
        $notLoaded = true;
        $retries = 0;
        while ($notLoaded) {
            try {
                $retries++;
                $this->waitForPageToLoad($this->_browserTimeoutPeriod);
                $notLoaded = false;
            } catch (RuntimeException $e) {
                if ($retries == 10) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Performs LogOut customer on front-end
     * @return Mage_Selenium_TestCase
     */
    public function logoutCustomer()
    {
        $this->frontend('home');
        $xpath = "//a[@title='Log Out']";
        if ($this->isElementPresent($xpath)) {
            $this->clickAndWait($xpath, $this->_browserTimeoutPeriod);
            $this->frontend('home');
        }

        return $this;
    }

    /**
     * Selects StoreView on Frontend
     *
     * @param string $storeViewName
     */
    public function selectFrontStoreView($storeViewName = 'Default Store View')
    {
        $xpath = "//select[@id='select-language']";
        $toSelect = $xpath . '//option[normalize-space(text())="' . $storeViewName . '"]';
        $isSelected = $toSelect . '[@selected]';
        if (!$this->isElementPresent($isSelected)) {
            $this->select($xpath, $storeViewName);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        }
        $this->assertElementPresent($isSelected, '\'' . $storeViewName . '\' store view not selected');
    }

    ################################################################################
    #                                                                              #
    #       Should be removed when CodeCoverage work for PHPUnit3.6                #
    #                                                                              #
    ################################################################################
    /**
     * @return array
     * @throws Exception
     */
    protected function getCodeCoverage()
    {
        if (!empty($this->coverageScriptUrl)) {
            $url = sprintf(
                '%s?PHPUNIT_SELENIUM_TEST_ID=%s',
                $this->coverageScriptUrl,
                //$this->testId
                $_COOKIE['PHPUNIT_SELENIUM_TEST_ID']
            );

            $buffer = @file_get_contents($url);

            if ($buffer !== FALSE) {
                $coverageData = unserialize($buffer);
                if (is_array($coverageData)) {
                    return $this->matchLocalAndRemotePaths($coverageData);
                } else {
                    throw new Exception('Empty or invalid code coverage data received from url "' . $url . '"');
                }
            }
        }

        return array();
    }

    ################################################################################
    #                                                                              #
    #       Should be removed when onNotSuccessfulTest is fixed                    #
    #                                                                              #
    ################################################################################
    /**
     * @param Exception $e
     *
     * @throws Exception|RuntimeException
     */
    protected function onNotSuccessfulTest(Exception $e)
    {
        if ($this->_saveHtmlPageOnFailure) {
            $this->saveHtmlPage();
        }
        if ($this->captureScreenshotOnFailure) {
            $this->takeScreenshot();
        }
        throw $e;
    }
}
