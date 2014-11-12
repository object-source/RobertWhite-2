<?php
class Magestore_Webpos_Model_Sales_Quote_Address_Total_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract {
	public function collect(Mage_Sales_Model_Quote_Address $address) {
        $session = Mage::getSingleton('checkout/session');
		$discount = $session->getData('webpos_admin_discount');
        if(!$discount){
            return $this;
        }
		
		$items = $address->getAllItems();
		if (!count($items)) {
			return $this;
		}
		$session->setData('webpos_admin_discount',$discount);
		$address->setWebposDiscountAmount($discount);		
		$address->setData('webpos_discount_amount',$discount);
		$address->setGrandTotal($address->getGrandTotal() - $address->getWebposDiscountAmount());
		$address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getWebposDiscountAmount());	
		return $this;
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address) 
	{
		$amount = $address->getWebposDiscountAmount();		
		$title = Mage::helper('sales')->__('Discount(Admin)');
		if ($amount!=0) {
			$address->addTotal(array(
					'code'=>$this->getCode(),
					'title'=>$title,
					'value'=>'-'.$amount
			));
		}
		return $this;
	}
}
