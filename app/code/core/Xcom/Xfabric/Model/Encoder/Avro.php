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
require_once('avro.php');
class Xcom_Xfabric_Model_Encoder_Avro
    implements Xcom_Xfabric_Model_Encoder_Interface
{

    /**
     * Encode avro message
     *
     * @param Xcom_Xfabric_Model_Message_Request $message
     * @return Xcom_Xfabric_Model_Encoder_Avro
     */
    public function encode(Xcom_Xfabric_Model_Message_Abstract $message)
    {
        $encodedBody = $this->encodeText($message->getBody(), $message->getSchema()->getRawSchema());
        $message->setBody($encodedBody);
        return $this;
    }

    public function encodeText($text, $rawSchema)
    {
        /* @deprecated backward compatibility with Messaging Framework 0.0.1 */
        if ($rawSchema instanceof Xcom_Xfabric_Model_Message_Abstract) {
            $rawSchema = $rawSchema->getSchema()->getRawSchema();
        }

        $schema = AvroSchema::parse($rawSchema);
        $datum_writer = new AvroIODatumWriter($schema);
        $write_io = new AvroStringIO();
        $encoder = new AvroIOBinaryEncoder($write_io);
        $datum_writer->write($text, $encoder);
        $encodedText = $write_io->string();
        return $encodedText;
    }

   /**
     * Decode avro message
     *
     * @param Xcom_Xfabric_Model_Message_Abstract $message
     * @return Xcom_Xfabric_Model_Encoder_Avro
     */
   public function decode(Xcom_Xfabric_Model_Message_Abstract $message)
   {
       $rawSchema = $message->getSchema()->getRawSchema();
       $decodedBody = $this->decodeText($message->getBody(), $rawSchema);
       $message->setBody($decodedBody);
       return $this;
   }

    /**
     * Decode avro data
     *
     * @param string $text
     * @param string $rawSchema
     * @return mixed
     */
   public function decodeText($text, $rawSchema)
   {
       $schema = AvroSchema::parse($rawSchema);
       $datum_reader = new AvroIODatumReader($schema, $schema);
       $read_io = new AvroStringIO($text);
       $decoder = new AvroIOBinaryDecoder($read_io);
       $results = $datum_reader->read($decoder);
       return $results;
   }
}
