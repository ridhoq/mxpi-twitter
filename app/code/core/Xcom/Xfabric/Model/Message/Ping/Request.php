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

class Xcom_Xfabric_Model_Message_Ping_Request implements Xcom_Xfabric_Model_Message_Data_Interface
{
    protected $_options = array();
    protected $_data = array();

    public function __construct($data)
    {
        $this->_options = array(
            'synchronous' => true,
            'destination_id' => $data['destination_id'],
            'topic' => 'message/ping',
            'schema_record_name' => 'Ping',
            'schema_version' => '1.0.0',
            'on_behalf_of_tenant' => false
        );
        $this->_data = $data;
    }

    public function getMessageData()
    {
        $data = array(
            'payload' => null,
        );
        return $data;
    }

    public function getOptions()
    {
        return $this->_options;
    }



}
