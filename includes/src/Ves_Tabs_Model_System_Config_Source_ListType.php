<?php

class Ves_Tabs_Model_System_Config_Source_ListType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'', 'label'=>Mage::helper('ves_tabs')->__('-- Please select --')),
            array('value'=>'latest', 'label'=>Mage::helper('ves_tabs')->__('Latest')),
            array('value'=>'best_buy', 'label'=>Mage::helper('ves_tabs')->__('Best Buy')),
            array('value'=>'most_viewed', 'label'=>Mage::helper('ves_tabs')->__('Most Viewed')),
            array('value'=>'special', 'label'=>Mage::helper('ves_tabs')->__('Special')),
            array('value'=>'attribute', 'label'=>Mage::helper('ves_tabs')->__('Featured Product'))
        );
    }    
}
