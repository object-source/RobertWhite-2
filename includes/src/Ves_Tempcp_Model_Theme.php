<?php
/**
 * Tempcp for Magento
 *
 * @category   Ves
 * @package    Ves_Tempcp
 * @copyright  Copyright (c) 2009 Ves GmbH & Co. KG <magento@Ves.de>
 */

/**
 * Tempcp for Magento
 *
 * @category   Ves
 * @package    Ves_Tempcp
 * @author     Landofcoder <landofcoder@gmail.com>
 */
class Ves_Tempcp_Model_Theme extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('ves_tempcp/theme');
    }
    public function loadThemeByGroup($group = "", $enable_cache = false){
        $store_id = Mage::app()->getStore()->getStoreId();
        $theme_id = 0;
        if($enable_cache){
            $cache = Mage::app()->getCache();
            $group_name = str_replace("/","_", $group_name);
            if(!$theme_id = $cache->load( $group_name."_".$store_id )){
                $theme_id = $this->getResource()->loadThemeByGroup( $group, $store_id );
                $cache->save($theme_id, $group_name."_".$store_id, array($group_name,"theme_".$theme_id), 60*60);
            }
        }else{
            $theme_id = $this->getResource()->loadThemeByGroup( $group, $store_id );
        }
        
        if(!empty($theme_id)){
           return $this->load($theme_id);
        }

        return false;
    }
    public function checkExistsByGroup($group = ""){
        if($group){
            $data = $this->getResource()->loadThemeByGroup( $group );
            if($data && count($data) > 0){
                return true;
            }else{
                return false;
            }
        }
        
        return true;
    }

    public function getStoresByTheme( $theme_id = 0) {
        $stores = array();
        if(!empty($theme_id)){
           $model = $this->load($theme_id);
           $stores = $model->getStoreId();
        }
        return $stores;
    }

    public function insertNewStoreView($data = array()){
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('core/store');
        // now $write is an instance of Zend_Db_Adapter_Abstract
        $writeresult = $write->query("INSERT IGNORE INTO `".$table."` (`store_id`,`code`,`website_id`,`group_id`,`name`, `sort_order`,`is_active`) VALUES (:store_id,:code,:website_id,:group_id,:name,:sort_order,:is_active)", $data);
        return $writeresult;
    }
}
