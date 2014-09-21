<?php

class Ves_Tabs_Model_System_Config_Source_ListTarget
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'_blank', 'label'=>Mage::helper('ves_tabs')->__('New window')),
            array('value'=>'_parent', 'label'=>Mage::helper('ves_tabs')->__('Parent window'))
        );
    }    
}
