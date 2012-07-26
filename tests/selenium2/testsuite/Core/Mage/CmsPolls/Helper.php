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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsPolls_Helper extends Mage_Selenium_TestCase
{
    /**
     * Add answers to Poll
     *
     * @param array $answersSet Array of answers for poll
     */
    public function addAnswer(array $answersSet)
    {
        $answerId = 1;
        $this->openTab('poll_answers');
        foreach ($answersSet as $value) {
            $this->clickButton('add_new_answer', false);
            $this->addParameter('answerId', '-' . $answerId++);
            $this->fillForm($value, 'poll_answers');
        }
    }

    /**
     * Create Poll
     *
     * @param array $pollData Array of Poll data
     */
    public function createPoll(array $pollData)
    {
        $pollData = $this->arrayEmptyClear($pollData);
        $answers = (isset($pollData['assigned_answers_set'])) ? $pollData['assigned_answers_set'] : array();
        $this->clickButton('add_new_poll');
        if (!$this->controlIsPresent('multiselect', 'visible_in')
                && isset($pollData['visible_in'])) {
            unset($pollData['visible_in']);
        }
        $this->fillForm($pollData, 'poll_information');
        $this->addAnswer($answers);
        $this->saveForm('save_poll');
    }

    /**
     * Open Poll
     *
     * @param array $pollData Poll to open.
     */
    public function openPoll(array $searchPollData)
    {
        $searchPollData = $this->arrayEmptyClear($searchPollData);
        if (!$this->controlIsPresent('dropdown', 'filter_visible_in')
                && isset($searchPollData['filter_visible_in'])) {
            unset($searchPollData['filter_visible_in']);
        }

        $xpathTR = $this->search($searchPollData, 'poll_grid');
        $this->assertNotEquals(null, $xpathTR, 'Poll is not found');
        $id = $this->getColumnIdByName('Poll Question');
        $this->addParameter('pollName', $this->getText($xpathTR . "//td[$id]"));
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage();
    }

    /**
     * Check Poll exists on frontend
     *
     * @param string $pollTitle "Poll Question"
     * @return boolean Return TRUE if poll is visible on home page
     */
    public function frontCheckPoll($pollTitle)
    {
        $this->addParameter('pollTitle', $pollTitle);
        if (!$this->controlIsPresent('fieldset', 'community_poll')) {
            return false;
        }

        return $this->controlIsPresent('pageelement', 'poll_title');
    }

    /**
     * Change Poll state
     *
     * @param string $pollData Array of Poll data
     */
    public function setPollState($searchPollData, $state)
    {
        $this->openPoll($searchPollData);
        $this->fillForm(array('poll_status' => $state), 'poll_information');
        $this->saveForm('save_poll');
    }

    /**
     * Change state for all opened polls to Close
     */
    public function closeAllPolls()
    {
        $xpathTR = $this->search(array('filter_status' => 'Open'));
        $id = $this->getColumnIdByName('Poll Question');
        while ($this->isElementPresent($xpathTR)) {
            $this->addParameter('pollName', $this->getText($xpathTR . "//td[$id]"));
            $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
            $this->click($xpathTR);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
            $this->fillForm(array('poll_status' => 'Closed'), 'poll_information');
            $this->saveForm('save_poll');
        }
    }

    /**
     * Check poll information
     *
     * @param array $pollData Array of Poll data
     */
    public function checkPollData($pollData)
    {
        if (!$this->controlIsPresent('multiselect', 'visible_in')
                && isset($pollData['visible_in'])) {
            unset($pollData['visible_in']);
        }
        $answers = (isset($pollData['assigned_answers_set'])) ? $pollData['assigned_answers_set'] : array();

        $this->assertTrue($this->verifyForm($pollData, 'poll_information'), $this->getParsedMessages());

        $answersXpath = $this->_getControlXpath('fieldset', 'assigned_answers_set');
        $answersCount = $this->getXpathCount($answersXpath);
        if (count($answers) == $answersCount) {
            $i = 1;
            foreach ($answers as $value) {
                $attId = $this->getAttribute($answersXpath . "[$i]@id");
                $answerId = explode("_", $attId);
                $this->addParameter('answerId', end($answerId));
                $this->assertTrue($this->verifyForm($value, 'poll_answers'), $this->getParsedMessages());
                $i++;
            }
        } else {
            $this->fail("Unexpected count of answers: " . count($answers) . "!= $answersCount");
        }
    }

    /**
     * Delete a Poll
     *
     * @param array $pollData Array of Poll data
     */
    public function deletePoll($searchPollData)
    {
        $this->openPoll($searchPollData);
        $this->clickButtonAndConfirm('delete_poll', 'confirmation_for_delete');
    }

    /**
     * Vote
     *
     * @param string $pollTitle "Poll Question"
     * @param string $pollAnswer Answer to votes
     */
    public function vote($pollTitle, $pollAnswer)
    {
        $this->addParameter('pollTitle', $pollTitle);
        $this->addParameter('answer', $pollAnswer);
        if ($this->controlIsPresent('pageelement', 'poll_title')
                && $this->controlIsPresent('radiobutton', 'vote')) {
            $this->clickControl('radiobutton', 'vote', false);
            $this->clickButton('vote');
        } else {
            $this->fail("Could not vote");
        }
    }
}