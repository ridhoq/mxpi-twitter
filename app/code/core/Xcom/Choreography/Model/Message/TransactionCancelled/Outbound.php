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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Choreography_Model_Message_TransactionCancelled_Outbound implements Xcom_Xfabric_Model_Message_Data_Interface
{
    protected $_options = array();
    protected $_data = array();

    public function __construct($data)
    {
        $this->_data = $data;
        $this->_options = array(
            'synchronous' => false,
            'destination_id' => $data['destination_id'],
            'topic' => 'com.x.core.v1/TransactionCancelled',
            'schema_record_name' => 'TransactionCancelled',
            'schema_version' => '1.0.1',
            'on_behalf_of_tenant' => true
        );
    }

    public function getMessageData()
    {
        return $this->_data;
    }

    public function getOptions()
    {
        return $this->_options;
    }

}
