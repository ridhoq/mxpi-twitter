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
 * @package     Xcom_Choreography
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Choreography_Model_Workflow_Definition_Transaction_ParserTest extends Xcom_Database_TestCase
{

    public function testParsing()
    {
        $filename = Mage::getBaseDir() . DIRECTORY_SEPARATOR . 'tests/integration/fixture/Xcom/Choreography/SubmitShippedOrder.json';

        $workflowJson = file_get_contents($filename);

        $workflowArray = json_decode($workflowJson, true);

        $firstTransaction = $workflowArray['localWorkflow']['nodes'][0];

        /** @var $parser Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser */
        $parser = new Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser($firstTransaction);

        $this->assertEquals(Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_TYPE_RESPONSE, $parser->getType());
        $this->assertEquals(Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser::TRANSACTION_ROLE_RECEIVER, $parser->getRole());
        $this->assertEquals('com.x.ordermanagement.v2.SubmitOrder.MarketplaceOrder', $parser->getTransactionId());
        $this->assertEquals(null, $parser->getPublishMode());

        $senderAction = $parser->getSenderAction();
        $this->assertEquals('com.x.ordermanagement.v2.SubmitMarketplaceOrder', $senderAction->getMessageName());
        $this->assertEquals('/com.x.ordermanagement.v2/SubmitShippedOrder.MarketplaceOrder/SubmitMarketplaceOrder', $senderAction->getTopic());
        $this->assertEquals(30, $senderAction->getReceiptTimeoutSeconds());
        $this->assertEquals(3 * 60, $senderAction->getValidationTimeoutSeconds());
        $this->assertNull($senderAction->getResponseTimeoutSeconds());
        $this->assertEquals(3, $senderAction->getRetries());

        $successAction = $parser->getReceiverSuccessAction();
        $this->assertEquals('com.x.ordermanagement.v2.SubmitOrderSucceeded', $successAction->getMessageName());
        $this->assertEquals('/com.x.ordermanagement.v2/SubmitShippedOrder.MarketplaceOrder/SubmitOrderSucceeded', $successAction->getTopic());
        $this->assertEquals(30, $successAction->getReceiptTimeoutSeconds());
        $this->assertEquals(3 * 60, $successAction->getValidationTimeoutSeconds());
        $this->assertNull($successAction->getResponseTimeoutSeconds());
        $this->assertEquals(3, $successAction->getRetries());

        $failureAction = $parser->getReceiverFailureAction();
        $this->assertEquals('com.x.ordermanagement.v2.SubmitOrderFailed', $failureAction->getMessageName());
        $this->assertEquals('/com.x.ordermanagement.v2/SubmitShippedOrder.MarketplaceOrder/SubmitOrderFailed', $failureAction->getTopic());
        $this->assertEquals(30, $failureAction->getReceiptTimeoutSeconds());
        $this->assertNull($failureAction->getValidationTimeoutSeconds());
        $this->assertNull($failureAction->getResponseTimeoutSeconds());
        $this->assertEquals(0, $failureAction->getRetries());

    }
}