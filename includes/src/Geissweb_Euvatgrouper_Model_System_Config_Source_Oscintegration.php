<?php
/**
 * Autor: Sven Geiß
 * Datum: 08.06.2014 16:43 Uhr
 * Dateiname: Oscintegration.php
 * (c) 2014 ||GEISSWEB|
 */

class Geissweb_Euvatgrouper_Model_System_Config_Source_Oscintegration {

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => '', 'label' => Mage::helper('euvatgrouper')->__('-- NONE --')),
			array('value' => 'ONESTEP_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('onestepcheckout.com')),
			array('value' => 'IWD_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('IWD One Step/Page Checkout')),
			array('value' => 'FME_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('FME OneStepCheckout')),
			array('value' => 'AHEADWORKS_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Aheadworks OnePageCheckout')),
			array('value' => 'ECOMDEV_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('EcomDev CheckItOut')),
			array('value' => 'VINAGENTO_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Vinagento Oscheckout')),
			array('value' => 'TM_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('TM FireCheckout')),
			array('value' => 'GOMAGE_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('GoMage LightCheckout')),
			array('value' => 'MAGESTORE_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('MageStore OneStepCheckout')),
			array('value' => 'ECOMTEAM_CHECKOUT', 'label' => Mage::helper('euvatgrouper')->__('Ecommerceteam EasyCheckout')),
			array('value' => 'NEXTBITS_CHECKOUTNEXT', 'label' => Mage::helper('euvatgrouper')->__('Nextbits CheckoutNext')),
		);
	}

	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'' => Mage::helper('adminhtml')->__('None'),
			'ONESTEP_CHECKOUT' => Mage::helper('euvatgrouper')->__('onestepcheckout.com'),
			'IWD_CHECKOUT' => Mage::helper('euvatgrouper')->__('IWD One Step/Page Checkout'),
			'FME_CHECKOUT' => Mage::helper('euvatgrouper')->__('FME OneStepCheckout'),
			'AHEADWORKS_CHECKOUT' => Mage::helper('euvatgrouper')->__('Aheadworks OnePageCheckout'),
			'ECOMDEV_CHECKOUT' => Mage::helper('euvatgrouper')->__('EcomDev CheckItOut'),
			'VINAGENTO_CHECKOUT' => Mage::helper('euvatgrouper')->__('Vinagento Oscheckout'),
			'TM_CHECKOUT' => Mage::helper('euvatgrouper')->__('TM FireCheckout'),
			'GOMAGE_CHECKOUT' => Mage::helper('euvatgrouper')->__('GoMage LightCheckout'),
			'MAGESTORE_CHECKOUT' => Mage::helper('euvatgrouper')->__('MageStore OneStepCheckout'),
			'ECOMTEAM_CHECKOUT' => Mage::helper('euvatgrouper')->__('Ecommerceteam EasyCheckout'),
			'NEXTBITS_CHECKOUTNEXT' => Mage::helper('euvatgrouper')->__('Nextbits CheckoutNext'),
		);
	}

}