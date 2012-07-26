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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Mapping_Model_Message_ProductTaxonomy_Updated_InboundTest extends Xcom_TestCase
{
    /** @var Xcom_Mapping_Model_Message_ProductTaxonomy_Updated_Inbound */
    protected $_object;
    protected $_instanceOf = 'Xcom_Mapping_Model_Message_ProductTaxonomy_Updated_Inbound';

    public function setUp()
    {
        parent::setUp();
        $this->_object = new Xcom_Mapping_Model_Message_ProductTaxonomy_Updated_Inbound();
    }

    public function tearDown()
    {
        $this->_object = null;
        parent::tearDown();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object);
    }

    public function testTopic()
    {
        $this->assertEquals('productTaxonomy/updated', $this->_object->getTopic());
    }

    public function testProcessEmpty()
    {
        $this->assertInstanceOf($this->_instanceOf, $this->_object->process());
    }

    public function testProcess()
    {
        $this->_object->setBody(array('version' => '100'));
        $this->_mockHelper();

        $this->assertInstanceOf($this->_instanceOf, $this->_object->process());
    }

    protected function _mockHelper()
    {
        $helperMock = $this->mockHelper('xcom_xfabric', array('send'));
        $helperMock->expects($this->any())
            ->method('send')
            ->will($this->returnSelf());
    }

    public function testGetOutboundTopics()
    {
        $topics = $this->_object->getOutboundTopics();
        $this->assertContains('productTaxonomy/get', $topics);
        $this->assertContains('productTaxonomy/productType/get', $topics);
    }

    public function testGetSupportedLocales()
    {
        $locales = $this->_object->getSupportedLocales();
        $this->assertCount(5, $locales);
    }
}
