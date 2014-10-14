<?php

class Magestore_Webpos_Model_Source_Adminhtml_Payment
{
    public function toOptionArray()
	{
		$collection = Mage::getModel('payment/config')->getActiveMethods();
		
		if(! count($collection))
			return;
			
		$options = array();	
			
		foreach($collection as $item)
		{
			$title = $item->getTitle() ? $item->getTitle() : $item->getId();
			$options[] = array('value'=> $item->getId(), 'label' => $title);
		}
		
		return $options;
	}
}