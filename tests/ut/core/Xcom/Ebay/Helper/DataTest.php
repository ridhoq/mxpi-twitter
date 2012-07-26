<?php
class Xcom_Ebay_Helper_DataTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Helper_Data */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Helper_Data();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testPrepareAttributeSetArray()
    {
        $productIds = array(1,2,3);
        $helperMock = $this->mockHelper('xcom_ebay', array('getRequestProductIds'));
        $helperMock->expects($this->once())
            ->method('getRequestProductIds')
            ->will($this->returnValue($productIds));

        $expectedValue = array(11,12,13);
        $channelProduct = $this->mockModel('xcom_listing/channel_product', array('getProductAttributeSets'));
        $channelProduct->expects($this->once())
            ->method('getProductAttributeSets')
            ->with($this->equalTo($productIds))
            ->will($this->returnValue($expectedValue));

        $result = $helperMock->prepareAttributeSetArray();
        $this->assertEquals($expectedValue, $result);
    }

    /**
     * @dataProvider getRequestProductIdsProvider
     */
    public function testGetRequestProductIdsWithEmptyParam($expectedResult)
    {
        Mage::app()->getRequest()->setParam('selected_products', $expectedResult);
        $result = $this->_object->getRequestProductIds();
        $this->assertInternalType('array', $result);
        $this->assertEquals($expectedResult, $result);
    }

    public function getRequestProductIdsProvider()
    {
        return array(
            array(array(1,2,3)),
            array(array())
        );
    }

    public function testGetRequestProductIdsWithEmptyParam2()
    {
        $expectedResult = '1,2,3';
        Mage::app()->getRequest()->setParam('selected_products', $expectedResult);
        $result = $this->_object->getRequestProductIds();
        $this->assertInternalType('array', $result);
        $this->assertEquals(explode(',', $expectedResult), $result);
    }

    public function testGetRequestProductIdsWithParam()
    {
        $expectedResult = '1,2,3';
        $result = $this->_object->getRequestProductIds();
        $this->assertInternalType('array', $result);
        $this->assertEquals(explode(',', $expectedResult), $result);
    }

    public function testGetRequestProductIdsWithParam2()
    {
        $expectedResult = array(1,2,3);
        $result = $this->_object->getRequestProductIds();
        $this->assertInternalType('array', $result);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetSession()
    {
        $sessionMock = $this->mockModel('adminhtml/session');
        $this->assertInstanceOf(get_class($sessionMock), $this->_object->getSession());
    }

    public function testValidateIsRequiredAttributeHasMappedValueWithoutAttributeSets()
    {
        $objectMock = $this->getMock(get_class($this->_object),
                                     array('prepareAttributeSetArray'));
        $objectMock->expects($this->once())
            ->method('prepareAttributeSetArray')
            ->will($this->returnValue(array()));

        $result = $objectMock->validateIsRequiredAttributeHasMappedValue();
        $this->assertFalse($result);
    }

    public function testValidateIsRequiredAttributeHasMappedValueWithNoProductType()
    {
        $objectMock = $this->getMock(get_class($this->_object),
                                     array('prepareAttributeSetArray'));
        $objectMock->expects($this->once())
            ->method('prepareAttributeSetArray')
            ->will($this->returnValue(array('set_1')));

        $productTypeMock = $this->getMock('Xcom_Mapping_Model_Resource_Product_Type',
            array('getMappingProductTypeId'), array(), '', false);
        Mage::registerMockResourceModel('xcom_mapping/product_type', $productTypeMock);
        $productTypeMock->expects($this->once())
            ->method('getMappingProductTypeId')
            ->with($this->equalTo('set_1'))
            ->will($this->returnValue(false));

        $result = $objectMock->validateIsRequiredAttributeHasMappedValue();
        $this->assertTrue($result);
    }


    public function testValidateIsRequiredAttributeHasMappedValueWithNotMappedAttributes()
    {
        $objectMock = $this->getMock(get_class($this->_object),
                                     array('prepareAttributeSetArray'));
        $objectMock->expects($this->once())
            ->method('prepareAttributeSetArray')
            ->will($this->returnValue(array('set_1')));

        $productTypeMock = $this->getMock('Xcom_Mapping_Model_Resource_Product_Type',
            array('getMappingProductTypeId'), array(), '', false);
        Mage::registerMockResourceModel('xcom_mapping/product_type', $productTypeMock);

        $productTypeMock->expects($this->once())
            ->method('getMappingProductTypeId')
            ->with($this->equalTo('set_1'))
            ->will($this->returnValue('type_1'));

        $validatorMock = $this->mockModel('xcom_mapping/validator',
                                    array('validateIsRequiredAttributeHasMappedValue'));

        $validatorMock->expects($this->once())
            ->method('validateIsRequiredAttributeHasMappedValue')
            ->with($this->equalTo('type_1'), null, $this->equalTo('set_1'))
            ->will($this->returnValue(false));

        $result = $objectMock->validateIsRequiredAttributeHasMappedValue();
        $this->assertTrue($result);
    }


    public function testUpdateListingForProduct()
    {
        $productId  = rand();
        $listingIds = array(
            1 => array('product_ids' => array($productId), 'channel_id' => rand()),
            2 => array('product_ids' => array($productId), 'channel_id' => rand()));
        $channelProductMock = $this->mockModel('xcom_listing/channel_product', array('getPublishedListingIds'));
        $channelProductMock->expects($this->once())
            ->method('getPublishedListingIds')
            ->with($this->equalTo(array($productId)))
            ->will($this->returnValue($listingIds));

        $listingMock = $this->mockModel('xcom_listing/listing', array('load', 'getQtyValue', 'prepareProducts',
            'getPolicyId', 'send', 'save', 'saveProducts'));
        $listingMock->expects($this->at(0))
            ->method('load')
            ->with($this->equalTo(1));
        $listingMock->expects($this->at(2))
            ->method('load')
            ->with($this->equalTo(2));
        $listingMock->expects($this->exactly(2))
            ->method('getQtyValue')
            ->will($this->onConsecutiveCalls(0,10));
        $listingMock->expects($this->once())
            ->method('prepareProducts')
            ->with($this->equalTo($listingIds[2]['product_ids']));
        $listingMock->expects($this->once())
            ->method('getPolicyId')
            ->will($this->returnValue(rand()));

        $policy = new Varien_object();
        $policyMock = $this->mockModel('xcom_ebay/policy', array('load'));
        $policyMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($policy));

        $channel    = new Varien_object();
        $channelMock = $this->mockModel('xcom_ebay/channel', array('load'));
        $channelMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($listingIds[2]['channel_id']))
            ->will($this->returnValue($channel));

        $listingMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo(array('policy'  => $policy, 'channel' => $channel)));

        $listingMock->expects($this->once())
            ->method('save');
        $listingMock->expects($this->once())
            ->method('saveProducts');

        $this->_object->updateListingForProduct(array($productId));
    }

    public function testIsExtensionEnabled()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED, false);
        $this->assertFalse($this->_object->isExtensionEnabled());
    }

    public function testGetRegistrationTargetCapabilityName()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_TARGET_CAPABILITY_NAME,
            'test target_capability_name');
        $this->assertEquals('test target_capability_name', $this->_object->getRegistrationTargetCapabilityName());
    }

    public function testGetRegistrationStoreEndpointUrl()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_ENDPOINT_URL,
            'test store_endpoint_url');
        $this->assertEquals('test store_endpoint_url', $this->_object->getRegistrationStoreEndpointUrl());
    }

    public function testGetRegistrationRequestUrl()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_REQUEST_URL,
            'test request_url');
        $this->assertEquals('test request_url', $this->_object->getRegistrationRequestUrl());
    }

    public function testGetRegistrationIsRegistered()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_IS_REGISTERED, '1');
        $this->assertTrue($this->_object->getRegistrationIsRegistered());
    }

    public function testGetRegistrationLegalAgreementUrl()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_LEGAL_AGREEMENT_URL,
            'test legal_agreement_url');
        $this->assertEquals('test legal_agreement_url', $this->_object->getRegistrationLegalAgreementUrl());
    }

    public function testGetRegistrationStoreFrontPlatform()
    {
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_FRONT_PLATFORM,
            'test store-front-platform');
        $this->assertEquals('test store-front-platform', $this->_object->getRegistrationStoreFrontPlatform());
    }

    public function testGetRegistrationRequest()
    {
        $result = new Varien_Object();
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_TARGET_CAPABILITY_NAME,
            'test target_capability_name');
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_ENDPOINT_URL,
            'test store_endpoint_url');
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_IS_REGISTERED, '1');
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_LEGAL_AGREEMENT_URL,
            'test legal_agreement_url');
        $this->mockStoreConfig(Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_FRONT_PLATFORM,
            'test store-front-platform');

        $data = array(
            'target_capability_name' => 'test target_capability_name',
            'store_endpoint_url'     => 'test store_endpoint_url',
            'is_registered'          => true,
            'legal_agreement_url'    => 'test legal_agreement_url',
            'store-front-platform'   => 'test store-front-platform',
        );
        $result->setOnboardingInfo(urlencode(Zend_Json::encode($data)));

        $this->assertEquals($result, $this->_object->getRegistrationRequest());
    }
    
    public function testGetEnvironmentHash()
    {

        $collectionMock = $this->mockResource('xcom_mmp/environment_collection',
            array('addFieldToFilter', 'toOptionHash'));
        $collectionMock->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with($this->equalTo('channel_type_code'), $this->equalTo('eBay'))
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with($this->equalTo('site_code'), $this->equalTo('US'))
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('toOptionHash')
            ->will($this->returnValue(array('test' => 'test_value')));
        $envMock = $this->mockModel('xcom_mmp/environment', array('getCollection'));
        $envMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($collectionMock));

        $this->assertArrayHasKey('test', $this->_object->getEnvironmentHash());
    }

    public function testIsXfabricRegistered()
    {
        $mockHelper = $this->mockHelper('xcom_xfabric', array('getResponseAuthorizationKey'));
        $mockHelper->expects($this->once())
            ->method('getResponseAuthorizationKey')
            ->will($this->returnValue(true));
        $this->assertTrue($this->_object->isXfabricRegistered());
    }
}
