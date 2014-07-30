<?php
/**
 * Autor: Sven Geiß
 * Datum: 13.03.14 11:34 Uhr
 * (c) 2014 ||GEISSWEB|
 */
Mage::log("UPGRADE..");
/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Remove old account based fields
 */
$attributeId = $installer->getAttributeId('customer', 'last_vat_validation_date');
$attributeTable = $installer->getAttributeTable('customer', 'last_vat_validation_date');
if(!empty($attributeId) && !empty($attributeTable)) {
	Mage::getSingleton('eav/config')->getAttribute('customer', 'last_vat_validation_date')->setData('used_in_forms', array())->save();
	$installer->run('DELETE FROM ' . $attributeTable . ' WHERE attribute_id = ' . $attributeId);
	$installer->removeAttribute('customer', 'last_vat_validation_date');
}
$attributeId = null;
$attributeTable = null;

$attributeId = $installer->getAttributeId('customer', 'vat_validation_result');
$attributeTable = $installer->getAttributeTable('customer', 'vat_validation_result');
if(!empty($attributeId) && !empty($attributeTable)) {
	Mage::getSingleton('eav/config')->getAttribute('customer', 'vat_validation_result')->setData('used_in_forms', array())->save();
	$installer->run('DELETE FROM ' . $attributeTable . ' WHERE attribute_id = ' . $attributeId);
	$installer->removeAttribute('customer', 'vat_validation_result');
}
$attributeId = null;
$attributeTable = null;

$attributeId = $installer->getAttributeId('customer', 'vies_result_data');
$attributeTable = $installer->getAttributeTable('customer', 'vies_result_data');
if(!empty($attributeId) && !empty($attributeTable)) {
	Mage::getSingleton('eav/config')->getAttribute('customer', 'vies_result_data')->setData('used_in_forms', array())->save();
	$installer->run('DELETE FROM ' . $attributeTable . ' WHERE attribute_id = ' . $attributeId);
	$installer->removeAttribute('customer', 'vies_result_data');
}

/**
 * Add additional VAT information fields to customer address
 */
$installer->addAttribute('customer_address', 'vat_trader_name', array(
	'label'			=> 'VAT Trader Name',
	'type'			=> 'varchar',
	'input'			=> 'text',
	'visible'		=> true,
	'required'		=> false,
	'user_defined'	=> true,
	'is_system'		=> false,
	'sort_order'	=> 10
));
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
	Mage::getSingleton('eav/config')->getAttribute('customer_address', 'vat_trader_name')->setData('used_in_forms', array('adminhtml_customer'))->save();
}

$installer->addAttribute('customer_address', 'vat_trader_company_type', array(
	'label'			=> 'VAT Trader Company Type',
	'type'			=> 'varchar',
	'input'			=> 'text',
	'visible'		=> true,
	'required'		=> false,
	'user_defined'	=> true,
	'is_system'		=> false,
	'sort_order'	=> 20
));
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
	Mage::getSingleton('eav/config')->getAttribute('customer_address', 'vat_trader_company_type')->setData('used_in_forms', array('adminhtml_customer'))->save();
}

$installer->addAttribute('customer_address', 'vat_trader_address', array(
	'label'			=> 'VAT Trader Address',
	'type'			=> 'text',
	'input'			=> 'textarea',
	'visible'		=> true,
	'required'		=> false,
	'user_defined'	=> true,
	'is_system'		=> false,
	'sort_order'	=> 30
));
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
	Mage::getSingleton('eav/config')->getAttribute('customer_address', 'vat_trader_name')->setData('used_in_forms', array('adminhtml_customer'))->save();
}



$installer->endSetup();
