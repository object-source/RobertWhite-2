<?php

/**
 * FORM payment form
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */

class Ebizmarts_SagePaySuite_Block_Form_SagePayForm extends Ebizmarts_SagePaySuite_Block_Form_SagePayToken {

    protected function _prepareLayout() {    	
        if(!$this->helper('sagepaysuite')->creatingAdminOrder()) {
            $this->setChild('surcharges.list', $this->getLayout()->createBlock('sagepaysuite/checkout_surchargesList', 'surcharges.list'));
    	}
        
        return parent::_prepareLayout();
    }    
    
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('sagepaysuite/payment/form/sagePayForm.phtml');
    }

}