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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Customer_Get_Inbound extends Xcom_Xfabric_Model_Message_Response
{


    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic = 'com.x.customer.v1/CustomerLookup/LookupCustomer';
        $this->_schemaRecordName = 'LookupCustomer';
        $this->_schemaVersion = "1.1.1";

        parent::_construct();
    }

    /**
     * Builds the Get success and/or failure outbound messages.
     * @return Xcom_Chronicle_Model_Message_Inventory_Stock_Get_Inbound this message
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        if (!isset($data)) {
            $data = array();
        }

        try {
            if ($this->_validateSchema()) {
                $resultSet = $this->_processLookup($data);
                $customer = $resultSet['customer'];
                if (!is_null($customer)) {
                    $response = array(
                        'customer' => $customer,
                        'destination_id' => $this->getPublisherPseudonym(),
                        'correlation_id' => $this->getCorrelationId(),
                    );
                    $this->_sendSuccess($response);
                }
                if (sizeof($resultSet['errors']) > 0) {
                    $response = array(
                        'errors' => $resultSet['errors'],
                        'destination_id' => $this->getPublisherPseudonym(),
                        'correlation_id' => $this->getCorrelationId(),
                    );
                    $this->_sendFailure($response);
                }
            }
        } catch (Exception $ex) {
            Mage::logException($ex);


            $errorResponse = array(
                'id' => $data['id'],
                'errors' => array(
                    array(
                        'code' => empty($code) ? '-1' : '' . $code,
                        'message' => $ex->getMessage(),
                        'parameters' => null
                    )
                ),
                'destination_id' => $this->getPublisherPseudonym(),
                'correlation_id' => $this->getCorrelationId(),
            );
            $this->_sendFailure($errorResponse);
        }
        return $this;
    }

    protected function _sendFailure($response)
    {
        Mage::helper('xcom_xfabric')->send('com.x.customer.v1/CustomerLookup/LookupCustomerFailed', $response);
    }

    protected function _sendSuccess($response)
    {
        Mage::helper('xcom_xfabric')->send('com.x.customer.v1/CustomerLookup/LookupCustomerSucceeded', $response);
    }

    /**
     * ProcessLookup
     *
     * @param array $data
     * @return array
     */
    protected function _processLookup(&$data)
    {
        $errors = array();
        $id = $data['id']['Id'];
        $namespace = $data['id']['namespace'];
        $capabilityId = Mage::helper('xcom_chronicle')->getNamespace();
        if ($namespace != $capabilityId) {
            $errors[] = array(
                'customer' => $id,
                'errors' => array(
                    array(
                        'code' => '-1',
                        'message' => 'namespace does not match',
                        'parameters' => null
                    )
                )
            );
            $resultSet = array(
                'customer' => null,
                'errors' => $errors,
            );
            return $resultSet;
        }

        $customer = null;

        try {
            $customer = $this->_getCustomerById($id);
            if (is_null($customer)) {
                $errors[] = array(
                    'customer' => $id,
                    'errors' => array(array(
                        'code' => '-1',
                        'message' => 'Could not find customer with given id',
                        'parameters' => null))
                );
            }
        }
        catch (Exception $ex) {
            Mage::logException($ex);
            $errors[] = array(
                'customer' => $id,
                'errors' => array(array(
                    'code' => '-1',
                    'message' => 'Exception when looking up customer.',
                    'parameters' => null))
            );
        }
        $resultSet = array(
            'customer' => $customer,
            'errors' => $errors,
        );
        return $resultSet;
    }

    protected function _getCustomerById($id)
    {
        $c = Mage::getModel('customer/customer')->load((int)$id);
        if (is_null($c->getEntityId())) {
            return null;
        } else {
            return Mage::getModel('xcom_chronicle/message_customer', $c)->toArray();
        }
    }
}


