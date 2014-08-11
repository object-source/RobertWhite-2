<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create webpos table
 */
$installer->run("
	
	DROP TABLE IF EXISTS {$this->getTable('webpos_admin')};

	CREATE TABLE {$this->getTable('webpos_admin')} (
	  `admin_id` int(11) unsigned NOT NULL auto_increment,
	  `key` varchar(255) NOT NULL default '',
	  `random` varchar(255),
	  PRIMARY KEY (`admin_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	ALTER TABLE {$this->getTable('sales_flat_order')} 
		ADD COLUMN `webpos_discount_amount` DECIMAL(12,4) default NULL,   
		ADD COLUMN `webpos_giftwrap_amount` DECIMAL( 12, 4 ) default NULL,   			     
		ADD COLUMN `webpos_admin_id` int(10) default NULL,   
		ADD COLUMN `webpos_admin_name` varchar(255) default NULL;   
	
	DROP TABLE IF EXISTS {$this->getTable('webpos_survey')};
	CREATE TABLE {$this->getTable('webpos_survey')}(
		`survey_id` int(11) unsigned NOT NULL auto_increment,
		`question` varchar(255) default '',			 
		`answer` varchar(255) default '',			 
		`order_id` int(10) unsigned NOT NULL,		   			  		   
		PRIMARY KEY (`survey_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

