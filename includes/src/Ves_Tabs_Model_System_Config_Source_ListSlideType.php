<?php

class Ves_Tabs_Model_System_Config_Source_ListSlideType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'fading', 'label'=>Mage::helper('ves_tabs')->__('Fading')),
            array('value'=>'top', 'label'=>Mage::helper('ves_tabs')->__('Slide Top')),
            array('value'=>'left', 'label'=>Mage::helper('ves_tabs')->__('Slide Left'))
        );
    }    
}
