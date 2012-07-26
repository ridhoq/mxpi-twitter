<?php
$tool = new Tool($argv);
$tool->run();

class Tool
{
    protected $_arguments;
    protected $_replacements = array();

    protected $_license_xml = <<<END
<!--
@LICENSE@
-->
END;

    protected $_license = <<<END
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
 * @category  Integration test
 * @package   @FULL_MODULE_NAME@
 * @copyright Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
END;

    protected $_test_modulename_xml = <<<END
<?xml version="1.0"?>
@LICENSE_XML@
<config>
    <modules>
        <@FULL_MODULE_NAME@>
            <active>true</active>
            <codePool>core</codePool>
        </@FULL_MODULE_NAME@>
    </modules>
</config>
END;

    protected $_config_xml = <<<END
<?xml version="1.0"?>
@LICENSE_XML@
<config>
    <modules>
        <@FULL_MODULE_NAME@>
            <version>0.1.0</version>
        </@FULL_MODULE_NAME@>
    </modules>
    <global>
        <models>
            <@FULL_MODULE_NAME_lc@>
                <class>@FULL_MODULE_NAME@_Model</class>
                <resourceModel>@FULL_MODULE_NAME_lc@_resource</resourceModel>
            </@FULL_MODULE_NAME_lc@>
            <@FULL_MODULE_NAME_lc@_resource>
                <class>@FULL_MODULE_NAME@_Model_Resource</class>
                <entities>
                    <job>
                        <table>@FULL_MODULE_NAME_lc@_job</table>
                    </job>
                </entities>
            </@FULL_MODULE_NAME_lc@_resource>
        </models>
        <resources>
            <@FULL_MODULE_NAME_lc@_setup>
                <setup>
                    <module>@FULL_MODULE_NAME@</module>
                </setup>
            </@FULL_MODULE_NAME_lc@_setup>
        </resources>
        <helpers>
            <@FULL_MODULE_NAME_lc@>
                <class>@FULL_MODULE_NAME@_Helper</class>
            </@FULL_MODULE_NAME_lc@>
        </helpers>
        <blocks>
            <@FULL_MODULE_NAME_lc@>
                <class>@FULL_MODULE_NAME@_Block</class>
            </@FULL_MODULE_NAME_lc@>
        </blocks>
    </global>
    <frontend>
        <routers>
            <@MODULE_NAME_lc@>
                <use>standard</use>
                <args>
                    <module>@FULL_MODULE_NAME@</module>
                    <frontName>@MODULE_NAME_lc@</frontName>
                </args>
            </@MODULE_NAME_lc@>
        </routers>
    </frontend>
</config>
END;

    protected $_class_model = <<<END
<?php
@LICENSE@
class @FULL_MODULE_NAME@_Model_@_addClass_NAME@ extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        \$this->_init('@MODULE_NAME_lc@/@_addClass_NAME_lc@');
    }
}
END;

    protected $_class_controller = <<<END
<?php
@LICENSE@
class @FULL_MODULE_NAME@_@_addClass_NAME@Controller extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    }
}
END;

    protected $_class_helper = <<<END
<?php
@LICENSE@
class @FULL_MODULE_NAME@_Helper_@_addClass_NAME@
{
}
END;

    protected $_class_block = <<<END
