<?php
/**
 * WDCA - Better Store Search
 *
 * NOTICE OF LICENSE
*/

/**
 *
 * @category   TBT
 * @package    TBT_Bss
 * @author     WDCA Better Store Search Team <contact@wdca.ca>
 */
class TBT_Bss_Block_System_Config_Rundiagnostics extends TBT_Bss_Block_System_Config_Abstractbutton
{

    public function getButtonData($buttonBlock)
    {
        $params = array(
            'website' => $buttonBlock->getRequest()->getParam('website')
        );
        $url = Mage::helper('adminhtml')->getUrl('testsweet/index/index', $params);
        $data = array(
            'label'     => Mage::helper('bss')->__("Run 'Test Bss' Diagnostics"),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => '',
            'comment'    => "",
        );

        return $data;
    }


}
