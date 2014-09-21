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
class Ves_Tempcp_Model_Module extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('ves_tempcp/module');
    }

    public function cleanModules( $theme_id = 0) {
    	if($theme_id) {
    		 /**
	         * Get the resource model
	         */
	        $resource = Mage::getSingleton('core/resource');
	         
	        /**
	         * Retrieve the write connection
	         */
	        $writeConnection = $resource->getConnection('core_write');

	        $table_name = $resource->getTableName("ves_tempcp/module");

	        $writeConnection->query("DELETE FROM `{$table_name}` WHERE `theme_id`=".(int)$theme_id);
    	}
    }

    public function getModuleId( $module_key = "") {
    	if($module_key) {
    		return $this->getCollection()
    				->addFieldToFilter('module_name', $module_key)
    				->getFirstItem()
    				->getId();
    	}
    	return 0;
    }

    public function getModulesByTheme ( $theme_id = 0, $check_status = null) {
    	if($theme_id) {
    		$collection = $this->getCollection()
    				->addFieldToFilter('theme_id', $theme_id)
    				->setOrder('sort_order', 'ASC');

            if($check_status !== null) {
                $collection->addFieldToFilter('status', $check_status);
            }
            return $collection;
    	}
    	return false;
    }
}