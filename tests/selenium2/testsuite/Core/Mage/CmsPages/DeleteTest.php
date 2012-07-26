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
 * Delete Page Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsPages_DeleteTest extends Mage_Selenium_TestCase
{
    protected function assertPreconditions()
    {
        $this->loginAdminUser();
        $this->addParameter('id', '0');
    }

    /**
     * <p>Creates and deletes Page with required fields</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Pages page</p>
     * <p>2. Create page with required fields</p>
     * <p>3. Open newly created page</p>
     * <p>4. Delete newly created page</p>
     * <p>Expected result</p>
     * <p>Page is created and deleted successfully</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3215
     */
    public function deleteCmsPage()
    {
        //Data
        $pageData = $this->loadData('new_cms_page_req');
        $search = array('filter_title'   => $pageData['page_information']['page_title'],
                        'filter_url_key' => $pageData['page_information']['url_key']);
        //Steps
        $this->navigate('manage_cms_pages');
        $this->cmsPagesHelper()->createCmsPage($pageData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_cms_page');
        //Steps
        $this->cmsPagesHelper()->deleteCmsPage($search);
        //Verification
        $this->assertMessagePresent('success', 'success_deleted_cms_page');
    }
}