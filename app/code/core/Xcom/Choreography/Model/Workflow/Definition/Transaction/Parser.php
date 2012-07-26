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
class Xcom_Choreography_Model_Workflow_Definition_Transaction_Parser extends Xcom_Choreography_Model_Workflow_Definition_Parser
{

    // External constants
    const TRANSACTION_TYPE_INFORM       = 'INFORM';
    const TRANSACTION_TYPE_NOTIFY       = 'NOTIFY';
    const TRANSACTION_TYPE_RESPONSE     = 'RESPONSE';
    const TRANSACTION_TYPE_QUERY        = 'QUERY';

    const TRANSACTION_PUBLISH_MODE_REGULAR      = 'REGULAR';
    const TRANSACTION_PUBLISH_MODE_BROADCAST    = 'BROADCAST';
    const TRANSACTION_PUBLISH_MODE_UNICAST      = 'UNICAST';

    const TRANSACTION_ROLE_SENDER   = 'SENDER';
    const TRANSACTION_ROLE_RECEIVER = 'RECEIVER';

    // Internal constants
    const RECEIPT_TIMEOUT       = 'receiptTimeout';
    const VALIDATION_TIMEOUT    = 'validationTimeout';
    const RESPONSE_TIMEOUT      = 'responseTimeout';

    const TRANSACTION_NODE_DETAILS_V1 = 'com.x.ocl.localworkflow.v1.TransactionNodeDetails';
    const TRANSACTION_TIMEOUTS_V1 = 'com.x.ocl.localworkflow.v1.TransactionTimeouts';
    const V1 = 'com.x.ocl.localworkflow.v1.';

    protected $_transactionId ;
    protected $_type; // INFORM, NOTIFY, RESPONSE, QUERY
    protected $_publishMode; // REGULAR, BROADCAST, UNICAST
    protected $_senderAction;
    protected $_receiverSuccessAction;
    protected $_receiverFailureAction;
    protected $_preconditions;
    protected $_successPostconditions;
    protected $_failurePostconditions;
    protected $_role; // SENDER or RECEIVER
    protected $_nodeId;



    function __construct($transactionNode)
    {
        $this->parse($transactionNode);
    }

    /**
     * Takes as input an array version of a XoclLocalWorkflow avdl object for a Node record of type TRANSACTION.
     * @param $transactionNode
     * @throws InvalidArgumentException when input is bad
     */
    public function parse($transactionNode)
    {
        if (!isset($transactionNode["type"])) {
            throw new InvalidArgumentException('Transaction parser input had no "type" field.');
        }
        if ($transactionNode["type"] != "TRANSACTION") {
            throw new InvalidArgumentException('Transaction parser was given input with incorret type.  type == ' . $transactionNode["type"]);
        }

        $this->_parseNode($transactionNode);
    }

    /**
     * Parses the Node record and caches results within this object.
     * @param $transactionNode
     */
    protected function _parseNode($transactionNode)
    {
        $this->_nodeId = $transactionNode['id'];
        // No need to parse 'type'
        $this->_parseDetails($transactionNode['details']);
    }

    /**
     * Parses TransactionNodeDetails record and caches results.
     * @param $transactionDetails
     */
    protected function _parseDetails($transactionDetails)
    {
        $details = $transactionDetails;
        if (isset($transactionDetails[self::TRANSACTION_NODE_DETAILS_V1])) {
            $details = $transactionDetails[self::TRANSACTION_NODE_DETAILS_V1];
        }
        $this->_role = $this->_convertRole($details['role']); // SENDER or RECEIVER
        $this->_parseTransaction($details['transaction']);

    }

    /**
     * Converts the avdl role into the Parser role constant value.
     * This insulates the remaining parser logic and any logic relying on the parser from changes in the avdl enum.
     * @param $role
     * @return string
     * @throws UnexpectedValueException
     */
    protected function _convertRole($role)
    {
        switch ($role) {
            case self::TRANSACTION_ROLE_SENDER:
                return self::TRANSACTION_ROLE_SENDER;
            case self::TRANSACTION_ROLE_RECEIVER:
                return self::TRANSACTION_ROLE_RECEIVER;
            default:
                throw new UnexpectedValueException('Unsupported role in conversion: ' . $role);
        }
    }

