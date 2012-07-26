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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Choreography_Model_Message_TransactionCancelledTest extends Xcom_TestCase
{
    /** @var Xcom_Choreography_Model_Message_TransactionCancelled */
    protected $_object;
    protected $_instanceOf = 'Xcom_Choreography_Model_Message_TransactionCancelled';
    protected $_modelName = 'xcom_choreography/message_transactionCancelled';

    public function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel($this->_modelName,array());
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

    public function testDataAccessors()
    {
        $error = array(array('code'=>"test",'message'=>null,'parameters'=>null));
        $reason = Xcom_Choreography_Model_Message_Reason::NotSpecified;
        $data = array(
            'error' => $error,
            'reason' => $reason,
        );
        $model = Mage::getModel($this->_modelName,$data);
        //print_r($model);

        //print_r('reason:' . $model->getReason());
        $this->assertEquals($model->getData(),$data);
        $this->assertEquals($error,$model->getError());
        $this->assertEquals($model->getReason(),$reason);
    }

}