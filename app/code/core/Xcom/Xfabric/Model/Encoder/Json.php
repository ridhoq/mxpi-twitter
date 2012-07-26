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

class Xcom_Xfabric_Model_Encoder_Json
    implements Xcom_Xfabric_Model_Encoder_Interface
{
    /**
     * Encode json data.
     *
     * @param Xcom_Xfabric_Model_Message_Abstract $message
     * @return Xcom_Xfabric_Model_Encoder_Json
     */
    public function encode(Xcom_Xfabric_Model_Message_Abstract $message)
    {
        $data = $message->getBody();
        if (!empty($data)) {
            $encodedBody = $this->encodeText($data, $message);
            $message->setBody($encodedBody);
        }
        return $this;
    }

    public function encodeText($text, $rawSchema)
    {
        return Zend_Json_Encoder::encode($text);
    }
    /**
     * Decode json data and retrieve array.
     *
     * @param Xcom_Xfabric_Model_Message_Abstract $message
     * @return Xcom_Xfabric_Model_Encoder_Json
     */
    public function decode(Xcom_Xfabric_Model_Message_Abstract $message)
    {
        $data = $message->getBody();
        $result = json_decode($data, true);
        $message->setBody($result);
        return $this;
    }

    /**
     * Decode avro data.
     *
     * @param string $text
     * @param string $rawSchema
     * @return mixed
     */
    public function decodeText($text, $rawSchema)
    {
        return json_decode($text, true);
    }
}
