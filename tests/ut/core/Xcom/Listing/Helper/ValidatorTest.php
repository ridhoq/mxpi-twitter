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
class Xcom_Listing_Helper_ValidatorTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Listing_Helper_Validator
     */
    protected $_object     = null;
    protected $_instanceOf = 'Xcom_Listing_Helper_Validator';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Listing_Helper_Validator();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    /**
     * @param string $field
     * @expectedException Mage_Core_Exception
     * @dataProvider validationFieldProvider
     */
    public function testValidateException($field)
    {
        $data = $this->_prepareValidationData($field);
        $this->_object->setListing(new Xcom_Listing_Model_Listing($data));
        $this->_object->validateFields();
    }

    public function validationFieldProvider()
    {
        return array(
            array('category_id'),
            array('price_type'),
            array('price_value'),
            array('qty_value_type'),
            array('qty_value'),
            array('policy_id')
        );
    }

    protected function _prepareValidationData($excludeField = null)
    {
        $data = array(
            'category_id' => 'test_category_id',
            'price_type' => 'test_price_type',
            'price_value' => 'test_price_value',
            'price_value_type' => 'test_price_value_type',
            'qty_value_type' => 'test_qty_value_type',
            'qty_value' => 'test_qty_value',
            'policy_id' => 'test_policy_id'
        );
        if (null !== $excludeField && isset($data[$excludeField])) {
            unset($data[$excludeField]);
        }
        return $data;
    }

    public function testValidate()
    {
        $this->_object->setListing(new Xcom_Listing_Model_Listing($this->_prepareValidationData()));
        $this->assertInstanceOf($this->_instanceOf, $this->_object->validateFields());
    }

    /**
     * @param array $data
     * @expectedException Mage_Core_Exception
     * @dataProvider validateOptionalFieldsExceptionProvider
     */
    public function testValidateOptionalFieldsException($data)
    {
        $this->_object->setListing(new Xcom_Listing_Model_Listing($data));
        $this->_object->validateOptionalFields();
    }

    public function validateOptionalFieldsExceptionProvider()
    {
        return array(
            array(array('policy_id' => '')),
            array(array('policy_id' => 1, 'category_id' => '')),
            array(array('policy_id' => 1, 'category_id' => 1, 'price_type' => 'test')),
            array(array('policy_id' => 1, 'category_id' => 1,
                'price_type' => 'test', 'price_value' => '123', 'price_value_type' => '123', 'qty_value' => 123)),
        );
    }

    /**
     * @param array $data
     * @dataProvider validateOptionalFieldsProvider
     */
    public function testValidateOptionalFields($data)
    {
        $this->_object->setListing(new Xcom_Listing_Model_Listing($data));
        $this->assertInstanceOf($this->_instanceOf, $this->_object->validateOptionalFields());
    }

    public function validateOptionalFieldsProvider()
    {
        return array(array(array(
            'policy_id' => 1, 'category_id' => 1,
            'price_type' => 'test', 'price_value' => '123', 'price_value_type' => '123',
            'qty_value' => 123, 'qty_value_type' => 'test'
        )));
    }

    public function testIsPriceChanged()
    {
        $this->_object->setListing(new Xcom_Listing_Model_Listing($this->_prepareValidationData('price_type')));
        $this->assertTrue($this->_object->isPriceChanged());
    }

    public function testIsQtyChanged()
    {
        $this->_object->setListing(new Xcom_Listing_Model_Listing($this->_prepareValidationData('qty_type')));
        $this->assertTrue($this->_object->isQtyChanged());
    }


    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidateEnabledProductException()
    {
        $product = $this->getProduct()->setStatus(false);
        $listingMock = $this->_prepareGetProductsMock($product);
        $this->_object->setListing($listingMock);
        $this->_object->validateProducts();
    }

    public function getProduct()
    {
        $product = $this->getMock('Mage_Catalog_Model_Product', array('save'));
        $product->addData(
            array(
                'id'          => 1,
                'sku'         => 'Product1',
                'status'      => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                'price'       => 100,
                'is_in_stock' => 1,
                'stock_item'  => new Varien_Object(
                    array(
                        'qty' => 10
                    )
                ),
            )
        );
        return $product;
    }

    protected function _prepareGetProductsMock($product, $methods = array())
    {
        $methods = array_merge(array('getProducts'), $methods);
        $objectMock = $this->mockModel('xcom_listing/listing', $methods);
        $objectMock->expects($this->any())
            ->method('getProducts')
            ->will($this->returnValue(array(1 => $product)));
        return $objectMock;
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidateIsInStockProductException()
    {
        $product = $this->getProduct()->setIsInStock(false);
        $listingMock = $this->_prepareGetProductsMock($product);
        $this->_object->setListing($listingMock);
        $this->_object->validateProducts();
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidateIsProductQtyUnavailableException()
    {
        $product = $this->getProduct();
        $product->getStockItem()->setQty(0);
        $listingMock = $this->_prepareGetProductsMock($product);
        $this->_object->setListing($listingMock);
        $this->_object->validateProducts();
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidateCalculateProductQuantityException()
    {
        $product = $this->getProduct();
        $product->getStockItem()->setQty(0);
        $listingMock = $this->_prepareGetProductsMock($product);
        $this->_object->setListing($listingMock);
        $listingMock->setQtyValue(1);
        $listingMock->setQtyValueType('percent');
        $this->_object->validateProducts();
    }

    public function testValidateProducts()
    {
        $product = $this->getProduct();
        $product->setListingPrice(50);
        $product->setListingQty(5);
        $product->setListingCategoryId(3);
        $product->setListingMarketItemId(10);
        $listingMock = $this->_prepareGetProductsMock($product);
        $this->_object->setListing($listingMock);
        $this->assertInstanceOf($this->_instanceOf, $this->_object->validateProducts());
    }

    public function testValidateFieldsMagentoTypePrice()
    {
        $listing = new Xcom_Listing_Model_Listing($this->_prepareValidationData());
        $listing->setPriceType('magentoprice');
        $listing->setPriceValue(null);
        $this->_object->setListing($listing);
        $this->_object->validateFields();
    }
}
