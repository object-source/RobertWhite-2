<?php
class Magestore_Promotionalgift_Adminhtml_ReportcartruleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('promotionalgift/adminhtml_reportcartrule'));
		$this->renderLayout();
	}	
	
	public function dashboardAction()
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		
		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('promotionalgift/adminhtml_reportcartrule_dashboard'));
		$this->renderLayout();
	}
	
	 
    public function ajaxBlockAction(){
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
    	$output = '';
    	$output = $this->getLayout()->createBlock("promotionalgift/adminhtml_reportcartrule_giftorder")->toHtml();
    	$this->getResponse()->setBody($output);
    }
}
