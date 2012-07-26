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
 * Product life cycle
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Xcom_Chronicle_XMessages_WebStore as WebStore;
use Xcom_Chronicle_XMessages_Pim as Pim;
use Xcom_Chronicle_XMessages_Inventory as Inventory;

class Xcom_Chronicle_Web_Product_DeleteTest extends Xcom_Chronicle_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {

        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * Delete Product
     *
     * @dataProvider deleteProductDataProvider
     * @test
     */
    public function deleteProduct($type)
    {
        //Delete Product
        $this->_deleteProduct($type);
        $expectedMsgs = array(
            array ( "topic" => Pim::PRODUCT_DELELTED ),
            array ( "topic" => WebStore::OFFER_DELELTED ),
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

    public function deleteProductDataProvider(){
        return array(
            "Delete Simple Product" => array("simple"),
            "Delete Configurable Product" => array("configurable")
        );
    }
}