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
class Core_Mage_CmsStaticBlocks_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to CMS -> Static Blocks</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_cms_static_blocks');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Delete a static block</p>
     * <p>Steps:</p>
     * <p>1. Create a new block</p>
     * <p>2. Open the block</p>
     * <p>3. Delete the block</p>
     * <p>Expected result:</p>
     * <p>Received the message that the block has been deleted.</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3228
     */
    public function deleteNew()
    {
        //Data
        $setData = $this->loadData('new_static_block');
        $blockToDelete = $this->loadData('search_static_block',
                array('filter_block_identifier' => $setData['block_identifier']));
        //Steps
        $this->cmsStaticBlocksHelper()->createStaticBlock($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_block');
        //Steps
        $this->cmsStaticBlocksHelper()->deleteStaticBlock($blockToDelete);
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_block');
    }
}