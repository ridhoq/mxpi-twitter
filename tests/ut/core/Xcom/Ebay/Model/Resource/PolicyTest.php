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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Ebay_Model_Resource_PolicyTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Model_Resource_Policy */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Model_Resource_Policy();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }
    /**
     * @param array $testData
     * @dataProvider afterSaveProvider
     */
    public function testAfterSave($testData)
    {
        $testObject = new Xcom_Ebay_Model_Policy();
        $testObject->addData($testData);
        $tableName = Mage::getModel('core/resource')->getTableName('xcom_ebay/channel_policy');
        $adaptorMock = $this->getMock('Varien_Db_Adapter_Pdo_MysqlFixture',
            array('insertOnDuplicate'), array(), '', false);
        $adaptorMock->expects($this->any())
            ->method('insertOnDuplicate')
            ->with($this->equalTo($tableName),
                   $this->equalTo($testData),
                   $this->equalTo(array_keys($testData)));

        $objectMock = $this->getMock('Xcom_Ebay_Model_Resource_PolicyFixture',
            array('_getWriteAdapter', '_cleanPolicyShipping', 'savePolicyShipping', '_prepareDataForTable'));
        $objectMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($adaptorMock));
        $objectMock->expects($this->any())
            ->method('_prepareDataForTable')
            ->will($this->returnValue($testData));

        $objectMock->afterSave($testObject);
    }

    public function afterSaveProvider()
    {
        return array(
            array(array(
                'payment_name'          => 'value_1',
                'payment_paypal_email'  => 'value_2',
                'location'              => 'value_3',
                'currency'              => 'value_4',
                'return_accepted'       => 'value_5',
                'return_by_days'        => 'value_6',
                'refund_method'         => 'value_7',
                'shipping_paid_by'      => 'value_8',
                'return_description'    => 'value_9',
                'apply_tax'             => 'value_10',
                'handling_time'         => null,
                'postal_code'           => '12345',
                'policy_id'             => null,
            )),
            array(array(
                'payment_name'          => '',
                'payment_paypal_email'  => null,
                'location'              => '',
                'currency'              => '',
                'return_accepted'       => '0',
                'return_by_days'        => null,
                'refund_method'         => null,
                'shipping_paid_by'      => null,
                'return_description'    => null,
                'apply_tax'             => '0',
                'handling_time'         => null,
                'postal_code'           => null,
                'policy_id'             => null
            )),
        );
    }
}

class Xcom_Ebay_Model_Resource_PolicyFixture extends Xcom_Ebay_Model_Resource_Policy
{
    public function afterSave($object)
    {
        return $this->_afterSave($object);
    }
}

class Varien_Db_Adapter_Pdo_MysqlFixture
{
    public function insertOnDuplicate($table, $data, $bind)
    {}
}
