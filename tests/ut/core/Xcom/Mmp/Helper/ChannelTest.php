<?php

class Xcom_Mmp_Helper_ChannelTest extends Xcom_TestCase
{
    /** @var Xcom_Mmp_Helper_Data */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Helper_Channel();
    }

    public function mockChannelCollection($result)
    {
        $this->_filterIsActive($result);
        $mock = $this->mockCollection(
            'xcom_mmp/channel',
            $result,
            array('addFieldToFilter', 'setOrder', 'count', 'load', 'getIterator', 'getItems')
        );
        $mock->expects($this->once())
            ->method('addFieldToFilter')
            ->with($this->equalTo('is_active'), $this->equalTo(array('eq' => 1)))
            ->will($this->returnValue($mock));
        $mock->expects($this->once())
            ->method('setOrder')
            ->with($this->equalTo('channeltype_code'), $this->equalTo(Zend_Db_Select::SQL_DESC))
            ->will($this->returnValue($mock));
        $mock->expects($this->any())
            ->method('count')
            ->will($this->returnValue(count($result)));

        return $mock;
    }

    public function mockchannelTypes($data)
    {
        $mock = $this->mockModel('xcom_channelgroup/config_channeltype')
            ->expects($this->once())
            ->method('getAllChannelTypes')
            ->will($this->returnValue($data));
        return $mock;
    }

    public function generateExpectAndResult($type)
    {
        $expect = array();
        $result = array();
        $subarray = array();
        $codes = array('France', 'United States', 'Uk', 'Fr');

        $iterator = 1;

        $result[] = array('label' => 'Magento Website', 'value' => 0);
        foreach ($type as $key => $itemType) {
            foreach ($codes as $code) {
                $iterator++;
                $isActive = ($iterator % 2 === 0);
                $expect[] = new Varien_Object(array(
                    'channel_id' => $iterator,
                    'name' =>  $code,
                    'channeltype_code' =>$itemType,
                    'is_active' => $isActive,
                ));
                if ($isActive) {
                    $subarray[] = array(
                        'label' => $code,
                        'value' => $iterator
                    );
                }
            }
            $result[] = array(
                'label' => $key,
                'value' => $subarray
            );
            $subarray = array();
        }

        return array(
            'data' => $expect,
            'result' => $result
        );
    }

    /**
     * @dataProvider providerChannel
     */
    public function testGenerateChannelOptions($channelTypesData, $channelTypes)
    {
        $this->mockchannelTypes($channelTypesData);
        $array = $this->generateExpectAndResult($channelTypes);
        $this->mockChannelCollection($array['data']);
        $result = $this->_object->generateChannelOptions();
        $this->assertEquals(
            $array['result'],
            $result,
            "this does not equal to expected result"
        );

    }

    public function testGenerateNoChannelOptions()
    {
        $this->mockChannelCollection(array());
        $result = $this->_object->generateChannelOptions();
        $this->assertEquals(1, count($result));
        $this->assertEquals($result[0], array('label' => 'Magento Website', 'value' => 0),  "expected empty array");
    }

    protected function _filterIsActive(&$arrayData)
    {
        //filter only active channel
        foreach ($arrayData as $key => $channel) {
            if (!$channel->getIsActive()) {
                unset($arrayData[$key]);
            }
        }
    }

    public function providerChannel()
    {
        return array(
            array(
                array(
                    new Varien_Object(array(
                        'code' => 'ebay',
                        'title' => 'eBay'
                    ))
                ),
                array('eBay' => 'ebay'),
            ),
            array(
                array(
                    new Varien_Object(array(
                        'code' => 'amazon',
                        'title' => 'Amazon'
                    )),
                    new Varien_Object(array(
                        'code' => 'ebay',
                        'title' => 'eBay'
                    )),
                ),
                array( 'Amazon' => 'amazon', 'eBay' => 'ebay'),
            ),
        );
    }
}
