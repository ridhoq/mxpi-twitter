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
 * Attribute Set creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsStaticBlocks_CreateTest extends Mage_Selenium_TestCase
{
    protected $_blockToBeDeleted = array();

    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore('generic_store_view', 'store_view');
        $this->assertMessagePresent('success', 'success_saved_store_view');
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to CMS -> Static Blocks</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_cms_static_blocks');
        $this->addParameter('id', '0');
    }

    protected function tearDownAfterTest()
    {
        if ($this->_blockToBeDeleted) {
            $this->loginAdminUser();
            $this->navigate('manage_cms_static_blocks');
            $this->cmsStaticBlocksHelper()->deleteStaticBlock($this->_blockToBeDeleted);
            $this->_blockToBeDeleted = array();
        }
    }

    /**
     * <p>Creating a new static block</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Block"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Block"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the block has been saved.</p>
     *
     * @return array
     * @test
     * @TestlinkId TL-MAGE-3129
     */
    public function createNewWithReqField()
    {
        //Data
        $setData = $this->loadData('new_static_block');
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_block');

        return $setData;
    }

    /**
     * <p>Creating a new static block with existing XML identifier.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Block"</p>
     * <p>2. Fill in the fields, enter already existing identifier</p>
     * <p>3. Click button "Save Block"</p>
     * <p>Expected result:</p>
     * <p>Received an error message about already existing identifier.</p>
     *
     * @param array $setData
     *
     * @test
     * @depends createNewWithReqField.
     * @TestlinkId	TL-MAGE-3131
     */
    public function withExistingIdentifier($setData)
    {
        $this->_blockToBeDeleted = $this->loadData('search_static_block',
                array('filter_block_identifier' => $setData['block_identifier']));
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        $this->assertMessagePresent('error', 'already_existing_identifier');
    }

    /**
     * <p>Creating a new static block</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Block"</p>
     * <p>2. Fill in the fields, add all types of widgets</p>
     * <p>3. Click button "Save Block"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the block has been saved.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3224
     */
    public function createNewWithAllWidgets()
    {
        //Data
        $setData = $this->loadData('static_block_with_all_widgets');
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_block');
        $this->_blockToBeDeleted = $this->loadData('search_static_block',
                array('filter_block_identifier' => $setData['block_identifier']));
    }

    /**
     * <p>Creating a new static block with special values (long, special chars).</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Block"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Block"</p>
     * <p>4. Open the block</p>
     * <p>Expected result:</p>
     * <p>All fields has the same values.</p>
     *
     * @param array $specialValue
     *
     * @test
     * @dataProvider withSpecialValuesDataProvider
     * @depends createNewWithReqField
     * @TestlinkId    TL-MAGE-3227
     */
    public function withSpecialValues(array $specialValue)
    {
        //Data
        $setData = $this->loadData('new_static_block', $specialValue);
        $blockToOpen = $this->loadData('search_static_block',
                array('filter_block_identifier' => $setData['block_identifier']));
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_block');
        $this->_blockToBeDeleted = $this->loadData('search_static_block',
                array('filter_block_identifier' => $setData['block_identifier']));
        //Steps
        $this->cmsStaticBlocksHelper()->openStaticBlock($blockToOpen);
        //Verifying
        $this->assertTrue($this->verifyForm($setData), $this->getParsedMessages());
    }

    public function withSpecialValuesDataProvider()
    {
        return array(
            array(array('block_title' => $this->generate('string', 255, ':alpha:'))),
            array(array('block_identifier' => $this->generate('string', 255, ':alpha:'))),
            array(array('block_title' => $this->generate('string', 50, ':punct:'))),
        );
    }

    /**
     * <p>Creating a new static block with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Block;"</p>
     * <p>2. Fill in the fields, but leave one required field empty;</p>
     * <p>3. Click button "Save Block".</p>
     * <p>Expected result:</p>
     * <p>Received error message "This is a required field."</p>
     *
     * @param string $emptyField
     * @param string $fieldType
     *
     * @test
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @TestlinkId TL-MAGE-3225
     */
    public function withEmptyRequiredFields($emptyField, $fieldType)
    {
        //Data
        $setData = $this->loadData('new_static_block', array($emptyField => '%noValue%'));
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        if ($emptyField == 'content') {
            $emptyField = 'simple_editor_disabled';
        }
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('block_title', 'field'),
            array('block_identifier', 'field'),
            array('store_view', 'multiselect'),
            array('content', 'field')
        );
    }

    /**
     * <p>Creating a new static block with invalid XML identifier.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Block"</p>
     * <p>2. Fill in the fields, enter invalid XML identifier</p>
     * <p>3. Click button "Save Block"</p>
     * <p>Expected result:</p>
     * <p>Received an error message about invalid XML identifier.</p>
     *
     * @param string $invalidValue
     *
     * @test
     * @dataProvider withInvalidXmlIdentifierDataProvider
     * @TestlinkId TL-MAGE-3226
     */
    public function withInvalidXmlIdentifier($invalidValue)
    {
        //Data
        $setData = $this->loadData('new_static_block', array('block_identifier' => $invalidValue));
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        $this->assertMessagePresent('validation', 'specify_valid_xml_identifier');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withInvalidXmlIdentifierDataProvider()
    {
        return array(
            array($this->generate('string', 12, ':digit:')),
            array($this->generate('string', 12, ':punct:')),
            array("with_a_space " . $this->generate('string', 12, ':alpha:'))
        );
    }
}