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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sub category creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Category_Create_SubCategoryTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to Catalog -> Manage Categories</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
    }

    /**
     * @TODO Temporary workaround(should be deleted)
     */
    protected function tearDownAfterTest()
    {
        $this->navigate('manage_categories', false);
    }

    /**
     * <p>Creating Subcategory with required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add Subcategory" button </p>
     * <p>2. Fill in required fields</p>
     * <p>3. Click "Save Category" button</p>
     * <p>Expected Result:</p>
     * <p>Subcategory created, success message appears</p>
     *
     * @return string
     * @test
     * @TestlinkId TL-MAGE-3645
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $categoryData = $this->loadData('sub_category_required');
        //Steps
        $this->categoryHelper()->createCategory($categoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();

        return $categoryData['parent_category'] . '/' . $categoryData['name'];
    }

    /**
     * <p>Creating Sub Category with all fields filling</p>
     * <p>Steps</p>
     * <p>1. Click "Add Root Category" button </p>
     * <p>2. Fill in required fields</p>
     * <p>3. Click "Save Category" button</p>
     * <p>Expected Result:</p>
     * <p>Root Category created, success message appears</p>
     *
     * @param string $rooCat
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3642
     */
    public function rootCategoryWithAllFields($rooCat)
    {
        //Data
        $categoryData = $this->loadData('sub_category_all', array('parent_category'=> $rooCat));
        //Steps
        $this->categoryHelper()->createCategory($categoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
    }

    /**
     * <p>Creating Subcategory with required fields empty</p>
     * <p>Steps</p>
     * <p>1. Click "Add Subcategory" button </p>
     * <p>2. Fill in necessary fields, leave required fields empty</p>
     * <p>3. Click "Save Category" button</p>
     * <p>Expected Result:</p>
     * <p>Subcategory not created, error message appears</p>
     *
     * @param string $emptyField
     * @param string $fieldType
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3644
     */
    public function withRequiredFieldsEmpty($emptyField, $fieldType)
    {
        //Data
        $categoryData = $this->loadData('sub_category_required', array($emptyField => '%noValue%'));
        //Steps
        $this->categoryHelper()->createCategory($categoryData);
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('name', 'field'),
            array('available_product_listing', 'multiselect')
        );
    }

    /**
     * <p>Creating Subcategory with special characters</p>
     * <p>Steps</p>
     * <p>1. Click "Add Subcategory" button </p>
     * <p>2. Fill in required fields with special characters</p>
     * <p>3. Click "Save Category" button</p>
     * <p>Expected Result:</p>
     * <p>Subcategory created, success message appears</p>
     *
     * @param string $rooCat
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3644
     */
    public function withSpecialCharacters($rooCat)
    {
        //Data
        $categoryData = $this->loadData('sub_category_required',
                                        array('name'          => $this->generate('string', 32, ':punct:'),
                                             'parent_category'=> $rooCat));
        //Steps
        $this->categoryHelper()->createCategory($categoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
    }

    /**
     * <p>Creating Subcategory with long values in required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add Subcategory" button </p>
     * <p>2. Fill in required fields</p>
     * <p>3. Click "Save Category" button</p>
     * <p>Expected Result:</p>
     * <p>Subcategory created, success message appears</p>
     *
     * @param string $rooCat
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3644
     */
    public function withLongValues($rooCat)
    {
        //Data
        $categoryData = $this->loadData('sub_category_required',
                                        array('name'          => $this->generate('string', 255, ':alnum:'),
                                             'parent_category'=> $rooCat));
        //Steps
        $this->categoryHelper()->createCategory($categoryData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();
    }

    /**
     * <p>Creating nested Subcategory with required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add Subcategory" button </p>
     * <p>2.Select existing "Category Path"</p>
     * <p>3. Fill in required fields</p>
     * <p>4. Click "Save Category" button</p>
     * <p>Expected Result:</p>
     * <p>Subcategory created, success message appears</p>
     *
     * @param string $rooCat
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-3641
     */
    public function nestedSubCategory($rooCat)
    {
        for ($i = 1; $i <= 10; $i++) {
            //Data
            $categoryData = $this->loadData('sub_category_required', array('parent_category'=> $rooCat));
            //Steps
            $this->categoryHelper()->createCategory($categoryData);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_category');
            $this->categoryHelper()->checkCategoriesPage();
            //Steps
            $rooCat .= '/' . $categoryData['name'];
        }
    }

}
