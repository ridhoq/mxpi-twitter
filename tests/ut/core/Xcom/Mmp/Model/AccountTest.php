<?php

class Xcom_Mmp_Model_AccountTest extends Xcom_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Model_Account();
    }

    public function testValidate()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. Auth ID is required.
     *
     * @return void
     */
    public function testValidateEmptyAuthId()
    {
        $this->_object->setData('auth_id', '');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. xAccount ID is required.
     *
     * @return void
     */
    public function testValidateEmptyXaccountId()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', '');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. Auth ID is required.
     *
     * @return void
     */
    public function testValidateNullAuthId()
    {
        $this->_object->setData('auth_id', null);
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }
    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. xAccount ID is required.
     *
     * @return void
     */
    public function testValidateNullXaccountId()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', null);
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. User ID is required.
     *
     * @return void
     */
    public function testValidateEmptyUserId()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', '');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. User ID is required.
     *
     * @return void
     */
    public function testValidateNullUserId()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', null);
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. Validated At is required.
     *
     * @return void
     */
    public function testValidateEmptyValidatedAt()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', '');
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. Validated At is required.
     *
     * @return void
     */
    public function testValidateNullValidatedAt()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', null);
        $this->_object->setData('environment', 'test_environment');

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. Environment is required.
     *
     * @return void
     */
    public function testValidateEmptyEnvironment()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', 0);

        $this->_object->validate();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong account data. Environment is required.
     *
     * @return void
     */
    public function testValidateNullEnvironment()
    {
        $this->_object->setData('auth_id', 'test_auth_id');
        $this->_object->setData('xaccount_id', 'test_xaccount_id');
        $this->_object->setData('user_id', 'test_user_id');
        $this->_object->setData('validated_at', 'test_validated_at');
        $this->_object->setData('environment', null);

        $this->_object->validate();
    }

}
