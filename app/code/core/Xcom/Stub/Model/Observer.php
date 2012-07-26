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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Stub_Model_Observer
{

    public function sendResponse($stubId)
    {
        ini_set('memory_limit', '618M');
        $message = Mage::getModel('xcom_stub/message');
        $message->load($stubId);

        $body = $message->getRecipientMessageBody();
        $topic = $message->getRecipientTopicName();
        if (empty($topic) || empty($body)) {
            echo "Empty topic or body";
            return;
        }
        $headers['X-XC-SCHEMA-VERSION'] = '1.0.0';
        $headers['Authorization']       = 'Bearer QVVUSElELTEA+5YxhWpsZwk6hKkr/iEwQg11';
        $headers['X-XC-SCHEMA-URI']     = "https://ocl.xcommercecloud.com/$topic/1.0.0";


        try {
            /** @var $message Xcom_Xfabric_Model_Message_Response */
            $message = Mage::helper('xcom_xfabric')->getMessage($topic, true);
            $message
                ->setBody($body)
                ->setTopic($topic)
                ->setHeaders($headers);
            $message->decode();
            $message->process();
            $message->save();
            echo PHP_EOL . "$topic :: OK" . PHP_EOL;

        } catch (Exception $e) {
            echo $e;
        }
    }

    public function createMessageFromServer($topic, $recordName, $version)
    {
        $host = Mage::helper('xcom_xfabric')->getOntologyBaseUri();
        echo $schemaUri = $host . $topic . '/' . $version;
        echo "\r\n";

        $schema = Mage::getModel('xcom_xfabric/schema')
            ->init($recordName, $version, $schemaUri);

        $rawSchema = $schema->getRawSchema();
        $original = json_decode($rawSchema, true);
        //print_r($original);
        $message = $this->getMessageBody($original);
        return $message;
    }


    public function createMessageFromFile($fileName)
    {
        $rawSchema = file_get_contents($fileName);
        $message = $this->getMessageBody(json_decode($rawSchema, true));
        return $message;
    }

    public function getMessageBody($schema)
    {
        $message = array();
        if (isset($schema['type'])) {
            switch ($schema['type']) {
                case 'record':
                    foreach ($schema['fields'] as $entry) {
                        $message = array_merge($message, $this->getMessageBody($entry));
                    }
                    break;
                case 'array':
                        if (is_array($schema['items'])) {
                            $message = array_merge($message, $this->getMessageBody($schema['items']));
                        } else {
                            $message[] = $schema['items'];
                        }
                    break;
                case 'string':
                    $message[$schema['name']] = 'string';
                    $message[$schema['name']] .= (!empty($schema['doc'])) ?  '. [' . $schema['doc'] . ']' : '';
                    break;
                case 'bytes':
                    $message[$schema['name']] = 'bytes';
                    break;
                case 'enum':
                    return $schema['symbols'][0];
                    break;
                default:
                    if (is_array($schema['type'])) {
                        $message[$schema['name']] = $this->getMessageBody($schema['type']);
                    } else if (isset($schema['name'])) {
                        $message[$schema['name']] = $schema['type'];
                    }
            }
        } else {
            $values = $this->getArrayValues($schema);
            if (is_array($values)) {
                $message = array_merge($message, $this->getArrayValues($schema));
            } else {
                $message = $values;
            }
        }
        return $message;
    }

    public function getArrayValues($schema)
    {
        $array_exists = false;
        foreach ($schema as $entry) {
            if (is_array($entry)) {
                $array_exists = true;
            }
        }

        if (!$array_exists) {
            return implode(';', $schema);
        }

        $message = array();
        if (is_array($schema)) {
            foreach ($schema as $entry) {
                if (is_array($entry)) {
                    $message[] = $this->getMessageBody($entry);
                } else {
                    if ($entry != 'null') {
                        $message[] = $entry;
                    }
                }
            }
        } else {
            return array($schema);
        }
        return $message;
    }
}
