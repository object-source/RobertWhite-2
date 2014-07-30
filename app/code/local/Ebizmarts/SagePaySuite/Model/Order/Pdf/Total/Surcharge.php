<?php

class Ebizmarts_SagePaySuite_Model_Order_Pdf_Total_Surcharge extends Mage_Sales_Model_Order_Pdf_Total_Default {
    
    /**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount() {
        $amount = Mage::helper('sagepaysuite/surcharge')->getAmount($this->getOrder()->getId());
        return $amount;
    }    
    
}