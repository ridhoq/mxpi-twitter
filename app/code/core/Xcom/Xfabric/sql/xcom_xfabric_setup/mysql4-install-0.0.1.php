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

$this->startSetup();

/*$this->run("

DROP TABLE IF EXISTS {$this->getTable('xcom_xfabric/message')};
CREATE TABLE `{$this->getTable('xcom_xfabric/message')}` (
  `message_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `received_id` smallint(6),
  `instance` varchar(50) NOT NULL DEFAULT '',
  `host` varchar(255) NOT NULL DEFAULT '',
  `topic` varchar(255) NOT NULL DEFAULT '',
  `headers` varchar(1000) NOT NULL DEFAULT '',
  `raw_message` text,
  `message` text,
  `unique_data` text,
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Xcom_Xfabric module. Messages Table';

");*/


$this->run("
    DROP TABLE IF EXISTS {$this->getTable('xcom_xfabric/message_request')};
    CREATE TABLE `{$this->getTable('xcom_xfabric/message_request')}` (
      `message_id` smallint(6) NOT NULL AUTO_INCREMENT,
      `topic` varchar(255) NOT NULL DEFAULT '',
      `headers` varchar(1000),
      `unique_data` text,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY (`message_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Xcom_Xfabric module. Request Messages Table';
");

$this->run("
    DROP TABLE IF EXISTS {$this->getTable('xcom_xfabric/message_response')};
    CREATE TABLE `{$this->getTable('xcom_xfabric/message_response')}` (
      `message_id` smallint(6) NOT NULL AUTO_INCREMENT,
      `request_id` smallint(6),
      `topic` varchar(255) NOT NULL DEFAULT '',
      `headers` varchar(1000),
      `body` text,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY (`message_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Xcom_Xfabric module. Response Messages Table';
");


$this->run("
    ALTER TABLE `{$this->getTable('xcom_xfabric/message_response')}`
        ADD CONSTRAINT FOREIGN KEY (`request_id`)
        REFERENCES `{$this->getTable('xcom_xfabric/message_request')}` (`message_id`);
");

$this->endSetup();
 
