<?php

class Ves_Tabs_Model_System_Config_Source_ListSlideEvent
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'click', 'label'=>Mage::helper('ves_tabs')->__('On Click')),
            array('value'=>'mouseover', 'label'=>Mage::helper('ves_tabs')->__('On Mouseover'))
        );
    }    
}
