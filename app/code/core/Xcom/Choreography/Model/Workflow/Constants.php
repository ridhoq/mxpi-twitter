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
class Xcom_Choreography_Model_Workflow_Constants
{
    // Role enum
    const TRANSACTION_ROLE_SENDER   = 'SENDER';
    const TRANSACTION_ROLE_RECEIVER = 'RECEIVER';

    // TransactionType enum
    const TRANSACTION_INFORM            = 'INFORM';
    const TRANSACTION_NOTIFY            = 'NOTIFY';
    const TRANSACTION_REQUEST_RESPONSE  = 'REQUEST_RESPONSE';
    const TRANSACTION_QUERY             = 'QUERY';

    // Transaction Communication Mode enum
    const TRANSACTION_COMMUNICATION_BROADCAST   = 'BROADCAST';
    const TRANSACTION_COMMUNICATION_UNICAST     = 'UNICAST';
}