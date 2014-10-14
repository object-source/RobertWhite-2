<?php
class Magestore_Promotionalgift_Block_Cart_Giftvoucher_Item extends Mage_Checkout_Block_Cart_Item_Renderer
{
	public function getProductOptions(){
		$options = parent::getProductOptions();
		/*Giftvoucher*/
		foreach (Mage::helper('giftvoucher')->getGiftVoucherOptions() as $code => $label)
            if ($option = $this->getItem()->getOptionByCode($code)) {
                if ($code == 'giftcard_template_id') {
                    $valueTemplate = Mage::getModel('giftvoucher/gifttemplate')->load($option->getValue());
                    $options[] = array(
                        'label' => $label,
                        'value' => $this->htmlEscape($valueTemplate->getTemplateName() ? $valueTemplate->getTemplateName() : $option->getValue()),
                    );
                } else if ($code == 'amount') {
                    $options[] = array(
                        'label' => $label,
                        'value' => Mage::helper('core')->formatPrice($option->getValue()),
                    );
                } else {
                    $options[] = array(
                        'label' => $label,
                        'value' => $this->htmlEscape($option->getValue()),
                    );
                }
            }
		/*End Giftvoucher*/
		$item = $this->getItem();
		$quoteId = Mage::getModel('checkout/cart')->getQuote()->getId();
		
		$shoppingQuote = Mage::getModel('promotionalgift/shoppingquote')
					->getCollection()
					->addFieldToFilter('quote_id',$quoteId)
					->addFieldToFilter('item_id',$item->getId())
					->getFirstItem()
					;
		if($shoppingQuote->getId()){
			$options[] = array(
				'label'	=> Mage::helper('promotionalgift')->__('Promotional Gift'),
				'value'	=> $this->htmlEscape($shoppingQuote->getMessage()),
			);
			return $options;
		}
		
		$quotes = Mage::getModel('promotionalgift/quote')
					->getCollection()
					->addFieldToFilter('quote_id',$quoteId)
					->addFieldToFilter('item_id',$item->getId())
					// ->getFirstItem()
					;
		if(count($quotes) > 0){
			foreach($quotes as $quote){
				if($quote->getMessage()){
					$options[] = array(
						'label'	=> Mage::helper('promotionalgift')->__('Promotional Gift'),
						'value'	=> $this->htmlEscape($quote->getMessage()),
					);
					break;
				}
			}
		}
		return $options;
	}
	
	public function getProductThumbnail() {
        if(!Mage::helper('giftvoucher')->getInterfaceCheckoutConfig('display_image_item') || $this->getProduct()->getTypeId() != 'giftvoucher') 
            return parent::getProductThumbnail();
        $item = $this->getItem();
        if($item->getOptionByCode('giftcard_template_image')) $filename = $item->getOptionByCode('giftcard_template_image')->getValue();
        else $filename = 'default.png';
        if($item->getOptionByCode('giftcard_use_custom_image') && $item->getOptionByCode('giftcard_use_custom_image')->getValue()) {
            $urlImage = '/tmp/giftvoucher/images/' . $filename;
            $filename = 'custom/'.$filename;
        }else {
            if ($item->getOptionByCode('giftcard_template_id')) {
                $templateId = $item->getOptionByCode('giftcard_template_id')->getValue();
                $designPattern = Mage::getModel('giftvoucher/gifttemplate')->load($templateId)->getDesignPattern();
                if ($designPattern == Magestore_Giftvoucher_Model_Designpattern::PATTERN_LEFT)
                    $filename = 'left/'.$filename;
                else if ($designPattern == Magestore_Giftvoucher_Model_Designpattern::PATTERN_TOP)
                    $filename = 'top/'.$filename;
            }
            $urlImage = '/giftvoucher/template/images/' . $filename;
        }
        $imageUrl = Mage::getBaseDir('media') . str_replace("/", DS, $urlImage);
        
        if (!file_exists($imageUrl)){
                return parent::getProductThumbnail();
        }
        return $this->helper('giftvoucher')->getProductThumbnail($imageUrl, $filename, substr($urlImage, 1));
    }
}