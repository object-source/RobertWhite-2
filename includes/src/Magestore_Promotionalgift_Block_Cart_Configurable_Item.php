<?php
class Magestore_Promotionalgift_Block_Cart_Configurable_Item extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable
{
	public function getOptionList()
    {
        $options = parent::getOptionList();
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
}