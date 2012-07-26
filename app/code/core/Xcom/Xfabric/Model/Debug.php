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
 * @category    Xcom
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Xfabric_Model_Debug extends Mage_Core_Model_Abstract implements ArrayAccess
{
    /**
     *
     * Stack array
     *
     * @var array
     */
    protected static $_stack        = array();

    /**
     *
     * Array of visitors.
     *
     * Visitor can be a kind of small util which is watch over some part of environment and update node data with it
     *
     * @var array
     */
    protected static $_visitors     = array();

    /**
     *
     * Current stack pointer
     * @var int
     */
    private static $_stackPointer   = 0;

    /**
     * @var string
     */
    const ROOT_CALL_METHOD = 'root_call';

    /**
     * We can use Debug model as container for debug event.
     * Within this method we also set default visitors list.
     *
     * @see lib/Varien/Varien_Object#_construct()
     */
    public function _construct()
    {
        $this->_init('xcom_xfabric/debug');

        self::$_visitors = array(
            Mage::getSingleton('xcom_xfabric/debug_visitor_profile'),
            Mage::getSingleton('xcom_xfabric/debug_visitor_save')
        );
    }

    /**
     * Debug service/method call procedure.
     * This method should be called before event
     *
     * @param  string $method   The service/method name to be debugged
     * @param  string $topic   The message topic
     * @param  string $headers   The headers of message
     * @param  string $body   The message body
     * @return void
     */
    public function start($method, $topic, $headers, $body)
    {
        if (!$this->getId()) {
            $this->setName($method);
            $this->setStartedAt(now());
            $this->setStartedMicrotime(microtime(1));
            $this->save();
        }

        if (count(self::$_stack) == 0 || self::$_stackPointer == 1) {
            self::$_stackPointer = 0;
            $node = self::_createNode();
            $node->setMethod($method);
            $node->setTopic($topic);
            $node->setHeaders($headers);
            $node->setBody($body);
            $node->setDebugId($this->getId());
            self::$_stack[self::$_stackPointer++] = $node;
            foreach (self::$_visitors as $visitor) {
                $visitor->start($node);
            }
        }

        $node = self::_createNode();
        $node->setMethod($method);
        $node->setDebugId($this->getId());

        $parent = self::$_stack[self::$_stackPointer - 1];
        $node->setParentId($parent->getNodeId());

        self::$_stack[self::$_stackPointer++] = $node;

        foreach (self::$_visitors as $visitor) {
            $visitor->start($node);
        }

        return $this;
    }

    /**
     * Debug service/method call procedure.
     * This method should be called after event
     *
     * @param  string $method   The service/method name to be debugged
     * @param  string $topic   The message topic
     * @param  string $headers   The headers of message
     * @param  string $body   The message body
     * @return void
     */
    public function stop($method, $topic, $headers, $body)
    {

        $pointer = self::$_stackPointer - 1;
        if ($pointer == 0) {
            /** Back to root **/
            return false;
        }
        $prevPointer = $pointer - 1;

        if (!isset(self::$_stack[$prevPointer])) {
            return false;
        }
        $parent = self::$_stack[$prevPointer];

        if (!isset(self::$_stack[$pointer])) {
            return false;
        }
        self::$_stack[$pointer]->setMethod($method);
        self::$_stack[$pointer]->setTopic($topic);
        self::$_stack[$pointer]->setHeaders($headers);
        self::$_stack[$pointer]->setBody($body);

        $parent->addChild(self::$_stack[$pointer]);

        self::$_stackPointer--;

        $count = $pointer;
        while ($count >= 0) {
            $node = self::$_stack[$count--];
            foreach (self::$_visitors as $visitor) {
                $visitor->stop($node);
            }
        }

        if ($this->getId()) {
            $this->setCompletedAt(now());
            $this->setCompletedMicrotime(microtime(1));
            $this->save();
        }

//        //Start new debug session if we fall to root_call node
//        if (isset(self::$_stack[self::$_stackPointer - 1])
//            && self::$_stack[self::$_stackPointer - 1]->getMethod() == self::ROOT_CALL_METHOD
//        ) {
            $this->setId(NULL);
//        }

        return $this;
    }

    protected static function _createNode()
    {
        return new Xcom_Xfabric_Model_Debug_Node();
    }

    public function getRootNode()
    {
        return self::$_stack[0];
    }

    public function getPointer()
    {
        return self::$_stackPointer;
    }

    public function getNodeByPointer($pointer)
    {
        if (isset(self::$_stack[$pointer])) {
            return self::$_stack[$pointer];
        } else {
            return null;
        }
    }

    /// Implements ArrayAccess Interface

    public function offsetExists ($offset)
    {
        return isset(self::$_stack[$offset]);
    }

    public function offsetUnset ($offset)
    {
        Mage::throwException(Mage::helper('xcom_xfabric')->__("Manual data unsetting from stack is denied"));

        return false;
    }

    public function offsetGet($offset)
    {
        return isset (self::$_stack[$offset]) ? self::$_stack[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        Mage::throwException(Mage::helper('xcom_xfabric')->__("Manual data setting from stack is denied"));

        return false;
    }
}
