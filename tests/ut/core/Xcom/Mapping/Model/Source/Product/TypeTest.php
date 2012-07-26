<?php

class Xcom_Mapping_Model_Source_ProductTest extends Xcom_TestCase
{

    /** @var Xcom_Mapping_Model_Source_Product */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Source_Product_Type';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('xcom_mapping/source_product_type');
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testGetTree()
    {
        $data = $this->_getTestData_1();
        $this->_object->setData($data);
        $result = $this->_object->getTree();

        $standard = array(
            '1' => array(
                'id' => '1',
                'name' => 'Clothing, Shoes & Accessories',
                'parent' => '0',
                'type' => 'class',
                'level' => '0',
                'children' => array(
                    '2' => array(
                        'id' => '2',
                        'name' => 'Second class',
                        'parent' => '1',
                        'type' => 'class',
                        'level' => '1',
                        'children' => array(
                            '1_type' => array(
                                'id' => '1',
                                'name' => 'Women\'s Boots',
                                'parent' => '2',
                                'type' => 'type',
                                'level' => '2',
                                'children' => array()
                            ),
                        )
                    ),
                    '3' => array(
                        'id' => '3',
                        'name' => 'Business class',
                        'parent' => '1',
                        'type' => 'class',
                        'level' => '1',
                        'children' => array(
                            '1_type' => array(
                                'id' => '1',
                                'name' => 'Women\'s Boots',
                                'parent' => '3',
                                'type' => 'type',
                                'level' => '2',
                                'children' => array()
                            )
                        )
                    ),
                )
            ),
            '4' => array(
                'id' => '4',
                'name' => 'Econom class',
                'parent' => '0',
                'type' => 'class',
                'level' => '0',
                'children' => array()
            ),
        );

        $this->assertEquals($standard, $result);
    }


    public function testToOptionArray()
    {
        $data = $this->_getTestData_1();
        $this->_object->setData($data);
        $result = $this->_object->toOptionArray();

        $standard = array(
            array(
                'label' => 'Clothing, Shoes & Accessories',
                'value' => array(
                    array(
                        'label' => 'Second class',
                        'value' => array(
                            array(
                                'label' => 'Women\'s Boots',
                                'value' => '1',
                                'active-item'   => true,
                            ),
                        )
                    ),
                    array(
                        'label' => 'Business class',
                        'value' => array(
                            array(
                                'label' => 'Women\'s Boots',
                                'value' => '1',
                                'active-item' => true,
                            )
                        )
                    ),
                )
            ),
            array(
                'label' => 'Econom class',
                'value' => array()
            ),
        );
        $this->assertEquals($standard, $result);
    }

    protected function _getTestData_1()
    {
        $data = array(
            array(
                'id' => '1',
                'name' => 'Clothing, Shoes & Accessories',
                'parent' => '0',
                'type' => 'class',
            ),
            array(
                'id' => '2',
                'name' => 'Second class',
                'parent' => '1',
                'type' => 'class',
            ),
            array(
                'id' => '3',
                'name' => 'Business class',
                'parent' => '1',
                'type' => 'class',
            ),
            array(
                'id' => '4',
                'name' => 'Econom class',
                'parent' => '0',
                'type' => 'class',
            ),
            array(
                'id' => '1',
                'name' => 'Women\'s Boots',
                'parent' => '2',
                'type' => 'type',
            ),
            array(
                'id' => '1',
                'name' => 'Women\'s Boots',
                'parent' => '3',
                'type' => 'type',
            )
        );
        return $data;
    }
}
