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
 * Creating Admin User
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XCom_Mmp_MappingAttributeTest extends Mage_Selenium_TestCase {

    protected $_initializer_in_progress=false;
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests() {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     */
    protected function assertPreConditions() {
        $this->addParameter('idmagatset', 0);
        $this->addParameter('producttypeid', 0);
        $this->addParameter('idmagatvalue', 0);
        $this->addParameter('targetatid', 0);

    }

    /**
     * <p>Navigate to Channels->Attribute Mapping<-p>
     */
    /**
     * <p>TL-MAGE-74:Attribute Set creation - based on Default</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in fields</p>
     * <p>3. Click button "Save Attribute Set"</p>
     * <p>Expected result</p>
     * <p>Received the message on successful completion of the attribute set creation</p>
     *
     * @test
     */
    public function test_create_set()
    {
        //Data
        $this->navigate('manage_attribute_sets');
        $setData = $this->loadData('attribute_set', null);
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'));
    }

   /**
     * <p>Create mapping for Magento Attribute Set.</p>
     * <p>Steps:</p>
     * <p>1.Go to Channels-Attribute Mapping;</p>
     * <p>2.Press "Map Now" linl for previuosly created set("Sport");</p>
     * <p>3.Select "Tickets" as Target set ;</p>
     * <p>4.Press "Continue" button</p>
     * <p>5.Press "+Add Attribute Mapping" button</p>
     * <p>6.Select Magento Attribute("Condition") and target attribute("Condition *") </p>
     * <p>7.Press "Continue" button<</p>
     * <p>8.Select target attribute value:("New") </p>
     * <p>9.Press "Save" button</p>
     * <p>Expected result:</p>
     * <p>Mapping is saved. Success message presents</p>
     */


     /**
       * <p> Navigate to Channels->Attribute Mapping</p>
       * Verify Initializer
       */
    protected function Precondition() {
        $this->navigate('attribute_set_mapping');
        $init_message1 = $this->isTextPresent('Updating Channel information from X.commerce');
        $init_message2 = $this->isTextPresent('You will not be able to use the Channel functionality ' .
                'until this process is complete.');
        $init_message3 = $this->isTextPresent('This process may take some time.');
        if ($init_message1 == true and $init_message2 == true and $init_message3 == true){
            $this->_initializer_in_progress=true;
        }
    }
    /**
     * <p>Navigate to Manage Attribute Set Mapping page <-p>
     */
    public function test_ManageAttributeSetMapping()
    {
        $this->Precondition();
        if ($this->_initializer_in_progress == true) {
            $this->markTestSkipped('Initializer work is not complete');
            return;
        }
/**
 * obtain producttype id where name=Planers from DB
 */
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $select = Mage::getSingleton('core/resource')->getConnection()
         ->select()->from($resource->getTableName('xcom_mapping/product_type_locale'), $cols = 'locale_id')
            ->where('name = ?' , 'Planers')
            ->where('locale_code = ?', 'en_US');
        $this->addParameter('producttypeid', $this->locale_id);
        $this->locale_id = Mage::getSingleton('core/resource')->getConnection()->fetchOne($select);

        $this->assertElementPresent($this->_getControlXpath('button', 'reset_filter'));
        $this->assertElementPresent($this->_getControlXpath('button', 'search'));
        $this->assertElementPresent($this->_getControlXpath('field', 'attribute_set'));
        $this->assertElementPresent($this->_getControlXpath('dropdown', 'target_system'));
        $this->assertElementPresent($this->_getControlXpath('field', 'target_set'));
        $this->assertElementPresent($this->_getControlXpath('link', 'map_now'));
        $xpath_map_now = $this->_getControlXpath('link', 'map_now');
        $idmagatset = $this->getAttribute($xpath_map_now . '@is_mapped');
        $this->addParameter('idmagatset', $idmagatset);
        $this->clickControl('link', 'map_now');
    }
    /**
     * <p>Navigate to  New Attribute Set Mapping <-p>
     *
     * @depends test_ManageAttributeSetMapping
     */

        public function test_SelectAttributeSetMapping() {
        $this->clickControl('link', 'expand_all', false);
        $this->clickControl('link', 'target_set', false);
        $this->clickControl('button', 'continue');
    }

    /**
     * <p>Navigate to Manage Attribute Mapping <-p>
     *
     * @depends test_SelectAttributeSetMapping
     */
    public function test_ManageAttributeMapping() {
        $this->assertElementPresent($this->_getControlXpath('button', 'back'));
        $this->assertElementPresent($this->_getControlXpath('button', 'add_attribute_mapping'));
        $this->assertElementPresent($this->_getControlXpath('button', 'submit'));
        $this->assertTrue($this->successMessage('pending_attribute'));
        $this->select('id=attribute_id','label=Condition');
        $this->select('id=mapping_attribute_id','label=Condition *');
        $this->clickControl('button', 'add_attribute_mapping');
        $this->assertElementPresent($this->_getControlXpath('checkboxe', 'checkbox_for_delete'));
        $attributevalue = $this->_getControlXpath('link', 'map_now_attribute');
        $idmagatvalue = $this->getAttribute($attributevalue . '@is_mapped');
        $this->addParameter('idmagatvalue', $idmagatvalue);
/**
 * obtain attribute id from link
 */
       $xpath_link = $this->_getControlXpath('link', 'map_now_attribute');
        $link = $this->getAttribute($xpath_link . '@href');
        $map_att_id = $this->mmpHelper()->getIdFromLink($link,'mapping_attribute_id');
        $this->addParameter('map_att_id', $map_att_id);
        $this->clickControl('link', 'map_now_attribute');
   }

    /**
     * <p>Navigate to Attribute Value Mapping <-p>
     *
     *  @depends test_ManageAttributeMapping
     */
    public function test_AttributeValueMapping() {
        $this->assertElementPresent($this->_getControlXpath('button', 'back'));
        $this->assertElementPresent($this->_getControlXpath('button', 'reset'));
        $this->assertElementPresent($this->_getControlXpath('button', 'save'));
        $this->assertElementPresent($this->_getControlXpath('button', 'save_and_continue_edit'));
        $attribute_value_data = $this->loadData('attribute_value');
        $this->fillForm($attribute_value_data);
        $this->saveForm('save');
    }

    /**
     * <p>Save Mapping and Return to Manage Attribute Mapping page <-p>
     *
     *  @depends test_AttributeValueMapping
     */

    public function test_AttributeMapping_saved() {
        $this->assertTrue($this->successMessage('mapping_saved'));
        $this->assertElementPresent($this->_getControlXpath('button', 'back'));
        $this->assertElementPresent($this->_getControlXpath('button', 'add_attribute_mapping'));
        $this->assertElementPresent($this->_getControlXpath('link', 'edit_mapping'));
    }

   /**
     * <p>Delete Magento Attribute Set <-p>
     *
     *  @depends test_create_set
     */

     public function test_delete_set() {
        $this->navigate('manage_attribute_sets');
        $setData = $this->loadData('attribute_set');
        //Steps
        $this->attributeSetHelper()->openAttributeSet($setData['set_name']);
        $this->clickButtonAndConfirm('delete_attribute_set', 'confirmation_for_delete');
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_deleted'));
    }
}
