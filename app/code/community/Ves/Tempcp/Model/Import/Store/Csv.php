<?php
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class Ves_Tempcp_Model_Import_Store_Csv extends Mage_Core_Model_Abstract {

	private $array_delimiter = ';';
	private $delimiter = ',';
	private $header_columns;
	protected $_modelname = 'core/store';

	private function openFile($filepath) {
		$handle = null;
		if (($handle = fopen($filepath, "r")) !== FALSE) {
			return $handle;
		} else {
			throw new Exception('Error opening file ' . $filepath);
		} // end

	} // end

	public function process($filepath, $stores = array()) {

		$handle = $this->openFile($filepath);
		$defaultStoreId = Mage::app()
								->getWebsite(true)
								->getDefaultGroup()
								->getDefaultStoreId();
		$default_store = Mage::app()->getStore($defaultStoreId);
		$default_website_id = $default_store->getWebsiteId();
		$default_group_id = $default_store->getGroupId();
		$row = 0;

		if ( $handle != null ) {

			// loop thru all rows
			while (($data = fgetcsv($handle, 110000, $this->delimiter)) !== FALSE) {
				$row++;

				// if this is the head row keep this as a column reference
				if ($row == 1) {
					$this->mapHeader($data);
					continue;
				}
				$allow_insert = true; 
				// make sure we have a reset model object
				//$staticblock = Mage::getSingleton($this->_modelname)->clearInstance();
				$staticblock = Mage::getModel($this->_modelname);
				$identifier = 0;
				// get the identifier column for this row
				$id_key = $this->getIdentifierColumnIndex();
				if( $id_key !== NULL && $id_key >= 0 ) {
					$identifier = $data[$id_key];

					// if a store already exists for this identifier - dont allow insert the store view
					if($identifier && in_array($identifier, $stores)) {
						$allow_insert = false;
					}
				}

				// if a store already exists for this identifier - load the data
				if($allow_insert) {
					// loop through each column
					$bind_data = array();
					foreach ($this->header_columns as $index => $keyname) {
						$keyname = strtolower($keyname);
						$bind_data[$keyname] = html_entity_decode($data[$index]);

					} // end for
					$bind_data["website_id"] = $default_website_id;
					$bind_data["group_id"] = $default_group_id;
					
					
					// save our block
					try {
						Mage::getModel("ves_tempcp/theme")->insertNewStoreView( $bind_data );
						Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_tempcp')->__('Updated ' . $identifier));
					} catch (Exception $e) {
						Mage::throwException($e->getMessage() . ' URL Key = ' . $data[$this->getIdentifierColumnIndex()]);
					}
				}
				
			} // end while
		}// end if
	} // end
	private function mapHeader($data_array) {
		$this->header_columns = $data_array;
	}
	private function getIdentifierColumnIndex() {
		$header = $this->header_columns;
		$index = array_search('Store_Id', $header);
		return $index;
	}
}
