<?php
class Xcom_ChannelGroup_Model_ChanneltypeTest extends Xcom_TestCase
{
    /** @var $_object Xcom_ChannelGroup_Model_Config_Channeltype */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('xcom_channelgroup/config_channeltype');
        $configGroup = array(
            'group1' => array(
                'title' => 'group1Title',
                'sort_order' => 10,
            ),
        );
        $this->mockStoreConfig(Xcom_ChannelGroup_Model_Config_Channeltype::XML_PATH_XCOM_CHANNEL_GROUP, $configGroup);

        $configType = array(
            'type1' => array(
                'group' => 'group1',
                'title' => 'type1Title',
                'module' => '',
                'sort_order' => 10,
            ),
        );
        $this->mockStoreConfig(Xcom_ChannelGroup_Model_Config_Channeltype::XML_PATH_XCOM_CHANNEL_TYPE, $configType);
    }

    public function testGetChannelType()
    {
        $result1 = $this->_object->getChanneltype();
        $this->assertEmpty($result1->getData());
        $this->assertTrue(count($result1->getData()) == 0);

        $result2 = $this->_object->getChanneltype('type1');
        $this->assertEmpty($result1->getData());
        $this->assertTrue(count($result2->getData()) > 0);
        $this->assertEquals('type1Title', $result2->getTitle());
        $this->assertEquals('type1', $result2->getCode());
        $this->assertEquals('type', $result2->getType());
    }

    public function testGetAllTabs()
    {
        $objectMock = $this->mockModel('xcom_channelgroup/config_channeltype', array('isValid'));
        $objectMock->expects($this->any())
          ->method('isValid')
          ->will($this->returnValue(true));

        $items = $objectMock->getAllTabs();
        $this->assertTrue(count($items) == 2);
        $this->assertArrayHasKey('group1', $items);
        $this->assertInstanceOf('Varien_Object', $items['group1']);
        $this->assertEquals('group1Title', $items['group1']->getTitle());
        $this->assertEquals('group1', $items['group1']->getCode());
        $this->assertEquals('group', $items['group1']->getType());

        $this->assertArrayHasKey('type1', $items);
        $this->assertInstanceOf('Varien_Object', $items['type1']);
        $this->assertEquals('type1Title', $items['type1']->getTitle());
        $this->assertEquals('type1', $items['type1']->getCode());
        $this->assertEquals('type', $items['type1']->getType());
    }

    public function testGetAllChannelGroups()
    {
        $objectMock = $this->mockModel('xcom_channelgroup/config_channeltype', array('isValid'));
        $objectMock->expects($this->any())
          ->method('isValid')
          ->will($this->returnValue(true));

        $items = $objectMock->getAllChannelGroups();
        $this->assertTrue(count($items) == 1);
        $this->assertEquals('group1Title', $items['group1']->getTitle());
        $this->assertEquals('group1', $items['group1']->getCode());
        $this->assertEquals('group', $items['group1']->getType());
    }

    public function testGetAllChannelTypes()
    {
        $objectMock = $this->mockModel('xcom_channelgroup/config_channeltype', array('isValid'));
        $objectMock->expects($this->any())
          ->method('isValid')
          ->will($this->returnValue(true));

        $items = $objectMock->getAllChannelTypes();
        $this->assertTrue(count($items) == 1);
        $this->assertEquals('type1Title', $items['type1']->getTitle());
        $this->assertEquals('type1', $items['type1']->getCode());
        $this->assertEquals('type', $items['type1']->getType());
    }

    public function testGetChannelTypesByGroup()
    {
        $result1 = $this->_object->getChannelTypesByGroup();
        $this->assertEmpty($result1);

        $result2 = $this->_object->getChannelTypesByGroup('group1');
        $this->assertNotEmpty($result2);
        $this->assertEquals('type1Title', $result2['type1']->getTitle());
        $this->assertEquals('type1', $result2['type1']->getCode());
        $this->assertEquals('type', $result2['type1']->getType());
    }

    public function testIsValid()
    {
        $testObject = new Varien_Object();

        $result = $this->_object->isValid($testObject);
        $this->assertFalse($result);

        $testObject->setType(Xcom_ChannelGroup_Model_Config_Channeltype::GROUP_ID);
        $result = $this->_object->isValid($testObject);
        $this->assertFalse($result);

        $testObject->setData('title', 'group1Title');
        $result = $this->_object->isValid($testObject);
        $this->assertFalse($result);

        $testObject->setData('code', 'group1');
        $result = $this->_object->isValid($testObject);
        $this->assertTrue($result);

        $testObject->setType(Xcom_ChannelGroup_Model_Config_Channeltype::TYPE_ID);
        $testObject->unsetData('title');
        $testObject->unsetData('code');
        $result = $this->_object->isValid($testObject);
        $this->assertFalse($result);

        $testObject->setData('title', 'type1Title');
        $result = $this->_object->isValid($testObject);
        $this->assertFalse($result);

        $testObject->setData('code', 'type1');
        $testObject->setData('module', 'moduleTest');
        $result = $this->_object->isValid($testObject);
        $this->assertTrue($result);

        $testObject->unsetData('type');
        $result = $this->_object->isValid($testObject);
        $this->assertTrue($result);
    }

    public function testGetDefault()
    {
        $this->mockStoreConfig(Xcom_ChannelGroup_Model_Config_Channeltype::XML_PATH_XCOM_CHANNEL_TYPE_DEFAULT,
            'test_undefined_code');

        $objectMock1 = $this->mockModel('xcom_channelgroup/config_channeltype', array('isValid'));
        $objectMock1->expects($this->any())
          ->method('isValid')
          ->will($this->returnValue(true));

        $result1 = $objectMock1->getDefault();
        $this->assertTrue(is_object($result1));
        $this->assertFalse($result1->hasData());

        $this->mockStoreConfig(Xcom_ChannelGroup_Model_Config_Channeltype::XML_PATH_XCOM_CHANNEL_TYPE_DEFAULT,
            'type1');

        $objectMock2 = $this->mockModel('xcom_channelgroup/config_channeltype', array('isValid'));
        $objectMock2->expects($this->any())
          ->method('isValid')
          ->will($this->returnValue(true));

        $result2 = $objectMock2->getDefault();
        $this->assertTrue(is_object($result2));
        $this->assertTrue($result2->hasData());
        $this->assertEquals('type1Title', $result2->getTitle());
        $this->assertEquals('type1', $result2->getCode());
        $this->assertEquals('type', $result2->getType());
    }
}
