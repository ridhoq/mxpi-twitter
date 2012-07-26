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
 * @package     Xcom_Log
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Log_Model_Source_ResultTest extends Xcom_TestCase
{
    /**
     * @var Xcom_Log_Model_Source_Result
     */
    protected $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('xcom_log/source_result');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->_object = null;
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Xcom_Log_Model_Source_Result', $this->_object);
    }

    public function testToOptionHash()
    {
        $result = array(
            Xcom_Log_Model_Source_Result::RESULT_WARNING   => Mage::helper('xcom_log')->__('Warning'),
            Xcom_Log_Model_Source_Result::RESULT_ERROR     => Mage::helper('xcom_log')->__('Error'),
            Xcom_Log_Model_Source_Result::RESULT_SUCCESS   => Mage::helper('xcom_log')->__('Success')
        );
        $this->assertEquals($result, $this->_object->toOptionHash(), 'Wrong result ot option hash');
    }
}
