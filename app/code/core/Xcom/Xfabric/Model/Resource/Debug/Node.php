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
class Xcom_Xfabric_Model_Resource_Debug_Node extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource model initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_xfabric/debug_node', 'node_id');
    }

    /**
     * Insert new record into Debug Table
     *
     * @param  object $node
     *
     * @return object Xcom_Xfabric_Model_Resource_Debug
     */
    public function insert($node)
    {
        $adapter = $this->_getWriteAdapter();
        //Fixed zero symbols
        //$body = preg_replace('/[\x00]\*[\x00]/', '___', serialize($node->getBody()));

        $adapter->insert($this->getMainTable(), array(
            'debug_id'              => $node->getDebugId(),
            'topic'                 => $node->getTopic(),
            'headers'               => $node->getHeaders(),
            'body'                  => $node->getBody(),
            'memory_usage_before'   => $node->getMemoryUsageBefore(),
            'started_at'            => now(),
            'started_microtime'     => $node->getStartedMicrotime(),
        ));

        // update node data with node_id value
        $nodeId = $adapter->lastInsertId();
        $node->setNodeId($nodeId);

        return $this;
    }

    /**
     * Update Debug entry data
     *
     * @param  object   $node
     * @param  boolean  $hasError
     *
     * @return object   Xcom_Xfabric_Model_Resource_Debug
     */
    public function update($node, $hasError)
    {
        //Fixed zero symbols
        //$body = preg_replace('/[\x00]\*[\x00]/', '___', serialize($node->getBody()));

        $this->_getWriteAdapter()->update($this->getMainTable(), array(
            'parent_id'             => $node->getParentId(),
            'topic'                 => $node->getTopic(),
            'headers'               => $node->getHeaders(),
            'body'                  => $node->getBody(),
            'memory_usage_after'    => $node->getMemoryUsageAfter(),
            'completed_at'          => now(),
            'has_error'             => $hasError,
            'completed_microtime'   => $node->getCompletedMicrotime(),
        ), array('node_id = ? ' => $node->getNodeId()));

        return $this;
    }
}
