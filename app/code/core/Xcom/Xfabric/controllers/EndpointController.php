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
 * @package    Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento Endpoint Controller
 *
 * @category   Xcom
 * @package    Xcom_Xfabric
 */
class Xcom_Xfabric_EndpointController extends Mage_Core_Controller_Front_Action
{

   /**
    * Get all request to endpoint
    * Initialize message procession
    *
    * @return void
    */
    public function indexAction()
    {
        ini_set('memory_limit', '618M');
        $headers = $this->_getHeaders();
        $body = $this->_getInput();
        $topic = $this->getRequest()->getParam('topic');

        Mage::getSingleton('xcom_xfabric/debug')
            ->start('Received message on topic ' . $topic,
                    $topic, serialize($headers), '');

        try {
            $endpoint = Mage::helper('xcom_xfabric')->getEndpoint($topic, $headers, $body);
            $endpoint->receive();
            $debugHeaders = json_encode($headers);
            $debugBody = json_encode($endpoint->getMessage()->getMessageData());
        } catch (Exception $e) {
            $debugHeaders = $e->getMessage() . $e->getTraceAsString();
            $debugBody = 'Error during processing the inbound message';
        }
        Mage::getSingleton('xcom_xfabric/debug')
            ->stop('Result of message receiving', $topic, $debugHeaders, $debugBody);

        echo "OK";
    }

    /**
     * Get raw body from php input
     * @return string
     */
    public function _getInput()
    {
        $input = @file_get_contents('php://input');
        return $input;
    }

    /**
     * Get headers.
     * Doesn't work in CLI context
     * @return mixed
     */
    public function _getHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = array();
        foreach($_SERVER as $headerName => $headerValue) {
            if (substr($headerName, 0, 5) == 'HTTP_') {
                $headers[substr($headerName, 5)] = $headerValue;
            }
        }
        return $headers;
    }
}

