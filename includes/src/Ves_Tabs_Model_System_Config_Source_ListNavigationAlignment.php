<?php

class Ves_Tabs_Model_System_Config_Source_ListNavigationAlignment
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'horizontal', 'label'=>Mage::helper('ves_tabs')->__('Horizontal')),
            array('value'=>'vertical', 'label'=>Mage::helper('ves_tabs')->__('Vertical')),
            array('value'=>'0', 'label'=>Mage::helper('ves_tabs')->__('Disable'))
        );
    }    
}