    /**
     * Parses the Transaction record.
     *
     * Record structure:
     * 	string id;
     *  TransactionType type;
     *  union{null, TransactionPublishMode} publishMode = null;
     *  TransactionAction senderAction;
     *  union{null, TransactionAction} receiverSuccessAction;
     *  union{null, TransactionAction} receiverFailureAction;
     *  union{null, array<Expression>} preconditions = null;
     *  union{null, array<Expression>} successPostconditions = null;
     *  union{null, array<Expression>} failurePostconditions = null;
     * @param $transaction
     */
    protected function _parseTransaction($transaction)
    {
        $this->_transactionId = $transaction['id'];
        $this->_type = $this->_convertType($transaction['type']); // INFORM, NOTIFY, RESPONSE, QUERY
        $this->_publishMode = $this->_convertMode($transaction['publishMode']); // REGULAR, BROADCAST, UNICAST
        $this->_senderAction = $this->_parseTransactionAction($transaction['senderAction']);
        if (isset($transaction['receiverSuccessAction'])) {
            $this->_receiverSuccessAction = $this->_parseTransactionAction($transaction['receiverSuccessAction']);
        }
        if (isset($transaction['receiverFailureAction'])) {
            $this->_receiverFailureAction = $this->_parseTransactionAction($transaction['receiverFailureAction']);
        }
        if (isset($transaction['preconditions'])) {
            $this->_preconditions = $this->_parseConditionsArray($transaction['preconditions']);
        }
        if (isset($transaction['successPostconditions'])) {
            $this->_successPostconditions = $this->_parseConditionsArray($transaction['successPostconditions']);
        }
        if (isset($transaction['failurePostconditions'])) {
            $this->_failurePostconditions = $this->_parseConditionsArray($transaction['failurePostconditions']);
        }
    }

    /**
     * Converts the avdl transaction type into the Parser transaction type constant value.
     * This insulates the remaining parser logic and any logic relying on the parser from changes in the avdl enum.
     * @param $type
     * @return string
     * @throws UnexpectedValueException
     */
    protected function _convertType($type)
    {
        switch ($type) {
            case self::TRANSACTION_TYPE_INFORM:
                return self::TRANSACTION_TYPE_INFORM;
            case self::TRANSACTION_TYPE_NOTIFY:
                return self::TRANSACTION_TYPE_NOTIFY;
            case self::TRANSACTION_TYPE_QUERY:
                return self::TRANSACTION_TYPE_QUERY;
            case self::TRANSACTION_TYPE_RESPONSE:
                return self::TRANSACTION_TYPE_RESPONSE;
            default:
                throw new UnexpectedValueException('Unsupported type in conversion: ' . $type);
        }
    }

    /**
     * Converts the avdl mode into the Parser mode constant value.
     * This insulates the remaining parser logic and any logic relying on the parser from changes in the avdl enum.
     * @param $mode
     * @return null|string
     * @throws UnexpectedValueException
     */
    protected function _convertMode($mode)
    {
        if (null == $mode) {
           return null;
        }
        switch ($mode) {
            case self::TRANSACTION_PUBLISH_MODE_BROADCAST:
                return self::TRANSACTION_PUBLISH_MODE_BROADCAST;
            case self::TRANSACTION_PUBLISH_MODE_REGULAR:
                return self::TRANSACTION_PUBLISH_MODE_REGULAR;
            case self::TRANSACTION_PUBLISH_MODE_UNICAST:
                return self::TRANSACTION_PUBLISH_MODE_UNICAST;
            default:
                throw new UnexpectedValueException('Unsupported mode in conversion: ' . $mode);
        }
    }

    /**
     * Parses the TransactionAction record and returns a Transaction_Action object.
     *
     * 	record TransactionAction {
        string messageName;
        string topic;
        union{null, TransactionTimeouts} timeouts = null;
        union{null, int} retries = null;
        }
     * @param $transactionAction array
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Action
     */
    protected function _parseTransactionAction($transactionAction)
    {
        if (empty($transactionAction)) {
            return null;
        }

        if (isset($transactionAction[self::V1 . 'TransactionAction'])) {
            $transactionAction = $transactionAction[self::V1 . 'TransactionAction'];
        }

        $messageName = $transactionAction['messageName'];
        $topic = $transactionAction['topic'];
        $timeouts = $this->_parseTransactionTimeouts($transactionAction['timeouts']);
        $retries = $this->_parseRetries($transactionAction['retries']);

        $action = new Xcom_Choreography_Model_Workflow_Definition_Transaction_Action();
        $action->setMessageName($messageName);
        $action->setRetries($retries);
        $action->setTimeouts($timeouts);
        $action->setTopic($topic);

        return $action;
    }

