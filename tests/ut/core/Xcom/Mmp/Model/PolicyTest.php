<?php
class Xcom_Mmp_Model_PolicyTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Mmp_Model_Policy
     */
    protected $_object;

    public function setUp()
    {
        $this->_object = Mage::getModel('xcom_mmp/policy');
        parent::setUp();
    }

    /**
     * @dataProvider policyNameProvider
     *
     * @param $policyName
     * @param $channelId
     * @param $policyId
     * @param $result
     */
    public function testIsPolicyNameUnique($policyName, $channelId, $policyId, $result)
    {
        $resource = $this->mockResource('xcom_mmp/policy');
        $resource->expects($this->once())
            ->method('isPolicyNameUnique')
            ->with($this->equalTo($policyName),
                $this->equalTo($channelId),
                $this->equalTo($policyId))
            ->will($this->returnValue($result));

        $this->assertEquals($this->_object->isPolicyNameUnique($policyName, $channelId, $policyId), $result);
    }


    public function policyNameProvider()
    {
        return array(
            array(
                'policyName'    => 'Policy Name',
                'channel_id'    => 1,
                'policy_id'     => null,
                'result'        => true
            ),
            array(
                'policyName'    => 'Policy Name',
                'channel_id'    => 1,
                'policy_id'     => null,
                'result'        => false
            ),
            array(
                'policyName'    => 'Policy Name',
                'channel_id'    => 1,
                'policy_id'     => 1,
                'result'        => true
            )
        );
    }

    public function testSavePolicyShipping()
    {
        $this->_object->setId('test');
        $resourceMock = $this->mockResource('xcom_mmp/policy');
        $resourceMock->expects($this->once())
            ->method('savePolicyShipping')
            ->with($this->equalTo($this->_object));
        $this->assertInstanceOf(get_class($this->_object), $this->_object->savePolicyShipping());
    }
}
