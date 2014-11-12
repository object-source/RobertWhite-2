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
        return substr(Mage::getStoreConfig('euvatgrouper/vat_settings/own_vatid', Mage::app()->getStore()->getId()), 0, 2);
    }

    /**
     * Gets the mail sender address
     * @return int
     */
    public function getMailSender()
    {
        return Mage::getStoreConfig('euvatgrouper/vat_settings/mail_sender', Mage::app()->getStore()->getId());
    }

    /**
     * Gets the mail recipient address
     * @return int
     */
    public function getMailRecipient()
    {
        return Mage::getStoreConfig('euvatgrouper/vat_settings/mail_recipient', Mage::app()->getStore()->getId());
    }

    /**
     * Gets the mail template
     * @return int
     */
    public function getMailTemplate()
    {
        return Mage::getStoreConfig('euvatgrouper/vat_settings/mail_template', Mage::app()->getStore()->getId());
    }

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
}

?>