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

$this->startSetup();

$this->run("

DROP TABLE IF EXISTS {$this->getTable('xcom_stub/message')};
CREATE TABLE `{$this->getTable('xcom_stub/message')}` (
  `message_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `sender_topic_name` varchar(255) NOT NULL DEFAULT '',
  `sender_message_name` varchar(255) NOT NULL DEFAULT '',
  `sender_message_body` text,
  `recipient_topic_name` varchar(255) NOT NULL DEFAULT '',
  `recipient_message_name` varchar(255) NOT NULL DEFAULT '',
  `recipient_message_body` text,
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Xcom_Stub module. Message Table';

");

$this->endSetup();

