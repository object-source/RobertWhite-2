<?php

class Magestore_Promotionalgift_Model_System_Config_Source_Showfreegift
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        return array(
			array('value' => 'cart', 'label'=>Mage::helper('promotionalgift')->__('On Cart')),
            array('value' => 'checkout', 'label'=>Mage::helper('promotionalgift')->__('On Checkout')),
            array('value' => 'both', 'label'=>Mage::helper('promotionalgift')->__('On both Cart and Checkout')),
            array('value' => 'hide', 'label'=>Mage::helper('promotionalgift')->__('Hide it')),
        );
    }
}