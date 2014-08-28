<?php

class Magestore_Promotionalgift_Block_Cart_Coupon extends Mage_Checkout_Block_Cart_Coupon
{
	public function getCouponCode(){
		if (Mage::getStoreConfig('promotionalgift/general/enable')
			&& Mage::getSingleton('checkout/session')->getData('promptionalgift_coupon_code')){
			return Mage::getSingleton('checkout/session')->getData('promptionalgift_coupon_code');
		}
		return $this->getQuote()->getCouponCode();
	}
}