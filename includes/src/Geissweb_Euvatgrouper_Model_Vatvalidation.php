<?php

/**
 * ||GEISSWEB| EU-VAT-GROUPER
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@geissweb.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support at support@geissweb.de.
 *
 * @category     Mage
 * @package      Geissweb_Euvatgrouper
 * @copyright    Copyright (c) 2012 GEISS Weblösungen (http://www.geissweb.de)
 * @license      http://www.geissweb.de/eula.html GEISSWEB End User License Agreement
 */
class Geissweb_Euvatgrouper_Model_Vatvalidation extends Varien_Object
{
    //protected $_eventPrefix = 'euvatgrouper_vatvalidation';
    //protected $_eventObject = 'vatvalidation';
    protected $_viesUrl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    public $shop_nr = "";
    public $shop_cc = "";
    public $user_cc = "";
    public $user_nr = "";
    public $user_taxvat = "";

	public $op_mode = "SINGLE";
	public $address_type = "";

    /**
     * Connects to EU VIES
     */
    public function __construct()
    {
        parent::__construct();

        try {

			if(Mage::helper('euvatgrouper')->isIPv6Mode())
			{
				$opts = array('socket' => array('bindto' => Mage::helper('euvatgrouper')->getIPv4ToBindOn().':0'));
				$context = stream_context_create($opts);
				$this->setClient(new SoapClient($this->_viesUrl, array(
					'exceptions' 	=> 0,
					'trace' 		=> false,
					'cache_wsdl' 	=> WSDL_CACHE_NONE,
					'soap_version' 	=> SOAP_1_1,
					'user_agent' 	=> 'Magento Webshop',
					'stream_context'=> $context
					)
				));

			} else {
				$this->setClient(new Zend_Soap_Client($this->_viesUrl, array(
					'soap_version'	=> SOAP_1_1,
					'user_agent' 	=> 'Magento Webshop',
					'cache_wsdl'	=> WSDL_CACHE_NONE
					)
				));
			}

			$this->setShopCc(Mage::helper('euvatgrouper')->getShopVatCc());
			$this->setShopNr(substr(trim(str_replace(" ", "", Mage::helper('euvatgrouper')->getShopVatId())), 2));

        } catch (SoapFault $s) {
			Mage::logException($s);
        }

    }

    /**
     * Validate
     */
    public function validate()
    {
        if (Mage::helper('euvatgrouper')->isValidationEnabled() && $this->isViesOnline())
        {

			$this->vies_params = array(
				'countryCode' => $this->getUserCc(),
				'vatNumber' => $this->getUserNr(),
				'requesterCountryCode' => $this->getShopCc(),
				'requesterVatNumber' => $this->getShopNr(),
			);

            if(isset($this->vies_params['countryCode'], $this->vies_params['vatNumber'], $this->vies_params['requesterCountryCode'], $this->vies_params['requesterVatNumber']))
            {
                // Advanced VAT validation function with return of request identifier
                $this->result = $this->getClient()->checkVatApprox($this->vies_params);

                // In case of error, retry with default request type
                if(is_soap_fault($this->result))
				{
                    $this->result = $this->getClient()->checkVat(array(
						'countryCode' => $this->getUserCc(),
                        'vatNumber' => $this->getUserNr())
					);
                }

                // When request was successful
                if(!is_soap_fault($this->result))
				{
					//var_dump( $this->result);
					$this->result->address_type = $this->getAddressType();
					$this->result->traderName = (property_exists($this->result, 'traderName')) ? $this->result->traderName : '';
					$this->result->traderCompanyType = (property_exists($this->result, 'traderCompanyType')) ? $this->result->traderCompanyType : '';
					$this->result->traderAddress = (property_exists($this->result, 'traderAddress')) ? $this->result->traderAddress : '';


                    if ($this->result->valid == 'true')
					{
                        if ($this->getUserCc() != $this->getShopCc()) {
                            $this->result->valid_vat = true;
                            $this->result->is_vat_free = true;

                        } elseif ($this->getUserCc() == $this->getShopCc()) {
                            $this->result->valid_vat = true;
                            $this->result->is_vat_free = false;

                        } else {
                            $this->result->valid_vat = false;
                            $this->result->is_vat_free = false;
                        }
                    } else {
                        $this->result->valid_vat = false;
                        $this->result->is_vat_free = false;
                    }

					// Use the validation result in session for tax calculation
					Mage::log("Vies Result: ".var_export($this->result,true));

                    //Save updated validation data to customer's address
					Mage::dispatchEvent('vat_check_after', array(
						'validation_result' => $this->result,
						'address_id' 		=> $this->getAddressId()
					));

                    //Send successmail in case of real successful validation
                    if (($this->result->valid_vat == 1 && $this->result->is_vat_free == 1) && !$this->getIsCronValidation())
					{
                        Mage::dispatchEvent('vat_check_success_email', array(
                                'customer' => Mage::getSingleton('customer/session')->getCustomer(),
                                'customer_session' => Mage::getSingleton('customer/session'),
                                'validation_result' => $this->result)
                        );
                    }
                }
            }

        } else { // VIES offline or extension disabled

            $this->result = new stdClass();
            $this->result->countryCode = $this->getUserCc();
            $this->result->vatNumber = $this->getUserNr();
            $this->result->requestDate = date("Y-m-d H:i:s", time());
			$this->result->traderName = '';
			$this->result->traderCompanyType = '';
			$this->result->traderAddress = '';
            $this->result->valid = false;
            $this->result->valid_vat = false;
            $this->result->is_vat_free = false;
			$this->result->faultstring = "SERVICE_UNAVAILABLE";

			//Save updated validation data to customer's address
			Mage::dispatchEvent('vat_check_after', array(
				'validation_result' => $this->result,
				'address_id' 		=> $this->getAddressId()
			));

        }
    }
//endfunction

    /**
     * Assigns the user to the default user group
     * @return bool
     */
    public function assignDefault()
    {
        $this->result = new stdClass();
        $this->result->countryCode = "";
        $this->result->vatNumber = "";
        $this->result->requestDate = date("Y-m-d H:i:s", time());
		$this->result->traderName = '';
		$this->result->traderCompanyType = '';
		$this->result->traderAddress = '';
        $this->result->valid = false;
        $this->result->valid_vat = 0;
        $this->result->is_vat_free = 0;
		$this->result->countryCode = (isset($this->viesres->countryCode)) ? $this->viesres->countryCode : $this->getUserCc();
		$this->result->vat_id_removed = true;
		$this->result->requestIdentifier = "";
		$this->result->address_type = $this->getAddressType();

		if (Mage::getSingleton('customer/session')->isLoggedIn())
		{
			$customer = Mage::getSingleton('customer/session')->getCustomer();

		} else {$customer = false;}


		//Save updated validation data to customer's address
		Mage::dispatchEvent('vat_check_after', array(
			'validation_result' => $this->result,
			'address_id' 		=> $this->getAddressId(),
			'customer'			=> $customer
		));

        return true;
    }

    public function isViesOnline()
    {
        $cs = curl_init();
        curl_setopt($cs, CURLOPT_URL, $this->_viesUrl);
        curl_setopt($cs, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cs, CURLOPT_VERBOSE, false);
		curl_setopt($cs, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($cs, CURLOPT_TIMEOUT, 15);
        curl_exec($cs);
        $httpcode = curl_getinfo($cs, CURLINFO_HTTP_CODE);
        curl_close($cs);
        return ($httpcode == "200") ? true : false;
    }

}
