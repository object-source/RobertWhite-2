<?php 
class Magestore_Promotionalgift_Block_Adminhtml_Reportcartrule_Renderer_Product
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	//show each product in a row
	public function render(Varien_Object $row) 
	{
        if($row->getOrderId()){
            $html = $this->getBackendProductHtmls($row->getProductIds(),$row->getProductNames());
            return sprintf('%s', $html);
        }  else {
            return sprintf('%s', $row->getOrderItemNames());
        }
	}
	
	public function getBackendProductHtmls($productIds,$productNames) 
	{
        $productHtmls = array();
        $productIds = explode(',', $productIds);
		$productNames = explode(';', $productNames);
		$count = 0;
        foreach ($productIds as $productId) {
            $productName = Mage::getModel('catalog/product')->load($productId)->getName();
			if($productName){
				$productUrl = $this->getUrl('adminhtml/catalog_product/edit/', array('_current' => true, 'id' => $productId));
				$productHtmls[] = '<a href="' . $productUrl . '" title="' . Mage::helper('promotionalgift')->__('View Product Detail') . '">' . $productName . '</a>';
			}else{
				$productHtmls[] = $productNames[$count];
			}
			$count++;
        }
        return implode('<br />', $productHtmls);
    }
}