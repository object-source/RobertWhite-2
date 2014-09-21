<?php
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class Ves_Tempcp_Block_Adminhtml_Cms_Enhanced_Store_Grid extends Ves_Tempcp_Block_Adminhtml_Cms_Enhanced_Abstract_Grid
{

	protected $_isExport = true;

	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('storeGrid');
		$this->setDefaultSort('store_id');
		$this->setDefaultDir('desc');
	}

	/**
	 */
	protected function _prepareColumns()
	{

		$this->addColumn('store_id', array(
				'header'    => Mage::helper('ves_tempcp')->__('Store_Id'),
				'width'     =>'50px',
				'index'     => 'store_id'
		));

		$this->addColumn('code', array(
				'header'    =>Mage::helper('ves_tempcp')->__('Code'),
				'width'     =>'50px',
				'index'     => 'code'
		));

		$this->addColumn('website_id', array(
				'header'    =>Mage::helper('ves_tempcp')->__('Website_id'),
				'width'     =>'50px',
				'index'     => 'website_id'
		));

		$this->addColumn('group_id', array(
				'header'    =>Mage::helper('ves_tempcp')->__('Group_Id'),
				'width'     =>'50px',
				'index'     => 'group_id'
		));

		$this->addColumn('name', array(
				'header'    =>Mage::helper('ves_tempcp')->__('Name'),
				'width'     =>'50px',
				'index'     => 'name'
		));

		$this->addColumn('sort_order', array(
				'header'    =>Mage::helper('ves_tempcp')->__('Sort_Order'),
				'width'     =>'50px',
				'index'     => 'sort_order'
		));

		$this->addColumn('is_active', array(
				'header'    =>Mage::helper('ves_tempcp')->__('Is_Active'),
				'width'     =>'50px',
				'index'     => 'is_active'
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('ves_tempcp')->__('CSV'));

		return parent::_prepareColumns();

	}

	protected function _prepareCollection()
	{
		//$store_collection = Mage::getModel('core/store')->getCollection();
		$store_collection = Mage::getModel('core/store')->getCollection();
		// add the stores this block belongs to
		foreach ($store_collection as $key => $store) {
			$store_group = $store->getGroup();
			$store_name = $store_group->getName()." > ".$store->getName();
			$store->setName($store_name);
		} // end

		/* @var $collection Mage_Core_Model_Mysql4_Store_Collection */
		$this->setCollection($store_collection);
		return parent::_prepareCollection();
	}


}
