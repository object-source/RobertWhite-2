<?php
/**
 * ||GEISSWEB|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@geissweb.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.geissweb.de/ for more information
 * or send an email to support@geissweb.de or visit our customer forum at
 * http://www.geissweb.de/forum
 *
 * @category   Mage
 * @package    Geissweb_Euvatgrouper
 * @copyright  Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license    http://www.geissweb.de/eula.html
 */

Mage::log("OK WE ARE HERE!");

$installer = $this;

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer->startSetup();

$customer = Mage::getModel('customer/customer');
$attribute_set_id = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();



$installer->addAttribute('customer', 'last_vat_validation_date', array(
	'label'		=> 'Date of last VAT validation',
	'type'		=> 'datetime',
	'input'		=> 'date',
	'visible'	=> true,
	'backend' 	=> 'eav/entity_attribute_backend_datetime',
	'frontend'	=> 'eav/entity_attribute_frontend_datetime',
	'required'	=> false,
	'user_defined'	=> true,
	'default'	=> false,
	'sort_order'	=> 12
));
if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $installer->addAttributeToSet('customer', $attribute_set_id, 'General', 'last_vat_validation_date');
}
if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
    Mage::getSingleton('eav/config')->getAttribute('customer', 'last_vat_validation_date')
	->setData('used_in_forms', array('customer_account_edit', 'customer_account_create', 'adminhtml_customer', 'checkout_register'))
	->save();
}




$installer->addAttribute('customer', 'vat_validation_result', array(
	'label'		=> 'VAT-ID is valid',
	'type'		=> 'int',
	'input'		=> 'select',
	'visible'	=> true,
	'backend'	=> NULL,
	'source'	=> 'eav/entity_attribute_source_boolean',
	'required'	=> false,
	'user_defined'	=> true,
	'default'	=> false,
	'sort_order'	=> 13
));
if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $installer->addAttributeToSet('customer', $attribute_set_id, 'General', 'vat_validation_result');
}
if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
    Mage::getSingleton('eav/config')->getAttribute('customer', 'vat_validation_result')
	->setData('used_in_forms', array('customer_account_edit', 'customer_account_create', 'adminhtml_customer', 'checkout_register'))
	->save();
}





$installer->addAttribute('customer', 'vies_result_data', array(
	'label'		=> 'VIES result data log',
	'type'		=> 'text',
	'input'		=> 'textarea',
	'visible'	=> true,
	'backend'	=> NULL,
	'required'	=> false,
	'user_defined'	=> true,
	'default'	=> false,
	'sort_order'	=> 14
));
if (version_compare(Mage::getVersion(), '1.6.0', '<='))
{
      $installer->addAttributeToSet('customer', $attribute_set_id, 'General', 'vies_result_data');
}
if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
    Mage::getSingleton('eav/config')->getAttribute('customer', 'vies_result_data')
	->setData('used_in_forms', array('customer_account_edit', 'customer_account_create', 'adminhtml_customer', 'checkout_register'))
	->save();
}



$installer->endSetup();

?>