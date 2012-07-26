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
 * Backend Delete Tags tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tags_BackendDeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Catalog -> Tags -> All tags</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('all_tags');
        $this->assertTrue($this->checkCurrentPage('all_tags'), $this->getParsedMessages());
        $this->addParameter('storeId', '1');
    }

    /**
     * <p>Deleting a new tag</p>
     * <p>Steps:</p>
     * <p>1. Create a new tag</p>
     * <p>2. Open the tag</p>
     * <p>3. Click button "Delete Tag"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the tag has been deleted.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3498
     */
    public function deleteNew()
    {
        //Setup
        $setData = $this->loadData('backend_new_tag', null, 'tag_name');
        //Steps
        $this->tagsHelper()->addTag($setData);
        $this->assertTrue($this->checkCurrentPage('all_tags'), $this->getParsedMessages());
        $this->tagsHelper()->deleteTag(array('tag_name' => $setData['tag_name']));
        //Verify
        $this->assertTrue($this->checkCurrentPage('all_tags'), $this->getParsedMessages());
        $this->assertMessagePresent('success', 'success_deleted_tag');
    }
}
