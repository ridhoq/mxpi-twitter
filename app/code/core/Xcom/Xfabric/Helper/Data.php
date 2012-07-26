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

class Xcom_Xfabric_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_DEFAULT_XCOM_TOPICS = 'default/xcom/topics';

    /**
     * @var Xcom_Xfabric_Model_Authorization Keeps authorization model singleton
     */
    protected $_authorizationModel;

    /**
     * Returns authorization model
     *
     * @return Xcom_Xfabric_Model_Authorization
     */
    public function getAuthModel()
    {
        if (!$this->_authorizationModel) {
            $this->_authorizationModel = Mage::getModel('xcom_xfabric/authorization');
            $this->_authorizationModel->load();
        }

        return $this->_authorizationModel;
    }

    /**
     * Returns transport model
     *
     * @return Xcom_Xfabric_Model_Transport_Interface
     */
    public function getTransport()
    {
        $config = array('url' => $this->getAuthModel()->getFabricUrl());
        $transportModel = Mage::getStoreConfig('xfabric/connection_settings/adapter');
        return Mage::getModel($transportModel, $config);
    }

    public function getEncoder()
    {
        switch ($this->getEncoding()) {
            case Xcom_Xfabric_Model_Message::AVRO_JSON_ENCODING:
                $encoder = Mage::getModel('xcom_xfabric/encoder_json');
                break;
            default:
            case Xcom_Xfabric_Model_Message::AVRO_BINARY_ENCODING:
                $encoder = Mage::getModel('xcom_xfabric/encoder_avro');
                break;
        }
        return $encoder;
    }

    public function getEncoding()
    {
        return Mage::getStoreConfig('xfabric/connection_settings/encoding');
    }

    /**
     * Get Message model by Topic.
     * @deprecated Messaging Framework 0.0.2
     *
     * @param string $topic
     * @param bool   $isInbound
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Core_Exception
     */
    public function getMessage($topic, $isInbound = false)
    {
        if (strpos($topic, '/') === 0) {
            $topic = substr($topic, 1);
        }

        $messageNode = null;
        //try to get inbound message object first in case of inbound
        if ($isInbound) {
            $messageNode = $this->getNodeByXpath(".//*[name='" . $topic . "']/message", '/inbound');
        } else {
            $messageNode = $this->getNodeByXpath(".//*[name='" . $topic . "']/message", '/outbound');
        }

        //if inbound node was not got
        if (!$messageNode) {
            $messageNode = $this->getNodeByXpath(".//*[name='" . $topic . "']/message");
        }

        if (!$messageNode) {
            throw Mage::exception('Xcom_Xfabric', $this->__("Unknown topic: '%s'", $topic));
        }
        $message = Mage::getModel((string)$messageNode[0]);
        return $message;
    }

    /**
     * Retrieve node by xpath from current config node
     * @param $xpath
     * @return SimpleXMLElement[]
     */
    public function getNodeByXpath($xpath, $subNode = '')
    {
        $node = self::CONFIG_DEFAULT_XCOM_TOPICS . $subNode;
        if (!$node = Mage::getConfig()->getNode($node)) {
            return null;
        }
        return $node->xpath($xpath);
    }

    /**
     * Send message
     *
     * @param $topic
     * @param array $dataObject
     * @return Varien_Object
     * @throws Xcom_Xfabric_Exception
     */
    public function send($topic, array $dataObject = array())
    {
        try {
            $messageDataModel = Mage::getModel($topic, $dataObject);
        } catch (Exception $e) {
            $messageDataModel = false;
        }

        if (!$messageDataModel) {
            /* @deprecated backward compatibility with getting message by topic */
            /** @var $message Xcom_Xfabric_Model_Message_Request */
            $messageDataModel = $this->getMessage($topic);
            if (!$messageDataModel) {
                throw Mage::exception('Xcom_Xfabric', $this->__("Message for topic %s should be created", $topic));
            }
        }
        if ($messageDataModel instanceof Xcom_Xfabric_Model_Message_Request) {
            /* @deprecated backward compatibility with Xcom_Xfabric_Model_Message_Request */
            if (is_array($dataObject)) {
                $dataObject = new Varien_Object($dataObject);
            }
            $messageDataModel->process($dataObject);
            $result = $this->getTransport()
                ->setMessage($messageDataModel)
                ->send();
            return empty($result) ? new Varien_Object(array('response_data' => array())) : $result;
        } else if ($messageDataModel instanceof Xcom_Xfabric_Model_Message_Data_Interface) {
            $messageOptions = $messageDataModel->getOptions();
            $options = array(
                'message_data' => $messageDataModel,
                'transport' => $this->getTransport(),
                'authorization' => $this->getAuthModel(),
                'schema' => $this->getSchema(
                    $this->getSchemaUri($messageOptions['topic'], $messageOptions['schema_version'])),
                'encoder' => $this->getEncoder(),
                'encoding' => $this->getEncoding(),
            );

            try {
                $result = Mage::getModel('xcom_xfabric/endpoint', $options)
                    ->send();
                return empty($result) ? new Varien_Object(array('response_data' => array())) : $result;
            } catch (Exception $e) {
                throw Mage::exception('Xcom_Xfabric', $e->getMessage());
            }
        } else {
            throw Mage::exception('Xcom_Xfabric', 'Unknown Message Data object type');
        }
    }

    public function getEndpoint($topic, $headers, $body)
    {
        $topicConfig = strlen($topic) > 0 ? $this->getNodeByXpath("//*[name='" . $topic . "']", '/inbound') : array();
        $messageDataOptions = array(
            'topic' => $topic,
            'headers' => $headers,
            'body' => $body,
            'topic_options' => $topicConfig
        );
        $messageDataModel = Mage::getModel('xcom_xfabric/message_data_inbound', $messageDataOptions);

        $endpointOptions = array(
            'message_data' => $messageDataModel,
            'schema'  => $this->getSchema(
                $this->getValueByKey($headers, Xcom_Xfabric_Model_Message::SCHEMA_URI_HEADER)),
            'encoder'  => $this->getEncoder(),
            'encoding' => $this->getEncoding(),
            'authorization' => $this->getAuthModel(),
            'callback_handler' => Mage::getModel('xcom_xfabric/callback_dispatcher'),
        );

        return Mage::getModel('xcom_xfabric/endpoint', $endpointOptions);
    }

    public function getSchemaUri($topic, $version)
    {
        return $this->getOntologyBaseUri() . $topic . '/' . $version;
    }

    public function getValueByKey($assocArray, $key)
    {
        if (!empty($assocArray[strtolower($key)])) {
            return trim($assocArray[strtolower($key)]);

        }
        if (!empty($assocArray[strtoupper($key)])) {
            return trim($assocArray[strtoupper($key)]);
        }
        return null;
    }

    public function getSchema($uri)
    {
        $schemaOptions = array(
            'schema_uri' => $uri
        );
        return Mage::getModel('xcom_xfabric/schema', $schemaOptions);
    }

    /**
     * Returns autorization header.
     * @deprecated Messaging Framework 0.0.2
     * @param $isTenant
     * @return array
     */
    public function getAuthorizationHeader($isTenant = true)
    {
        $authorization = '';
        if ($this->getAuthModel()->hasAuthorizationData()) {
            $authorization = $isTenant ? $this->getAuthModel()->getAuthorizations()->getTenant()->getBearerToken()
            : $this->getAuthModel()->getAuthorizations()->getSelf()->getBearerToken();
        }

        return array('Authorization' => $authorization);
    }

    /**
     * Returns XFABRIC authorization key
     * @deprecated Messaging Framework 0.0.2
     * @return string
     */
    public function getResponseAuthorizationKey()
    {
        $authorization = $this->getAuthModel()->hasAuthorizationData()
            ? $this->getAuthModel()->getAuthorizations()->getXfabric()->getBearerToken()
            : '';

        return $authorization;
    }

    /**
     * Returns ontology server URI
     *
     * @return string
     */
    public function getOntologyBaseUri()
    {
        return Mage::getStoreConfig('xfabric/connection_settings/ontology_server_uri');
    }

    /**
     * Return canonized event name based on topic
     * @param $topic
     */
    public function getEventSuffix($topic)
    {
        return strtolower(preg_replace('/\W/', '_', trim($topic, '/')));
    }
}
