<?php
class Xcom_Mapping_Helper_From_AttributeTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Helper_Form_Attribute */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Helper_Form_Attribute();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Mapping_Helper_Form_Attribute', $this->_object);
    }
}
