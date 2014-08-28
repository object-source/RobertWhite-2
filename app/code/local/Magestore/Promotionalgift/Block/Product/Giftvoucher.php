<?php

class Magestore_Promotionalgift_Block_Product_Giftvoucher extends Magestore_Promotionalgift_Block_Product_View
{
	public function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('promotionalgift/product/giftvoucher.phtml');
		return $this;
	}
	
	public function getStartFormHtml(){
		return $this->getBlockHtml('product.info.giftvoucher');
	}
	
	public function getOptionsWrapperBottomHtml(){
		return $this->getBlockHtml('product.info.addtocart');
	}
        public function isCartPage()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        return $currentUrl;
    }
}