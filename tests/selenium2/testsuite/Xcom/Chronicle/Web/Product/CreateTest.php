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

class Xcom_Chronicle_Web_Product_CreateTest extends Xcom_Chronicle_TestCase
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
     * Create Simple Product
     *
     * @test
     */
    public function createSimpleProduct()
    {
        //Create Product and Get the message
        $this->_createSimpleProduct();

        $expectedMsgs = array (
            0 => array ( "topic" => Pim::PRODUCT_CREATED ),
            1 => array ( "topic" => Inventory::STOCK_ITEM_UPDATED ),
            2 => array ( "topic" => WebStore::OFFER_CREATED )
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }

     /**
     * Create Configurable Product
     *
     * @test
     */
    public function createConfigurableProduct()
    {
        //Create Product
        $this->_createConfigurableProduct();
        $expectedMsgs = array ( 0 => array ( "topic" => Pim::PRODUCT_CREATED ),
            1 => array ( "topic" => Inventory::STOCK_ITEM_UPDATED ),
            2 => array ( "topic" => WebStore::OFFER_CREATED ),
            3 => array ( "topic" => Pim::PRODUCT_CREATED ),
            4 => array ( "topic" => WebStore::OFFER_CREATED )
        );
        $msgs = $this->_get2dXMessages();

        //Verify
        $this->verifyXMessage($expectedMsgs, $msgs);
    }
}