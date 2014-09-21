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
class Ves_Tempcp_Model_Mysql4_Module extends Mage_Core_Model_Mysql4_Abstract {
	/**
	 * Constructor
	 * 
	 */
	protected function _construct() {

		$this->_init('ves_tempcp/module', 'module_id');
	}

}