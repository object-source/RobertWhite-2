<?php


class Magestore_Promotionalgift_Model_Quote_Freeshipping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());

        $address->setFreeShipping(0);
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }        
		
		foreach ($items as $item) {
			$quotes = Mage::getModel('promotionalgift/quote')->getCollection()
									->addFieldToFilter('item_id',$item->getId())
									->getFirstItem();
			
			if($quotes->getId())
			{
				$isfreeShipping = Mage::getModel('promotionalgift/catalogrule')->load($quotes->getCatalogRuleId())->getFreeShipping();
				if($isfreeShipping)
				{
					$item->setFreeShipping(true);
					// $item->save();
				}else
					{
						$item->setFreeShipping(false);
					}
			}else
			{
				$shoppingCartQuotes = Mage::getModel('promotionalgift/shoppingquote')->getCollection()
									->addFieldToFilter('item_id',$item->getId())
									->getFirstItem();
				if($shoppingCartQuotes->getId())
				{
					$isfreeShipping = Mage::getModel('promotionalgift/shoppingcartrule')->load($shoppingCartQuotes->getShoppingcartruleId())->getFreeShipping();
					if($isfreeShipping)
					{
						$item->setFreeShipping(true);
						// $item->save();
					}else
					{
						$item->setFreeShipping(false);
					}
				}
			}
		}
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }

}
