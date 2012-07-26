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
 * @package     Xcom_Stub
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Stub_Model_Resource_Message extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('xcom_stub/message', 'message_id');
    }

    /**
     * Retrieve recipient message data by filters.
     *
     * @param $topic
     * @param $body
     * @return array
     */
    public function getMessageData($topic, $body)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(
                'topic'     => 'recipient_topic_name',
                'headers'   => 'recipient_message_header',
                'body'      => 'recipient_message_body'
            ))
            ->where('sender_topic_name = ?', $topic)
            ->where('sender_message_body = ?', $body)
            ->order('description DESC');
            //we order by description because there can be more than one topic found
            //and we expect [Initializer] prefixed on the top

        return $this->_getReadAdapter()->fetchRow($select);
    }

    public function clearStubTable()
    {
        $this->_getReadAdapter()->delete($this->getMainTable());
        return $this;
    }
}
