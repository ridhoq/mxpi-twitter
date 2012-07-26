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
 * Poll creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsPolls_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     * <p>Close all opened Polls</p>
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
     * <p>Navigate to CMS -> Polls</p>
     *
     */
    protected function assertPreConditions()
    {
        $this->admin();
        $this->navigate('poll_manager');
        $this->cmsPollsHelper()->closeAllPolls();
        $this->addParameter('id', '0');
    }

    /**
     * <p>Creating a new Poll</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Poll"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Poll has been saved.</p>
     * <p>Poll is displayed on Homepage</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3217
     */
    public function createNew()
    {
        //Data
        $pollData = $this->loadData('poll_open');
        $name = $pollData['poll_question'];
        $searchPollData = $this->loadData('search_poll', array('filter_question' => $name));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->cmsPollsHelper()->openPoll($searchPollData);
        $this->cmsPollsHelper()->checkPollData($pollData);
        $this->frontend();
        $this->assertTrue($this->cmsPollsHelper()->frontCheckPoll($name),
                'There is no ' . $name . ' poll on home page');
    }

    /**
     * <p>Creating a new poll with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields, but leave one required field empty;</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>Expected result:</p>
     * <p>Received error message "This is a required field."</p>
     *
     * @param string $emptyField
     * @param string $fieldType
     *
     * @test
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @TestlinkId	TL-MAGE-3220
     */
    public function withEmptyRequiredFields($emptyField, $fieldType)
    {
        //Data
        $pollData = $this->loadData('poll_empty_required', array($emptyField => ''));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('poll_question', 'field'),
            array('visible_in', 'multiselect'),
            array('answer_title', 'field'),
            array('votes_count', 'field')
        );
    }

    /**
     * <p>Creating a new poll without answers</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields, but don't add any answer;</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>Expected result:</p>
     * <p>Received error message "Please, add some answers to this poll first."</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3221
     */
    public function withoutAnswer()
    {
        //Data
        $pollData = $this->loadData('poll_open', array('assigned_answers_set' => '%noValue%'));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('error', 'add_answers');
    }

    /**
     * <p>Creating a new poll with few identical answers</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields, but add few identical answers;</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>Expected result:</p>
     * <p>Received error message "Your answers contain duplicates."</p>
     *
     * @test
     * @depends createNew
     * @TestlinkId	TL-MAGE-3218
     */
    public function identicalAnswer()
    {
        //Data
        $pollData = $this->loadData('poll_open', array('answer_title' => 'duplicate'));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('validation', 'dublicate_poll_answer');
    }

    /**
     * <p>Closed poll is not displayed</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll".</p>
     * <p>2. Fill in the fields, set state to "Close".</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>4. Check poll on Homepage.</p>
     * <p>Expected result:</p>
     * <p>Poll should not be displayed.</p>
     *
     * @test
     * @depends createNew
     * @TestlinkId	TL-MAGE-3216
     */
    public function closedIsNotDispalyed()
    {
        //Data
        $pollData = $this->loadData('poll_open');
        $name = $pollData['poll_question'];
        $searchPollData = $this->loadData('search_poll', array('filter_question' => $name));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->frontend();
        $this->assertTrue($this->cmsPollsHelper()->frontCheckPoll($name),
                'There is no ' . $name . ' poll on home page');
        //Steps
        $this->admin();
        $this->navigate('poll_manager');
        $this->cmsPollsHelper()->setPollState($searchPollData, 'Closed');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->frontend();
        $this->assertFalse($this->cmsPollsHelper()->frontCheckPoll($name),
                'There is ' . $name . ' poll on home page');
    }

    /**
     * <p>Vote a poll</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields.</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>4. Vote a poll on Homepage.</p>
     * <p>5. Re-open Homepage.</p>
     * <p>Expected result:</p>
     * <p>Poll should not be available for vote</p>
     *
     * @test
     * @depends createNew
     * @TestlinkId TL-MAGE-3219
     */
    public function votePoll()
    {
        //Data
        $pollData = $this->loadData('poll_open');
        $name = $pollData['poll_question'];
        $searchPollData = $this->loadData('search_poll', array('filter_question' => $name));
        //Steps and Verifying
        $this->cmsPollsHelper()->createPoll($pollData);
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->frontend();
        $this->assertTrue($this->cmsPollsHelper()->frontCheckPoll($name),
                'There is no ' . $name . ' poll on home page');
        $this->cmsPollsHelper()->vote($name, $pollData['assigned_answers_set']['answer_1']['answer_title']);
        //Verifying
        //Re-open Home page
        $this->frontend();
        $this->assertFalse($this->cmsPollsHelper()->frontCheckPoll($name),
                'There is ' . $name . ' poll on home page');
    }
}