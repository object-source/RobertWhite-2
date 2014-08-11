<?php

class Magestore_Webpos_Model_Giftwrap extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        return array(
            0    => Mage::helper('webpos')->__('Per Order'),
            1    => Mage::helper('webpos')->__('Per Item')
        );
    }
	
	public function paypal_prepare_line_items($observer)
	{
		$paypalCart = $observer->getEvent()->getPaypalCart();
		if ($paypalCart){
			$salesEntity = $paypalCart->getSalesEntity();
			if(Mage::getModel('checkout/session')->getData('webpos_giftwrap_amount') > 0){
				$paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL,abs((float)Mage::getModel('core/session')->getData('webpos_giftwrap_amount')),Mage::helper('webpos')->__('Giftwrap'));				
			}
		}

	}
}