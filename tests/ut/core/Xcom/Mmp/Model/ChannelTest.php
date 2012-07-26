<?php

class Xcom_Mmp_Model_ChannelTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Model_Channel */
    protected $_object;

    public function setUp()
    {
        $this->_object = Mage::getModel('xcom_mmp/channel');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    protected function _expectSessionError($error)
    {
        $session = $this->mockModel('adminhtml/session');
        $session->expects($this->once())
          ->method('addError')
          ->with($error);
    }

    public function testValidate()
    {
        $this->_mockIsChannelNameUnique();
        $this->_object = Mage::getModel('xcom_mmp/channel');

        $this->_object->setName('test');
        $this->_object->setStoreId(1);
        $this->_object->setMpChannelCode('test_code');
        $this->_object->setAccountId(1);
        $this->_object->setId(1);

        $this->assertInstanceOf(get_class($this->_object), $this->_object->validate());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testValidateChannelNameNotEmpty()
    {
        $this->_mockIsChannelNameUnique();
        $this->_object->setName('');
        $this->_object->setStoreId(1);
        $this->_object->setMpChannelCode('test');
        $this->_object->setId(1);
        $this->_object->setAccountId(1);
        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testValidateChannel_StoreId()
    {
        $this->_mockIsChannelNameUnique();
        $this->_object->setName('unique_test_' . rand(2000, 5000));
        $this->_object->setMpChannelCode('unique_test_' . rand(2000, 5000));
        $this->_object->setId(1);
        $this->_object->setAccountId(1);
        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testValidateChannel_SiteCodeNotEmpty()
    {
        $this->_mockIsChannelNameUnique();
        $this->_object->setName('unique_test_' . rand(2000, 5000));
        $this->_object->setStoreId(1);
        $this->_object->setSiteCode('');
        $this->_object->setId(1);
        $this->_object->setAccountId(1);
        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testValidateChannel_DuplicateName()
    {
        $resourceMock = $this->_mockIsChannelNameUnique(false);
        $resourceMock->expects($this->once())
            ->method('isChannelStoreSiteUnique')
            ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
            ->will($this->returnValue(true));
        $this->_object = Mage::getModel('xcom_mmp/channel');

        $this->_object->setName('test');
        $this->_object->setStoreId(1);
        $this->_object->setMpChannelCode('test_code');
        $this->_object->setAccountId(1);
        $this->_object->setId(0);

        $this->_object->validate();
    }

    protected function _mockIsChannelNameUnique($result = true)
    {
        $mock = $this->mockResource('xcom_mmp/channel');
        $mock->expects($this->once())
            ->method('isChannelNameUnique')
            ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
            ->will($this->returnValue($result));
        return $mock;
    }
    /**
     * The Channel with such combination of Store View, eBay Site and eBay Account already exists
     *
     * @expectedException Mage_Core_Exception
     * @return void
     */
    public function testValidateChannel_WrongStoreSite()
    {
        $resourceMock = $this->_mockIsChannelNameUnique();
        $resourceMock->expects($this->once())
          ->method('isChannelStoreSiteUnique')
          ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
          ->will($this->returnValue(false));

        $this->_object = Mage::getModel('xcom_mmp/channel');

        $this->_object->setName('test');
        $this->_object->setStoreId(1);
        $this->_object->setMpChannelCode('test_code');
        $this->_object->setAccountId(1);
        $this->_object->setId(0);

        $this->_object->validate();
    }

    public function testValidateIsChannelStoreViewDiffers()
    {
        $resourceMock = $this->_mockIsChannelNameUnique();
        $resourceMock->expects($this->once())
          ->method('isChannelStoreSiteUnique')
          ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
          ->will($this->returnValue(true));
        $resourceMock->expects($this->once())
          ->method('isChannelStoreViewDiffers')
          ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
          ->will($this->returnValue(false));

        $this->_object = Mage::getModel('xcom_mmp/channel');

        $this->_object->setId(0);
        $this->_object->setName('unique_test_' . rand(2000, 5000));
        $this->_object->setStoreId(1);
        $this->_object->setSiteCode('test site code');
        $this->_object->setAccountId(1);

        $result = $this->_object->validate();
        $this->assertInstanceOf(get_class($this->_object), $result);
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage You already have a StoreView associated with this eBay Account and eBay Site combination. Please select a different eBay Account Or eBay Site.
     */
    public function testValidateIsChannelStoreViewDiffersError()
    {
        $resourceMock = $this->_mockIsChannelNameUnique();
        $resourceMock->expects($this->once())
          ->method('isChannelStoreSiteUnique')
          ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
          ->will($this->returnValue(true));
        $resourceMock->expects($this->once())
          ->method('isChannelStoreViewDiffers')
          ->with($this->isInstanceOf('Xcom_Mmp_Model_Channel'))
          ->will($this->returnValue(true));

        $this->_object = Mage::getModel('xcom_mmp/channel');

        $this->_object->setId(0);
        $this->_object->setName('unique_test_' . rand(2000, 5000));
        $this->_object->setStoreId(1);
        $this->_object->setSiteCode('test site code');
        $this->_object->setAccountId(1);

        $this->_object->validate();
    }

    public function testGetXaccountId()
    {
        $objectMock = $this->_mockObjectGetAccount(new Varien_Object(array('xaccount_id' => 'test_1')));
        $this->assertEquals('test_1', $objectMock->getXaccountId());
    }


    public function testGetAuthEnvironment()
    {
        $objectMock = $this->_mockObjectGetAccount(new Varien_Object(array('environment_value' => 'sandbox_test')));
        $this->assertEquals('sandbox_test', $objectMock->getAuthEnvironment());
    }

    /**
     * @param  $returnValue
     * @return Xcom_Mmp_Model_Channel
     */
    protected function _mockObjectGetAccount($returnValue)
    {
        $objectMock = $this->getMock(get_class($this->_object), array('getAccount'));
        $objectMock->expects($this->once())
            ->method('getAccount')
            ->will($this->returnValue($returnValue));
        return $objectMock;
    }

    public function testGetAccountModelClass()
    {
        $result = $this->_object->getAccountModelClass();
        $this->assertEquals('xcom_mmp/account', $result);
    }
}



class Xcom_Mmp_Model_Channel_Mock extends Xcom_Mmp_Model_Channel
{
    public function getAuthEnvironment()
    {
        return 'production';
    }
}

class Xcom_Mock_Model_Account extends Xcom_Mmp_Model_Account
{
}