<?php
@LICENSE@
class @FULL_MODULE_NAME@_Block_@_addClass_NAME@ extends Mage_Core_Block_Template
{
}
END;


    public function __construct($arguments)
    {
        if (!is_array($arguments) || empty($arguments[1])) {
            $this->_printUsage();
        }

        $this->_arguments = $arguments;
        $this->_addToken('@LICENSE@', $this->_license);
        $this->_addToken('@LICENSE_XML@', $this->_license_xml);
    }

    public function run()
    {
        try {
            switch ($this->_arguments[1]) {
                case 'add-test-module':
                    $this->_addTestModule();
                    break;
                default:
                    $this->_printUsage();
            }
        } catch (ToolException $e) {
            echo sprintf("ERROR: %s\n\n", $e->getMessage());
            $this->_printUsage();
        }
    }

    protected function _addToken($token, $replacement)
    {
        $this->_replacements[$token] = $replacement;
        return $this;
    }

    protected function _replaceTokens($sourceStr)
    {
        $tokens = array_keys($this->_replacements);

        do {
            $output = str_replace($tokens, array_values($this->_replacements), $sourceStr);
            $tokensLast = false;

            if (preg_match('/@(\w+)@/', $output, $matches)) {
                $tokensLast = in_array($matches[1], $tokens);
            }
        } while ($tokensLast);

        return $output;
    }

    protected function _addTestModule()
    {
        if (empty($this->_arguments[2])) {
            throw new ToolException('module name not defined');
        }

        $moduleName = ucwords($this->_arguments[2]);
        $fullModuleName = sprintf('Test_%s', $moduleName);

        if (strtolower(substr($moduleName, 0, 5)) == 'test_') {
            throw new ToolException('Test_ prefix is added automatically');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $moduleName)) {
            throw new ToolException('module name should conform with this regex pattern: [a-zA-Z0-9]+');
        }

        $this->_addToken('@FULL_MODULE_NAME@', $fullModuleName);
        $this->_addToken('@MODULE_NAME@', $moduleName);
        $this->_addToken('@FULL_MODULE_NAME_lc@', strtolower($fullModuleName));
        $this->_addToken('@MODULE_NAME_lc@', strtolower($moduleName));
        $this->_createFile(sprintf('integration/etc/modules/%s.xml', $fullModuleName), $this->_test_modulename_xml);
        $this->_createFile(sprintf('integration/modules/Test/%s/etc/config.xml', $moduleName), $this->_config_xml);

        for ($i = 3, $cnt = count($this->_arguments); $i < $cnt; $i++) {
            $this->_addClass($this->_arguments[$i]);
        }

        echo "Module {$fullModuleName} created\n";
        echo "Don't forget to clean cache (rm -rf var/cache)\n";
    }

    protected function _addClass($argument)
    {
        $paths = array(
            'model'      => 'Model/@_addClass_PATH@.php',
            'controller' => 'controllers/@_addClass_PATH@Controller.php',
            'helper'     => 'Helper/@_addClass_PATH@.php',
            'block'      => 'Block/@_addClass_PATH@.php',
        );

        $template = array(
            'model'      => $this->_class_model,
            'controller' => $this->_class_controller,
            'helper'     => $this->_class_helper,
            'block'      => $this->_class_block,
        );

        if (preg_match('/\+(model|controller|block)=(.+)/i', $argument, $matches) || preg_match('/\+(helper)(?:=(.+))?/i', $argument, $matches)) {
            $classType = $matches[1];
            if (strtolower($classType) == 'helper' && empty($matches[2])) {
                $path = 'data';
            } else {
                $path = strtolower($matches[2]);
            }

            $name = str_replace('/', '_', $path);
            $this->_addToken('@_addClass_PATH@', $path);
            $this->_addToken('@_addClass_NAME@', ucfirst($name));
            $this->_addToken('@_addClass_NAME_lc@', $name);
            $filePath = $this->_replaceTokens('integration/modules/Test/@MODULE_NAME@/' . $paths[$classType]);
            $this->_createFile($filePath, $template[$classType]);
            echo "Created {$classType}: {$filePath}\n";
        } else {
            echo "WARNING! Invalid directive: {$argument} - skipped\n";
        }

        return $this;
    }

    protected function _createFile($fileName, $contents)
    {
        $path = dirname($fileName);

        if (!is_dir($path)) {
            mkdir($path, 0655, true);
        }

        if (file_exists($fileName)) {
            echo "WARNING! File {$fileName} already exists - skipping\n";
        } else {
            file_put_contents($fileName, $this->_replaceTokens($contents));
        }

        return $this;
    }

    protected function _printUsage()
    {
        echo "USAGE: php tool.php add-test-module <module_name> [+model=name] [+controller=name] [+helper=name] [+block=name] ...\n";
        echo "WHERE:\n";
        echo "module_name      - name of the module, prefix Test_ automatically prepended\n";
        echo "+model=name      - model Test_ModuleName_Model_Name will be automatically created\n";
        echo "+controller=name - controller Test_ModuleName_NameController will be automatically created\n";
        echo "+helper[=name]   - helper Test_ModuleName_Helper_Name will be automatically created, if name is not specified 'Data' used\n";
        echo "+block=name      - block Test_ModuleName_Block_Name will be automatically created\n";
        exit(1);
    }
}

class ToolException extends Exception {}
