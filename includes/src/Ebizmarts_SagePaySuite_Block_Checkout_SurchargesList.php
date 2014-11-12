<?php

class Ebizmarts_SagePaySuite_Block_Checkout_SurchargesList extends Mage_Core_Block_Template {
    
    protected function _construct() {
        parent::_construct();
        
        $this->getSurcharges();
        $this->setTemplate('sagepaysuite/checkout/surcharges_list.phtml');
    }
    
    /**
     * Returns surcharge data from config.
     * 
     * @return array
     */
    public function getSurcharges() {
        
        if(empty($this->_surchargeList)) {
            $_config = Mage::getSingleton('sagepaysuite/api_payment')
                            ->getConfigData('surcharge_creditcards');

            $config = unserialize($_config);
            
            //Do nothing if no cards are configured for surcharge
            if ($config && count($config)) {
                $this->_surchargeList = $config;
            }
        }
        
        return $this->_surchargeList;        
    }
    
    /**
     * Checks if any surcharge can be applied.
     * 
     * @return boolean
     */
    public function canApplySurcharge() {
        return (count($this->_surchargeList) > 0);
    }    
    
    
    public function wouldAdd($type, $amount) {
        
        $add = '+ ';       
        
        if($type == 'percentage') {
            $add .= $amount . '% ' . $this->__('of Grand Total.');
        }
        else {
            //Fixed amount
            $add .= $this->helper('core')->formatPrice($amount, false);
        }
        
        return $add;
    }
            
}