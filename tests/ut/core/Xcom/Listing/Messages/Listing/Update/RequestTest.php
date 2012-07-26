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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Listing_Message_Listing_Update_RequestTest extends Xcom_TestCase
{
    /**
     * @var $_object Xcom_Listing_Model_Message_Listing_Update_Request
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_checkConnection = true;
        $this->_object = Mage::helper('xcom_xfabric')->getMessage('listing/update');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testPrepare()
    {
        $options = $this->_getOptions();
        $dataObject = new Varien_Object($options);
        $this->mockStoreConfig('xfabric/connection_settings/encoding', 'json');

        // this method calls in _prepareProductOptions as $this->getMapper()->getMappingOptions($product);
        $mockAttributeModel = $this->mockModel('xcom_mapping/attribute', array('getMappingOptions'));
        $mockAttributeModel->expects($this->any())
            ->method('getMappingOptions')
            ->will($this->returnValue(array('mapping_attribute_code' => 'mapping_attribute_value')));

        $encoder = $this->mockModel('xcom_xfabric/encoder_avro', array('encode'));

        $objectMock = $this->getMock(get_class($this->_object),
            array('_prepareListingInformation', '_saveLogRequestBody', '_prepareChannelHistory', 'getEncoder'));
        $objectMock->expects($this->any())
            ->method('_prepareListingInformation')
            ->will($this->returnValue(array()));
        $objectMock->expects($this->any())
            ->method('_saveLogRequestBody')
            ->will($this->returnValue(new Varien_Object(array('correlation_id' => 'test_correlation_id'))));
        $objectMock->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($encoder));

        $encoder = $this->mockModel('xcom_xfabric/schema', array(), FALSE);
        $objectMock->process($dataObject);
        $data = $objectMock->getMessageData();

        $this->assertInternalType('array', $data['updates'], 'Data updates must be array');
        $this->assertEquals($options['policy']->getXprofileId(), $data['xProfileId']);
        $this->assertContains('test_correlation_id', $objectMock->getHeaders());
    }

    protected function _getOptions()
    {
        $product1 = new Varien_Object(array('id' => 100, 'listing_market_item_id' => 'test_market_id'));
        return array(
            'products'     => array($product1),
            'policy'       => $this->_mockPolicy(),
            'channel'      => new Varien_Object(
                array(
                    'store_id' => 0,
                    'name'     => 'test_channel',
                    'code'     => 'test_code'
                )
            ),
            'market_ids' => array(1, 2)
        );
    }

    /**
     * Test the "Options should be specified." exception
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Options should be specified.
     */
    public function testNoOptionError()
    {
        $this->_object->process(null);
    }

    /**
     * Test the "Channel should be specified." exception
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Channel should be specified.
     */
    public function testNoChannelError()
    {
        $dataObject = new Varien_Object(
            array(
                'channel'      => null
            )
        );
        $this->_object->process($dataObject);
    }

    /**
     * Test the "Products should be specified." exception
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Products should be specified.
     */
    public function testNoProductsError()
    {
        $dataObject = new Varien_Object(
            array(
                'channel'      => new Varien_Object(
                    array(
                        'store_id' => 0,
                        'name'     => 'test_channel',
                        'code'     => 'test_code'
                    )
                ),
            )
        );
        $this->_object->process($dataObject);
    }

    protected function _mockPolicy()
    {
        $policyObject = $this->getMock('Xcom_Mmp_Model_Policy', array('save'), array(
            array(
                'policy_id'   => 'test_policy_id',
                'xprofile_id' => 'test_xprofile_id',
                'id'   => 'testId',
                'name' => 'test',
                'payment_name' => 'AMEX,CHECK',
                'shippings' => array(
                    new Varien_Object(
                        array(
                            'rate_type'      => 'TestRateType',
                            'international'  => 1,
                            'service_name'   => 'test_shipping_service'
                        )
                    ),
                    new Varien_Object(
                        array(
                            'rate_type'      => 'TestRateType2',
                            'international'  => 0,
                            'service_name'   => 'test_shipping_service2'
                        )
                    )
                ),
            ))
        );
      return $policyObject;
    }


    protected function _getOptionsWithProducts()
    {
        $policy = new Varien_Object();
        $policy->setId(1);
        $this->_mockPolicy();
        return array(
            'products'     => array(new Varien_Object(
                array(
                    'sku'                   => 'test_one',
                    'name'                  => 'test_name',
                    'id'                    => 1,
                    'price'                 => 10,
                    'entity_id'             => 1,
                    'qty'                   => 10
                )
            )),
            'policy'       => $policy,
            'channel'      => new Varien_Object(
                array(
                    'store_id' => 0,
                    'name'     => 'test_channel',
                    'code'     => 'test_code'
                )
            ),
            'market_ids' => array(1, 2)
        );
    }

    /**
     * Test preparing updates record
     *
     * @return void
     */
    public function testPrepareUpdatesRecordNonObjectProduct()
    {
        $dataObject = new Varien_Object(
            $this->_getOptionsWithProducts()
        );
        $this->mockModel('xcom_xfabric/encoder_avro', array('encodeText'));
        $dataObject->setData('products', array(array(
            'sku'                   => 'test_one',
            'name'                  => 'test_name',
            'id'                    => 1,
            'price'                 => 10,
            'entity_id'             => 1,
            'qty'                   => 10
        )));

        // this method calls in _prepareProductOptions as $this->getMapper()->getMappingOptions($product);
        $mockAttributeModel = $this->mockModel('xcom_mapping/attribute', array('getMappingOptions'));
        $mockAttributeModel->expects($this->any())
            ->method('getMappingOptions')
            ->will($this->returnValue(array('mapping_attribute_code' => 'mapping_attribute_value')));

        $mockAttributeModel = $this->mockModel('xcom_listing/channel_history', array('save'));
        $mockAttributeModel->expects($this->never())
            ->method('save');

        $this->_object->process($dataObject);
        $data = $this->_object->getMessageData();

        $this->assertInternalType('array', $data['updates'], 'Data updates must be array');
        $this->assertEquals(0, count($data['updates']), 'Products should not created.');
    }

    /**
     * Test preparing updates record with non-object products
     *
     * @return void
     */
    public function testPrepareUpdatesRecord()
    {
        $dataObject = new Varien_Object(
            $this->_getOptionsWithProducts()
        );

        $this->mockModel('xcom_xfabric/encoder_avro', array('encodeText'));
        // this method calls in _prepareProductOptions as $this->getMapper()->getMappingOptions($product);
        $mockAttributeModel = $this->mockModel('xcom_mapping/attribute', array('getMappingOptions'));
        $mockAttributeModel->expects($this->any())
            ->method('getMappingOptions')
            ->will($this->returnValue(array('mapping_attribute_code' => 'mapping_attribute_value')));

        $this->_object->process($dataObject);
        $data = $this->_object->getMessageData();

        $this->assertInternalType('array', $data['updates'], 'Data updates must be array');
        $this->assertEquals(
            count($dataObject->getData('products')),
            count($data['updates']),
            'Products are not created.'
        );
    }
}
