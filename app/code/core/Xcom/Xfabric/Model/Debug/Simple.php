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
class Xcom_Xfabric_Model_Debug_Simple extends Mage_Core_Model_Abstract implements ArrayAccess
{
    /**
     *
     * Stack parent Node
     *
     * @var array
     */
    protected $_parentNode        = null;

    /**
     *
     * Stack child Node
     *
     * @var array
     */
    protected $_dataNode        = null;

    /**
     *
     * Array of visitors.
     *
     * Visitor can be a kind of small util which is watch over some part of environemnt and update node data with it
     *
     * @var array
     */
    protected $_visitors     = array();

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

        $this->_visitors = array(
            Mage::getSingleton('xcom_xfabric/debug_visitor_profile'),
            Mage::getSingleton('xcom_xfabric/debug_visitor_save')
        );
    }

    /**
     * Debug service/method call procedure.
     * This method should be called before event
     *
     * @param  string $method   The service/method name to be debugged
     * @param  array  $data     The data could be any mixed array
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

        $parentId = 0;
        if (count($this->_stack) == 0) {
            $this->_parentNode = Mage::getModel('xcom_xfabric/debug_node');
            $this->_parentNode->setMethod(self::ROOT_CALL_METHOD);
            $this->_parentNode->setTopic($topic);
            $this->_parentNode->setHeaders($headers);
            $this->_parentNode->setBody($body);
            $this->_parentNode->setDebugId($this->getId());
            foreach ($this->_visitors as $visitor) {
                $visitor->start($this->_parentNode);
            }
        }
        $this->_dataNode = Mage::getModel('xcom_xfabric/debug_node');
        $this->_dataNode->setMethod($method);
        $this->_dataNode->setDebugId($this->getId());
        $this->_dataNode->setParentId($this->_parentNode->getNodeId());
        foreach ($this->_visitors as $visitor) {
            $visitor->start($this->_dataNode);
        }

        return $this;
    }

    /**
     * Debug service/method call procedure.
     * This method should be called after event
     *
     * @param  string $method   The service/method name to be debugged
     * @param  array  $data     The data could be any mixed array
     * @return void
     */
    public function stop($method, $topic, $headers, $body)
    {
        if (!$this->_dataNode || !$this->_parentNode) {
            return false;
        }

        $this->_dataNode->setTopic($topic);
        $this->_dataNode->setHeaders($headers);
        $this->_dataNode->setBody($body);

        foreach ($this->_visitors as $visitor) {
            $visitor->stop($this->_parentNode);
        }

        foreach ($this->_visitors as $visitor) {
            $visitor->stop($this->_dataNode);
        }

        if ($this->getId()) {
            $this->setCompletedAt(now());
            $this->setCompletedMicrotime(microtime(1));
            $this->save();
        }
        return $this;
    }
}

