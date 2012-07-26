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

class Xcom_Choreography_Model_Workflow_Definition_Parser
{
    const TIME_UNIT_SECONDS = 'SECONDS';
    const TIME_UNIT_MINUTES = 'MINUTES';
    const TIME_UNIT_HOURS = 'HOURS';
    const TIME_UNIT_DAYS = 'DAYS';

    const TIMEOUT_V_1 = 'com.x.ocl.localworkflow.v1.Timeout';


    /**
     * 	enum Operator {
        AND, OR, NOT
        }

        record ParamExpression {
        string name;
        string state;
        }

        record FunctionParam {
        union{string, ParamExpression} param;
        }

        record FunctionExpression {
        string functionName;
        array<FunctionParam> params;
        }

        record Expression {
        union{null, Expression} leftOperand = null;
        union{null, Operator} operator = null;
        union{null, Expression} rightOperand = null;

        union{null, ParamExpression} param = null;

        union{null, FunctionExpression} function = null;
        }
     * @param $expression
     * @return mixed
     */
    protected function _parseExpression($expression)
    {
        return $expression;
    }


    /**
        int value;
        TimeUnit unit;
     * @param $timeout
     * @return int timeout in seconds
     */
    protected function _parseTimeout($timeout)
    {
        if (null == $timeout || empty($timeout)) {
            return null;
        }

        if (isset($timeout[self::TIMEOUT_V_1])) {
            $timeout = $timeout[self::TIMEOUT_V_1];
        }

        if (!isset($timeout['value']) || !isset($timeout['unit'])) {
            throw new InvalidArgumentException('unit or value not set for timeout!');
        }
        $value = (int)$timeout['value'];
        $unit = $timeout['unit']; // SECONDS, MINUTES, HOURS, DAYS

        switch ($unit) {
            case self::TIME_UNIT_SECONDS:
                $result = $value;
                break;
            case self::TIME_UNIT_MINUTES:
                $result = $value * 60;
                break;
            case self::TIME_UNIT_HOURS:
                $result = $value * 60 * 60;
                break;
            case self::TIME_UNIT_DAYS:
                $result = $value * 60 * 60 * 24;
                break;
            default:
                throw new UnexpectedValueException('Transaction parsing error.  Unexpected time unit: ' . $unit);
        }

        return $result;
    }
}