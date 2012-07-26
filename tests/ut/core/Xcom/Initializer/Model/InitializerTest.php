<?php
class Xcom_Initializer_Model_InitializerTest extends Xcom_TestCase
{
    /* @var Xcom_Initializer_Model_Initializer */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Initializer_Model_Initializer();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    /**
     * @dataProvider dataProviderIsAllowed
     * @param string $config
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param array  $issuersWithJobs
     * @param bool   $isAllowed
     * @param string $urn
     */
    public function testIsAllowed($config, $module, $controller, $action, $issuersWithJobs, $isAllowed, $urn = '')
    {
        $configMock = $this->getMock('Mage_Core_Model_Config', array('getNode'));

        $configMock
            ->expects($this->any())
            ->method('getNode')
            ->with($this->equalTo('default/xcom/initializer_acl'))
            ->will($this->returnValue(new Varien_Simplexml_Element($config)));

        $request = new Mage_Core_Controller_Request_Http();
        $request->setControllerModule($module)->setControllerName($controller)->setActionName($action);
        $request->setRequestUri($urn)->setPathInfo();

        $fabricHelper = $this->mockHelper('xcom_xfabric', array('getNodeByXpath'));
        $fabricHelper->expects($this->any())->method('getNodeByXpath')->will($this->returnValue(1));

        $jobResource = $this->mockResource('xcom_initializer/job', array('hasJobsLeft', 'isDataCollected'));
        $jobResource->expects($this->any())
            ->method('isDataCollected')->will($this->returnValue(empty($issuersWithJobs)));

        if (empty($issuersWithJobs)) {
            $jobResource->expects($this->never())->method('hasJobsLeft');
        } else {
            $i = 1;
            foreach (array('xcom_mapping', 'xcom_other') as $issuer) {
                $jobResource
                    ->expects($this->at($i))
                    ->method('hasJobsLeft')
                    ->with($this->equalTo($issuer))
                    ->will($this->returnValue((int)in_array($issuer, $issuersWithJobs)));
                $i++;
            }
        }

        Mage::setConfigMock($configMock);
        $result = $this->_object->isAllowed($request);
        Mage::setUseMockConfig(false);
        $this->assertEquals($isAllowed, $result);
    }

    public function dataProviderIsAllowed()
    {
        $initializerAclConfig = <<<END
<initializer_acl>
    <xcom_mapping>
        <denied>
            <namespace>
                <a>Xcom</a>
            </namespace>
            <controller>
                <a>Core_Module/denied_controller</a>
            </controller>
        </denied>
        <allowed>
            <controller>
                <a>Xcom_Module/allowed_controller</a>
            </controller>
            <action>
                <a>*/*::allowedAnywhere</a>
                <b>*/denied_controller::allowedForController</b>
                <c>Xcom_Module/*::allowedActionForModule</c>
                <d>Core_Module/denied_controller::allowedActionForModuleAndController</d>
            </action>
        </allowed>
    </xcom_mapping>
    <xcom_other>
        <denied>
            <action>
                <a>*/*::allowedAnywhere</a>
            </action>
            <urn>
                <a>/admin/controller</a>
                <b>/frontend/controller/action</b>
            </urn>
        </denied>
        <allowed>
            <urn>
                <a>/admin/controller/action/</a>
                <b>frontend/controller/</b>
            </urn>
        </allowed>
    </xcom_other>
</initializer_acl>
END;

        return array(
            array($initializerAclConfig, 'Xcom_Xfabric', 'some_controller', 'someaction', array('xcom_mapping'), false), // 0
            array($initializerAclConfig, 'Mage_Core', 'some_controller', 'someaction', array('xcom_mapping'), true), // 1
            array($initializerAclConfig, 'Xcom_Module', 'allowed_controller', 'anyaction', array('xcom_mapping'), true), // 2
            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'anyaction', array('xcom_mapping'), false), // 3
            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'anyaction', array(), true), // 4
            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'allowedAnywhere', array('xcom_mapping'), true), // 5
            array($initializerAclConfig, 'Any_Module', 'denied_controller', 'allowedForController', array('xcom_mapping'), true), // 6
            array($initializerAclConfig, 'Xcom_Module', 'any_controller', 'allowedActionForModule', array('xcom_mapping'), true), // 7
            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'allowedActionForModuleAndController', array('xcom_mapping'), true), // 8

            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'anyaction', array('xcom_other'), true), // 9
            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'allowedAnywhere', array('xcom_other'), false), // 10
            array($initializerAclConfig, 'Any_Module', 'any_controller', 'anyaction', array('xcom_other'), false, 'admin/controller'), // 11
            array($initializerAclConfig, 'Any_Module', 'any_controller', 'anyaction', array('xcom_other'), true, 'admin/controller/action'), // 12
            array($initializerAclConfig, 'Any_Module', 'any_controller', 'anyaction', array('xcom_other'), true, 'frontend/controller/someaction'), // 13
            array($initializerAclConfig, 'Any_Module', 'any_controller', 'anyaction', array('xcom_other'), false, 'frontend/controller/action'), // 14

            array($initializerAclConfig, 'xcom_initializer', 'initializer', 'xcomDenied', array('xcom_mapping'), true), // 15
            array($initializerAclConfig, 'Core_Module', 'denied_controller', 'denied', array('xcom_mapping'), true), // 16
        );
    }

    protected function _mockInitializerJobMethods($data = array())
    {
        $resourceModel = $this->mockResource('xcom_initializer/job', array_keys($data));
        foreach ($data as $key => $val) {
            $resourceModel->expects($this->once())
                ->method($key)
                ->will($this->returnValue($val));
        }
        return $resourceModel;
    }

    public function testIsDataCollected()
    {
        $this->_mockInitializerJobMethods(array('isDataCollected' => true));

        $result = $this->_object->isDataCollected();
        $this->assertTrue($result);

        $this->_mockInitializerJobMethods(array('isDataCollected' => false));

        $result = $this->_object->isDataCollected();
        $this->assertFalse($result);
    }
}
