<?php

class Ves_Tempcp_IndexController extends Mage_Core_Controller_Front_Action {

    public function minicartAction(){

    	$json = array();

        $json['html'] =  Mage::helper("ves_tempcp/framework")->getMinicartHtml();
        $json['summary_qty'] = Mage::getSingleton('checkout/cart')->getSummaryQty();
        $json['summary_qty'] = !empty($json['summary_qty'])?$json['summary_qty']:0;
        $json['subtotal'] = Mage::helper('ves_tempcp')->getCartSubtotal();
        
        echo Mage::helper('core')->jsonEncode( $json );

    }
}

?>
