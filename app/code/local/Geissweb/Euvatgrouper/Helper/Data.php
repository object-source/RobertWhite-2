<?php
/**
 * ||GEISSWEB| EU VAT Enhanced for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through URL: https://www.geissweb.de/eula.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to https://www.geissweb.de/ for more information
 * or send an email to support[at]geissweb.de to make a feature request.
 *
 * @category   Mage
 * @package    Geissweb_Euvatgrouper
 * @copyright  Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license    https://www.geissweb.de/eula.html GEISSWEB End User License Agreement
 */
class Geissweb_Euvatgrouper_Helper_Data extends Mage_Core_Helper_Abstract
{
	var $_debug = false;

	public function __construct()
	{
		$this->_debug = $this->isDebugMode();
	}


	/**
     * Returns whether debug mode is turned on
     * @return bool
     */
    public function isDebugMode()
    {
        return Mage::getStoreConfig('euvatgrouper/extension_info/debug_mode', Mage::app()->getStore()->getId());
    }
    /**
     * Gets the default country from Store Config
     * @return bool
     */
    public function getOfflineValidate()
    {
        return Mage::getStoreConfig('euvatgrouper/vat_settings/offline_validation', Mage::app()->getStore()->getId());
    }

    /**
     * Gets the default country from Store Config
     * @return bool
     */
    public function getStoreCountryCode()
    {
        return Mage::getStoreConfig('general/country/default', Mage::app()->getStore()->getId());
    }

    /**
     * Determines if the validation of VAT-IDs is enabled
     * @return int
     */
    public function isValidationEnabled()
    {
        return Mage::getStoreConfig('euvatgrouper/vat_settings/validate_vatid', Mage::app()->getStore()->getId());
    }

    /**
     * Gets a list of all current EU member states
     * @return array
     */
    public function getEuCountries()
    {
        return explode(",", Mage::getStoreConfig('general/country/eu_countries'));
    }

    /**
     * Determines wether the country is a member state or not
     * @param $cc : 2-letter country code
     * @return bool
     */
    public function isEuCountry($cc)
    {
        if (in_array($cc, $this->getEuCountries()))
            return true;
        return false;
    }

    /**
     * Gets the full store VAT-ID
     * @return int
     */
    public function getShopVatId()
    {
        return Mage::getStoreConfig('euvatgrouper/vat_settings/own_vatid', Mage::app()->getStore()->getId());
    }

    /**
     * Gets the CC from VAT-ID
     * @return int
     */
    public function getShopVatCc()
    {
        return strtoupper(substr(Mage::getStoreConfig('euvatgrouper/vat_settings/own_vatid', Mage::app()->getStore()->getId()), 0, 2));
    }

	/**
	 * Gets the mail sender address
	 * @return int
	 */
	public function doSendValidationMail()
	{
		return Mage::getStoreConfig('euvatgrouper/email_settings/send_mail', Mage::app()->getStore()->getId());
	}


	/**
     * Gets the mail sender address
     * @return int
     */
    public function getMailSender()
    {
        return Mage::getStoreConfig('euvatgrouper/email_settings/mail_sender', Mage::app()->getStore()->getId());
    }

    /**
     * Gets the mail recipient address
     * @return int
     */
    public function getMailRecipient()
    {
        return Mage::getStoreConfig('euvatgrouper/email_settings/mail_recipient', Mage::app()->getStore()->getId());
    }

    /**
     * Gets the mail template
     * @return int
     */
    public function getMailTemplate()
    {
        return Mage::getStoreConfig('euvatgrouper/email_settings/mail_template', Mage::app()->getStore()->getId());
    }

	/**
	 * @param null $vatid
	 *
	 * @return bool|null|string
	 */
	public function cleanCustomerVatId($vatid=NULL)
	{
		if($vatid != NULL)
		{
			$vatid = trim(str_replace(array(" ", ".", ",", "-", "|", "/"), "", $vatid));
			return $vatid;
		}

		return false;
	}

