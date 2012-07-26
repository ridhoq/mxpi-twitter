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

class Xcom_Xfabric_Model_Message_Data_Inbound implements Xcom_Xfabric_Model_Message_Data_Interface
{
    protected $_options = array();

    public function __construct($options)
    {
        $this->_options = array(
            'topic' => $options['topic'],
            'headers' => $options['headers'],
            'body' => $options['body'],
            'is_process_later' => isset($options['topic_options']['is_process_later']) ? true : false
        );
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function getMessageData()
    {
        return null;
    }
}
