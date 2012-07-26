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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mmp_Block_Adminhtml_Widget_Grid_MassactionTest extends Xcom_TestCase
{
    /** @var $_object Xcom_Mmp_Block_Adminhtml_Account_Grid_Massaction */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mmp_Block_Adminhtml_Widget_Grid_Massaction();
    }

    public function tearDown()
    {
        $this->_object = null;
    }

    public function testGetJavaScriptCustomClassName()
    {
        $this->_prepareObject();

        $result = $this->_object->getJavaScript();
        $this->assertContains('new xcomGridMassaction(', $result);
    }

    protected function _prepareObject()
    {
        $this->_object->setParentBlock(new Xcom_Mmp_Block_Adminhtml_Widget_Grid_MassactionTest_Fixture());
        $this->_object->setUseSelectAll(false);
    }
}

class Xcom_Mmp_Block_Adminhtml_Widget_Grid_MassactionTest_Fixture
    extends Mage_Core_Block_Abstract
{
    protected $_collection;

    public function __construct()
    {
        $object = new Xcom_TestCase();
        $this->_collection = $object->mockCollection('xcom_mmp/policy');
    }
    public function getCollection()
    {
        return $this->_collection;
    }
}
