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
class Xcom_Chronicle_Model_Message_Product_Deleted_OutboundTest extends Xcom_TestCase
{
    /** @var Xcom_Chronicle_Model_Message_Product_Deleted_Outbound */
    protected $_object;
    protected $_instanceOf = 'Xcom_Chronicle_Model_Message_Product_Deleted_Outbound';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('com.x.pim.v1/ProductDeletion/ProductDeleted');
        $this->_object
            ->setEncoding(Xcom_Xfabric_Model_Message_Abstract::AVRO_JSON);
    }

    public function testConstructorSetFields()
    {
        $this->assertEquals('com.x.pim.v1/ProductDeletion/ProductDeleted', $this->_object->getTopic());
        $this->assertEquals('ProductDeleted', $this->_object->getSchemaRecordName());
        // _schemaFile is also set but there is no magical getter
    }
}