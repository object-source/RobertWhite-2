<?php

class Ves_Tabs_Model_System_Config_Source_ListEasingType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'easeInQuad', 'label'=>Mage::helper('ves_tabs')->__('easeInQuad')),
            array('value'=>'easeOutQuad', 'label'=>Mage::helper('ves_tabs')->__('easeOutQuad')),
            array('value'=>'easeInOutQuad', 'label'=>Mage::helper('ves_tabs')->__('easeInOutQuad')),
            array('value'=>'easeInCubic', 'label'=>Mage::helper('ves_tabs')->__('easeInCubic')),
            array('value'=>'easeInOutCubic', 'label'=>Mage::helper('ves_tabs')->__('easeInOutCubic')),
            array('value'=>'easeOutQuart', 'label'=>Mage::helper('ves_tabs')->__('easeOutQuart')),
            array('value'=>'easeInOutQuart', 'label'=>Mage::helper('ves_tabs')->__('easeInOutQuart')),
            array('value'=>'easeInQuint', 'label'=>Mage::helper('ves_tabs')->__('easeInQuint')),
            array('value'=>'easeOutQuint', 'label'=>Mage::helper('ves_tabs')->__('easeOutQuint')),
            array('value'=>'easeInOutQuint', 'label'=>Mage::helper('ves_tabs')->__('easeInOutQuint')),
            array('value'=>'easeInSine', 'label'=>Mage::helper('ves_tabs')->__('easeInSine')),
            array('value'=>'easeOutSine', 'label'=>Mage::helper('ves_tabs')->__('easeOutSine')),
            array('value'=>'easeInOutSine', 'label'=>Mage::helper('ves_tabs')->__('easeInOutSine')),
            array('value'=>'easeInExpo', 'label'=>Mage::helper('ves_tabs')->__('easeInExpo')),
            array('value'=>'easeInOutSine', 'label'=>Mage::helper('ves_tabs')->__('easeInOutSine')),
            array('value'=>'easeOutExpo', 'label'=>Mage::helper('ves_tabs')->__('easeOutExpo')),
            array('value'=>'easeInOutExpo', 'label'=>Mage::helper('ves_tabs')->__('easeInOutExpo')),
            array('value'=>'easeInCirc', 'label'=>Mage::helper('ves_tabs')->__('easeInCirc')),
            array('value'=>'easeOutCirc', 'label'=>Mage::helper('ves_tabs')->__('easeOutCirc')),
            array('value'=>'easeInOutExpo', 'label'=>Mage::helper('ves_tabs')->__('easeInOutExpo')),
            array('value'=>'easeInOutCirc', 'label'=>Mage::helper('ves_tabs')->__('easeInOutCirc')),
            array('value'=>'easeInElastic', 'label'=>Mage::helper('ves_tabs')->__('easeInElastic')),
            array('value'=>'easeOutElastic', 'label'=>Mage::helper('ves_tabs')->__('easeOutElastic')),
            array('value'=>'easeInOutElastic', 'label'=>Mage::helper('ves_tabs')->__('easeInOutElastic')),
            array('value'=>'easeOutBack', 'label'=>Mage::helper('ves_tabs')->__('easeOutBack')),
            array('value'=>'easeInBack', 'label'=>Mage::helper('ves_tabs')->__('easeInBack')),
            array('value'=>'easeInOutBack', 'label'=>Mage::helper('ves_tabs')->__('easeInOutBack')),
            array('value'=>'easeInBounce', 'label'=>Mage::helper('ves_tabs')->__('easeInBounce')),
            array('value'=>'easeOutBounce', 'label'=>Mage::helper('ves_tabs')->__('easeOutBounce')),
            array('value'=>'easeInOutBounce', 'label'=>Mage::helper('ves_tabs')->__('easeInOutBounce'))
        );
    }    
}