    /**
     * Parses the 'retries' union and returns the retries value
     * @param $retries
     * @return null
     */
    protected function _parseRetries($retries) {
        if (empty($retries)) {
            return null;
        }

        return $retries['int'];
    }

    /**
     * Parses the transaction timeouts and returns them in an array
     * Structure of input:
     * 	union{null, Timeout} receiptTimeout = null;
        union{null, Timeout} validationTimeout = null;
        union{null, Timeout} responseTimeout = null;
     * @param $timeouts
     * @return array
     */
    protected function _parseTransactionTimeouts($timeouts)
    {
        if (empty($timeouts)) {
            return null;
        }

        if (isset($timeouts[self::TRANSACTION_TIMEOUTS_V1])) {
            $timeouts = $timeouts[self::TRANSACTION_TIMEOUTS_V1];
        }

        $result = array();

        $result[self::RECEIPT_TIMEOUT] = $this->_extractTimeout($timeouts, self::RECEIPT_TIMEOUT);
        $result[self::VALIDATION_TIMEOUT] = $this->_extractTimeout($timeouts, self::VALIDATION_TIMEOUT);
        $result[self::RESPONSE_TIMEOUT] = $this->_extractTimeout($timeouts, self::RESPONSE_TIMEOUT);

        return $result;
    }

    /**
     * Extracts the timeout value from the array given the proper field name for the timeout in the array.
     * Returns the parsed timeout.
     * @param $result array reference
     * @param $timeouts array
     * @param $field string
     */
    protected function _extractTimeout($timeouts, $field)
    {
        if (isset($timeouts[$field])) {
            return $this->_parseTimeout($timeouts[$field]);
        }
        return null;
    }

    /**
     * Parses the Conditions record and returns the results.
     * @param $conditions
     * @return array|null
     */
    protected function _parseConditionsArray($conditions)
    {
        if (empty($conditions)) {
            return null;
        }

        $results = array();
        foreach ($conditions as $expression) {
            $parsedExpression = $this->_parseExpression($expression);
            if ($parsedExpression != null) {
                $results[] = $parsedExpression;
            }
        }

        return $results;
    }

    /**
     * Returns the Failure Postconditions in an array
     * @return array
     */
    public function getFailurePostconditions()
    {
        return $this->_failurePostconditions;
    }

    /**
     * Returns the node ID
     * @return string;
     */
    public function getNodeId()
    {
        return $this->_nodeId;
    }

    /**
     * Returns the preconditions
     * @return array
     */
    public function getPreconditions()
    {
        return $this->_preconditions;
    }

    /**
     * Returns the publish mode for this Transaction
     *  const TRANSACTION_PUBLISH_MODE_REGULAR      = 'REGULAR';
     *  const TRANSACTION_PUBLISH_MODE_BROADCAST    = 'BROADCAST';
     *  const TRANSACTION_PUBLISH_MODE_UNICAST      = 'UNICAST';
     * @return string
     */
    public function getPublishMode()
    {
        return $this->_publishMode;
    }

    /**
     * Returns the Transaction_Action for the Receiver Failure message.
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Action
     */
    public function getReceiverFailureAction()
    {
        return $this->_receiverFailureAction;
    }

    /**
     * Returns the Transaction_Action for the Receiver Success message.
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Action
     */
    public function getReceiverSuccessAction()
    {
        return $this->_receiverSuccessAction;
    }

    /**
     * Returns role of the Transaction
     *  const TRANSACTION_ROLE_SENDER   = 'SENDER';
     *  const TRANSACTION_ROLE_RECEIVER = 'RECEIVER';
     * @return string
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     * Returns the Transaction_Action for the Sender message.
     * @return Xcom_Choreography_Model_Workflow_Definition_Transaction_Action
     */
    public function getSenderAction()
    {
        return $this->_senderAction;
    }

    /**
     * Returns the Success Postconditions
     * @return array
     */
    public function getSuccessPostconditions()
    {
        return $this->_successPostconditions;
    }

    /**
     * Returns the Transaction ID
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    /**
     * Returns the Transaction type
     *  const TRANSACTION_TYPE_INFORM       = 'INFORM';
     *  const TRANSACTION_TYPE_NOTIFY       = 'NOTIFY';
     *  const TRANSACTION_TYPE_RESPONSE     = 'RESPONSE';
     *  const TRANSACTION_TYPE_QUERY        = 'QUERY';
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }


}

