<?php
/**
 * Autor: Sven Geiß
 * Datum: 13.03.14 20:25 Uhr
 * Dateiname: Vat.php
 * (c) 2014 ||GEISSWEB|
 */

//class Geissweb_Euvatgrouper_Block_Adminhtml_Customer_Edit_Tab_Vat extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
class Geissweb_Euvatgrouper_Block_Adminhtml_Customer_Edit_Tab_Vat extends Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses // implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('euvatgrouper/vat_addresses.phtml');
	}


	/**
	 * Initialize form object
	 *
	 * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
	 */
	public function initForm()
	{
		/* @var $customer Mage_Customer_Model_Customer */
		$customer = Mage::registry('current_customer');

		$form = new Varien_Data_Form();
		$fieldset = $form->addFieldset('address_fieldset', array(
				'legend'    => Mage::helper('customer')->__("Edit Customer's Address"))
		);

		/*
		 * VAT
		 */
		$vat_fieldset = $form->addFieldset('vat_fieldset', array(
				'legend'    => Mage::helper('euvatgrouper')->__("VAT-ID Validation"))
		);
		$vat_attributes = Mage::getModel('eav/entity_attribute')->getCollection()
						->addFieldToFilter('entity_type_id', array('eq' => 2))
						->addFieldToFilter('attribute_code',
							array('in' => array(
								'vat_request_id',
								'vat_is_valid',
								'vat_request_date',
								'vat_request_success',
								'vat_trader_name',
								'vat_trader_company_type',
								'vat_trader_address'
						)));
		foreach ($vat_attributes as $attribute) {
			$attribute->setFrontendLabel(Mage::helper('customer')->__($attribute->getFrontend()->getLabel()));
			$attribute->unsIsVisible();
		}
		$this->_setFieldset($vat_attributes, $vat_fieldset);
		$form->getElement('vat_is_valid')->setReadonly(true,true);
		$form->getElement('vat_request_id')->setReadonly(true,true);
		$form->getElement('vat_request_date')->setReadonly(true,true);
		$form->getElement('vat_request_success')->setReadonly(true,true);
		$form->getElement('vat_trader_name')->setReadonly(true,true);
		$form->getElement('vat_trader_company_type')->setReadonly(true,true);
		$form->getElement('vat_trader_address')->setReadonly(true,true);
		/*
		 * END VAT
		 */

		$addressModel = Mage::getModel('customer/address');
		$addressModel->setCountryId(Mage::helper('core')->getDefaultCountry($customer->getStore()));
		/** @var $addressForm Mage_Customer_Model_Form */
		$addressForm = Mage::getModel('customer/form');
		$addressForm->setFormCode('adminhtml_customer_address')
			->setEntity($addressModel)
			->initDefaultValues();

		$attributes = $addressForm->getAttributes();
		if(isset($attributes['street'])) {
			Mage::helper('adminhtml/addresses')
				->processStreetAttribute($attributes['street']);
		}
		foreach ($attributes as $attribute) {
			/* @var $attribute Mage_Eav_Model_Entity_Attribute */
			$attribute->setFrontendLabel(Mage::helper('customer')->__($attribute->getFrontend()->getLabel()));
			$attribute->unsIsVisible();
		}
		$this->_setFieldset($attributes, $fieldset);

		$regionElement = $form->getElement('region');
		$regionElement->setRequired(true);
		if ($regionElement) {
			$regionElement->setRenderer(Mage::getModel('adminhtml/customer_renderer_region'));
		}

		$regionElement = $form->getElement('region_id');
		if ($regionElement) {
			$regionElement->setNoDisplay(true);
		}

		$country = $form->getElement('country_id');
		if ($country) {
			$country->addClass('countries');
		}

		if ($this->isReadonly()) {
			foreach ($addressModel->getAttributes() as $attribute) {
				$element = $form->getElement($attribute->getAttributeCode());
				if ($element) {
					$element->setReadonly(true, true);
				}
			}
		}

		$customerStoreId = null;
		if ($customer->getId()) {
			$customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
		}

		$prefixElement = $form->getElement('prefix');
		if ($prefixElement) {
			$prefixOptions = $this->helper('customer')->getNamePrefixOptions($customerStoreId);
			if (!empty($prefixOptions)) {
				$fieldset->removeField($prefixElement->getId());
				$prefixField = $fieldset->addField($prefixElement->getId(),
					'select',
					$prefixElement->getData(),
					'^'
				);
				$prefixField->setValues($prefixOptions);
			}
		}

		$suffixElement = $form->getElement('suffix');
		if ($suffixElement) {
			$suffixOptions = $this->helper('customer')->getNameSuffixOptions($customerStoreId);
			if (!empty($suffixOptions)) {
				$fieldset->removeField($suffixElement->getId());
				$suffixField = $fieldset->addField($suffixElement->getId(),
					'select',
					$suffixElement->getData(),
					$form->getElement('lastname')->getId()
				);
				$suffixField->setValues($suffixOptions);
			}
		}

		$addressCollection = $customer->getAddresses();
		$this->assign('customer', $customer);
		$this->assign('addressCollection', $addressCollection);
		$form->setValues($addressModel->getData());
		$this->setForm($form);

		return $this;
	}

}