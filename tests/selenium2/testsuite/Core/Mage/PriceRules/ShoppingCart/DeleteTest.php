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
 * Deleting Rules
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_ShoppingCart_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to Manage Shopping Cart Price Rules</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $this->addParameter('id', '0');
    }

    /**
     * <p>Delete Shopping cart price rule.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Create properly configured price rule (inactive) for shopping cart;</p>
     * <p>3. Open newly created shopping cart price rule;</p>
     * <p>4. Delete newly created shopping cart price rule;</p>
     * <p>Expected results:</p>
     * <p>Shopping Cart Price Rule successfully created and deleted;</p>
     *
     * @test
     * @TestlinkId	TL-MAGE-3321
     */
    public function deleteShoppingCartPriceRule()
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadData('scpr_required_fields', null, array('rule_name', 'coupon_code'));
        $ruleSearch = $this->loadData('search_shopping_cart_rule',
                                      array('filter_rule_name' => $ruleData['info']['rule_name'],
                                            'filter_coupon_code' => $ruleData['info']['coupon_code']));
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->priceRulesHelper()->deleteRule($ruleSearch);
        $this->assertMessagePresent('success', 'success_deleted_rule');
    }
}
