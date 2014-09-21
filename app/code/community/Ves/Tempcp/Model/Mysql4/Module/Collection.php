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
class Ves_Tempcp_Model_Mysql4_Module_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag;

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('ves_tempcp/module')
            ->setOrder('sort_order', 'ASC');
    }
    
    /**
     * Creates an options array for grid filter functionality
     *
     * @return array Options array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('module_id', 'sort_order');
    }

    public function addIsActiveFilter()
    {
        $this->addFilter('status', 1);
        return $this;
    }
    
    /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
    }
}