	/**
	 * Returns whether debug mode is turned on
	 * @return bool
	 */
	public function isIPv6Mode()
	{
		return Mage::getStoreConfig('euvatgrouper/extension_info/ipv6_mode', Mage::app()->getStore()->getId());
	}
	/**
	 * Returns the IPv4 address to bind on
	 * @return bool
	 */
	public function getIPv4ToBindOn()
	{
		return Mage::getStoreConfig('euvatgrouper/extension_info/ipv4_addr', Mage::app()->getStore()->getId());
	}

	/**
	 * Try to return the best fitting group for actual customer data
	 *
	 * @param $vatdata
	 * @param $customer_cc
	 *
	 * @return mixed
	 */
	public function getBestCustomerGroup($vatdata, $customer_cc)
	{
		if($this->_debug) Mage::log("[EUVAT] Function RUNNING: _getBestCustomerGroup --------------");

		if(Mage::registry('euvatenhanced_is_admin_validation'))
		{
			$key = "valid";
		} else { $key = "validation_result";}

		$shop_cc = $this->getShopVatCc();
		if ($this->_debug) Mage::log("[EUVAT] GETTING GROUP FOR: $customer_cc");

		if (is_array($vatdata))
		{
			if($shop_cc == $customer_cc && $vatdata[$key] == true)
			{
				if ($this->_debug) Mage::log("[EUVAT] Valid own Country -> GRP[" . $this->getSameCountryGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getSameCountryGroupId()) . "]");
				return $this->getSameCountryGroupId();
			}

			if ($vatdata[$key] == true && $vatdata['is_vat_free'] == true) {
				if ($this->_debug) Mage::log("[EUVAT] Valid VAT-exempt -> GRP[" . $this->getValidEuVatGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getValidEuVatGroupId()) . "]");
				return $this->getValidEuVatGroupId();

			} elseif ($vatdata[$key] == true && $vatdata['is_vat_free'] == false) {
				if ($this->_debug) Mage::log("[EUVAT] Valid own Country -> GRP[" . $this->getSameCountryGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getSameCountryGroupId()) . "]");
				return $this->getSameCountryGroupId();

			} else {
				if (!$this->isEuCountry($customer_cc)) {
					if ($this->_debug) Mage::log("[EUVAT] OUTSIDE EU -> GRP[" . $this->getOutsideEuGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getOutsideEuGroupId()) . "]");
					return $this->getOutsideEuGroupId();

				} else {
					if ($this->_debug) Mage::log("[EUVAT] DEFAULT -> GRP[" . $this->getDefaultGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getDefaultGroupId()) . "]");
					return $this->getDefaultGroupId();
				}
			}

		} else {

			if (!$this->isEuCountry($customer_cc)) {
				if ($this->_debug) Mage::log("[EUVAT] OUTSIDE EU2 -> GRP[" . $this->getOutsideEuGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getOutsideEuGroupId()) . "]");
				return $this->getOutsideEuGroupId();
			} else {
				if ($this->_debug) Mage::log("[EUVAT] DEFAULT2 -> GRP[" . $this->getDefaultGroupId() . "] TXCLS[" . $this->getTaxClassIdForGroup($this->getDefaultGroupId()) . "]");
				return $this->getDefaultGroupId();
			}
		}
	}

	public function getExcludedGroups()
	{
		return explode(",", Mage::getStoreConfig('euvatgrouper/grouping_settings/excluded_groups', Mage::app()->getStore()->getId()));
	}

	public function getValidEuVatGroupId()
	{
		return Mage::getStoreConfig('euvatgrouper/grouping_settings/target_group', Mage::app()->getStore()->getId());
	}

	public function getSameCountryGroupId()
	{
		return Mage::getStoreConfig('euvatgrouper/grouping_settings/target_group_same_cc', Mage::app()->getStore()->getId());
	}

	public function getOutsideEuGroupId()
	{
		return Mage::getStoreConfig('euvatgrouper/grouping_settings/target_group_outside', Mage::app()->getStore()->getId());
	}

	public function getDefaultGroupId()
	{
		return Mage::getStoreConfig('customer/create_account/default_group', Mage::app()->getStore()->getId());
	}

	public function getTaxClassIdForGroup($group_id)
	{
		return Mage::getSingleton('customer/group')->load($group_id)->getTaxClassId();
	}
}