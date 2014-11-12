<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Ves * @package     Ves_Tempcp
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Banner edit block
 *
 * @category   Ves
 * @package     Ves_Tempcp
 * @author    
 */

class Ves_Tempcp_Block_Adminhtml_Theme_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    var $data = null;
    public function __construct()
    {
        parent::__construct();

        $this->_objectId    = 'id';
        $this->_blockGroup  = 'ves_tempcp';
        $this->_controller  = 'adminhtml_theme';

        $this->_updateButton('save', 'label', Mage::helper('ves_tempcp')->__('Save Theme'));
        $this->_updateButton('delete', 'label', Mage::helper('ves_tempcp')->__('Delete Theme'));

        $this->setTemplate('ves_tempcp/form/edit.phtml');
        
        $mediaHelper = Mage::helper('ves_tempcp/media');
        $mediaHelper->loadMedia();

        $themeHelper = Mage::helper('ves_tempcp/theme');

        $this->data = $themeHelper->initTheme();

       
    }
    protected function _prepareLayout() {
         /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                   $this->getLayout()->createBlock('adminhtml/store_switcher')
                   ->setUseConfirm(false)
                   ->setSwitchUrl($this->getUrl('*/*/*/id/'.Mage::registry('theme_data')->get('theme_id'), array('store'=>null)))
           );
        }

        return parent::_prepareLayout();
    }
    public function getThemeData(){
        return $this->data;
    }
    public function getHeaderText()
    {
        return Mage::helper('ves_tempcp')->__("Venus Theme Control Panel - Edit Theme '%s'", $this->htmlEscape(Mage::registry('theme_data')->get('theme')));
    }

    /**
     *
     */
    public function getFileList( $path , $e=null ) {
        $output = array(); 
        $directories = glob( $path.'*'.$e );
        foreach( $directories as $dir ){
            $output[] = basename( $dir );
        }           
        
        return $output;
    }
    
    public function getContentCustomCss( ) {
        $output = "";
        $theme = Mage::registry('theme_data')->get('group');
        $tmp_theme = explode("/", $theme);
        if(count($tmp_theme) == 1) {
            $theme = "default/".$tmp_theme;
        }
        if($theme) {
            
           $custom_css_path = Mage::getBaseDir('skin')."/frontend/".$theme."/css/local/custom.css";

           if(is_file($custom_css_path) && file_exists($custom_css_path)) {
                $file = new Varien_Io_File();
                $file->open(array('path' => Mage::getBaseDir('skin')."/frontend/".$theme."/css/local/"));
                //$flocal->streamOpen('customers.txt', 'r');
                $output = $file->read(Mage::getBaseDir('skin')."/frontend/".$theme."/css/local/custom.css");
                $file->close();
           }
        }  
         
        return $output;
    }

    public function getBackupByTheme($list_modules = array(), $default_theme = "") {
        $output = array();
        if(!empty($list_modules) && $default_theme) {
            $importDir = Mage::getBaseDir('cache') ."/backup_".str_replace("/","_", $default_theme).'/';
            foreach($list_modules as $key=>$val) {

                if(file_exists($importDir.$key.".".$val['type'])) {
                    $output[$key] = $key; 
                }
            }
        }
        return $output;
    }

    public function getStaticBlocks( $block_id = "") {
        $blocks = Mage::getModel('cms/block')->getCollection()
                                            ->addFilter("is_active", 1)
                                            ->getItems();

        $html = '<option value="0">'.Mage::helper('ves_tempcp')->__("---- Select a Static Block ----").'</option>';
        if(!empty($blocks)){
            foreach($blocks as $block){
                $title = $block->getTitle();
                $title = str_replace("'","", $title);
                if($block_id == $block->getIdentifier()) {
                    $html .= '<option value="'.$block->getIdentifier().'" selected="selected">'.$title.'</option>';
                } else {
                    $html .= '<option value="'.$block->getIdentifier().'">'.$title.'</option>';
                }
                
            }
        }
        return $html;
    }

    public function getListLayouts( $module_layout = array()) {
        $options = array(
                        'cms',
                        'contacts', 
                        'catalog-product',
                        'catalog-category',
                        'checkout',
                        'tag',
                        'catalogsearch',
                        'sales',
                        'customer',
                        'wishlist',
                        'review',
                        'oauth',
                        'newsletter',
                        'downloadable');

        $layouts = array(
                    "all" => Mage::helper('ves_tempcp')->__("All Pages"),
                    "home" => Mage::helper('ves_tempcp')->__("Home"));
        if($options){
            foreach($options as $option){
                $layouts[$option] = Mage::helper('ves_tempcp')->__($option);
            }
        }
        $html = "";
        if(!empty($layouts)){
            foreach($layouts as $key=>$val){
                $val = str_replace("'","\'", $val);
                if(!$module_layout && $key == "all"){
                    $html .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
                }elseif($module_layout && in_array($key, $module_layout)) {
                    $html .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
                } else {
                    $html .= '<option value="'.$key.'">'.$val.'</option>';
                }
            }
        }
        return $html;
    }

    public function getCancelLink(){
        return $this->getUrl('*/adminhtml_theme/index');
    }

    public function getBackupLink( $theme_default = "") {
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/edit', array("id"=>$theme_id, "backup"=>1));
    }

    public function getRestoreLink( $module_name = "", $theme_default = "", $type = "json") {
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/restoreSetting', array("module"=>$module_name, "type"=>$type, "id"=>$theme_id));
    }

    public function getBackupSettingLink( $theme_default = "") {
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/edit', array("id"=>$theme_id, "backupsetting"=>1));
    }

    public function getInstallSampleLink( $module = "", $type = "query", $theme_default = "") {
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/installSample', array("id"=>$theme_id, "module" => $module, "type"=> $type));
    }

    public function getSystemAdvancedConfigLink() {
        return $this->getUrl('*/system_config/edit/section/advanced');
    }

    public function getStoreSampleLink( $theme_default = "") {
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/storesample', array("id" => $theme_id));
    }

    public function getLiveEditLink(){
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/customize', array("id"=>$theme_id));
    }
    public function getCleanCacheLink($theme_default = "") {
        $theme_id = Mage::registry('theme_data')->get('theme_id');
        return $this->getUrl('*/adminhtml_theme/cleancssjscache', array("id" => $theme_id));
    }
    public function getAjaxSaveLink(){
        return $this->getUrl('*/adminhtml_theme/ajaxsave');
    }
    public function getStoreSwitcherHtml() {
       return $this->getChildHtml('store_switcher');
    }
    public function getCustomLink($route , $params = array()){
        $link =  Mage::helper("adminhtml")->getUrl($route, $params);
        $link = str_replace("/adminhtml/","/", $link);
        $link = str_replace("/tempcp/","/", $link);
        $link = str_replace("//admin","/admin", $link);
        return $link;
    }
    public function getDirectivesLink($params = array()){
       return $this->getCustomLink("*/cms_wysiwyg/directive", $params);
    }
    public function getVariablesLink($params = array()){
       return $this->getCustomLink("*/system_variable/wysiwygPlugin", $params);
    }
    public function getImagesLink($params = array()){
       return $this->getCustomLink("*/cms_wysiwyg_images/index", $params);
    }
    public function getWidgetLink($params = array()){
        return $this->getCustomLink("*/widget/index", $params);
    }
}