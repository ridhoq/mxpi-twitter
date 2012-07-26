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
 * @category    Xcom
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 class Xcom_Ebay_Block_Adminhtml_Channel_GridTest extends Xcom_TestCase
{
    /** @var Xcom_Ebay_Block_Adminhtml_Channel_GridFixture */
    protected $_object;

    public function setUp()
    {
        $this->_checkConnection = true;
        parent::setUp();
        $this->_object = new Xcom_Ebay_Block_Adminhtml_Channel_GridFixture();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testPrepareColumns()
    {
        $this->_object->prepareColumns();
        $marketplace = $this->_object->getColumn('marketplace');
        $this->assertInstanceOf('Mage_Adminhtml_Block_Widget_Grid_Column', $marketplace);
        $action = $this->_object->getColumn('action');
        $this->assertInstanceOf('Mage_Adminhtml_Block_Widget_Grid_Column', $action);
        $params = $action->getActions();
        $this->assertEquals('*/ebay_channel/edit', $params[0]['url']['base']);
        $this->assertEquals('channel_id', $params[0]['field']);
    }
}

class Xcom_Ebay_Block_Adminhtml_Channel_GridFixture extends Xcom_Ebay_Block_Adminhtml_Channel_Grid
{
    public function prepareColumns()
    {
        $this->setLayout(Mage::app()->getLayout());
        return parent::_prepareColumns();
    }
}
