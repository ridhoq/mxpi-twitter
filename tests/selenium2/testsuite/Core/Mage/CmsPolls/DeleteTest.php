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
class Core_Mage_CmsPolls_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to CMS -> Polls</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('poll_manager');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Delete a Poll</p>
     * <p>Steps:</p>
     * <p>1. Create a Poll</p>
     * <p>2. Open the Poll</p>
     * <p>3. Delete the Poll</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Poll has been deleted.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3222
     */
    public function deleteNewPoll()
    {
        //Data
        $pollData = $this->loadData('poll_open');
        $searchPollData = $this->loadData('search_poll',
                array('filter_question' => $pollData['poll_question']));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        //Steps
        $this->cmsPollsHelper()->deletePoll($searchPollData);
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_poll');
    }
}