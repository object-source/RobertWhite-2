<?php


class Ves_Tabs_Model_System_Config_Source_ListNavigation
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'', 'label'=>Mage::helper('ves_tabs')->__('No')),
            array('value'=>'number', 'label'=>Mage::helper('ves_tabs')->__('Number')),
            array('value'=>'thumbs', 'label'=>Mage::helper('ves_tabs')->__('Thumbnails'))
        );
    }    
}
