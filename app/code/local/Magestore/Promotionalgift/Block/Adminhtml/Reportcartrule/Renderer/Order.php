<?php 
class Magestore_Promotionalgift_Block_Adminhtml_Reportcartrule_Renderer_Order
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row) 
	{
        if($row->getOrderId()){
            return sprintf('
                <a href="%s" title="%s">%s</a>',
                $this->getUrl('adminhtml/sales_order/view/', array('_current'=>true, 'order_id' => $row->getOrderId())),
                Mage::helper('catalog')->__('View Order Detail'),
                $row->getOrderIncrementId()
            );
        }else{
            return sprintf('%s', $row->getOrderIncrementId());
        }
	}
}