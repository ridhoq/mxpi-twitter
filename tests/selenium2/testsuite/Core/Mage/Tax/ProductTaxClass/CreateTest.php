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
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product Tax class Core_Mage_creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_ProductTaxClass_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Sales->Tax->Product Tax Classes</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_product_tax_class');
    }

    /**
     * <p>Creating Product Tax class Core_Mage_with required field</p>
     * <p>Steps</p>
     * <p>1. Click "Add New" button </p>
     * <p>2. Fill in required fields</p>
     * <p>3. Click "Save Class" button</p>
     * <p>Expected Result:</p>
     * <p>Product Tax class Core_Mage_created, success message appears</p>
     *
     * @return array $productTaxClassData
     * @test
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $productTaxClassData = $this->loadData('new_product_tax_class');
        //Steps
        $this->taxHelper()->createTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_class');
        $this->taxHelper()->openTaxItem($productTaxClassData, 'product_class');
        $this->assertTrue($this->verifyForm($productTaxClassData), $this->getParsedMessages());

        return $productTaxClassData;
    }

    /**
     * <p>Creating Product Tax class Core_Mage_with name that exists</p>
     * <p>Steps</p>
     * <p>1. Click "Add New" button </p>
     * <p>2. Fill in class Core_Mage_Name with value that exists</p>
     * <p>3. Click "Save Class" button</p>
     * <p>Expected Result:</p>
     * <p>Product Tax class Core_Mage_should not be created, error message appears</p>
     *
     * @depends withRequiredFieldsOnly
     * @param array $productTaxClassData
     * @test
     */
    public function withNameThatAlreadyExists($productTaxClassData)
    {
        //Steps
        $this->taxHelper()->createTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('error', 'tax_class_exists');
    }

    /**
     * <p>Creating Product Tax class Core_Mage_with empty name</p>
     * <p>Steps</p>
     * <p>1. Click "Add New" button </p>
     * <p>2. Leave class Core_Mage_Name empty</p>
     * <p>3. Click "Save Class" button</p>
     * <p>Expected Result:</p>
     * <p>Product Tax class Core_Mage_should not be created, error message appears</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withEmptyName()
    {
        //Data
        $productTaxClassData = $this->loadData('new_product_tax_class', array('product_class_name' => ''));
        //Steps
        $this->taxHelper()->createTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('error', 'empty_class_name');
    }

    /**
     * Fails because of MAGE-5237
     * <p>Creating a new Product Tax class Core_Mage_with special values (long, special chars).</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Class"</p>
     * <p>4. Open the Tax Class</p>
     * <p>Expected result:</p>
     * <p>All fields has the same values.</p>
     *
     * @dataProvider withSpecialValuesDataProvider
     * @param array $specialValue
     * @test
     *
     * @group skip_due_to_bug
     */
    public function withSpecialValues($specialValue)
    {
        //Data
        $productTaxClassData = $this->loadData('new_product_tax_class', array('product_class_name' => $specialValue));
        //Steps
        $this->taxHelper()->createTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_class');
        //Steps
        $this->taxHelper()->openTaxItem($productTaxClassData, 'product_class');
        //Verifying
        $this->assertTrue($this->verifyForm($productTaxClassData), $this->getParsedMessages());
    }

    public function withSpecialValuesDataProvider()
    {
        return array(
            array($this->generate('string', 255)),
            array($this->generate('string', 50, ':punct:'))
        );
    }
}