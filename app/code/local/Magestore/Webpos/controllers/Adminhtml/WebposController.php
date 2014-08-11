<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Adminhtml_WebposController extends Mage_Adminhtml_Controller_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$this->loadLayout();
        $this->renderLayout();
	}	
	
	public function gotowebposAction()
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$storeId = $this->getRequest()->getParam('webpos_storeid');
		$adminId = Mage::getModel('admin/session')->getUser()->getId();
		$key = Mage::getModel('adminhtml/session')->getSessionId();
		$adminLogin = $this->getUrl('webposadmin/adminhtml_webpos/index', array('_secure'=>true));
		$adminLogout = Mage::getBlockSingleton('adminhtml/page_header')->getLogoutLink();
		$random = md5(now());
		$cookieTime = Mage::getStoreConfig('web/cookie/cookie_lifetime');
		$code = md5($key.$random);
		$cookie = Mage::getSingleton('core/cookie');
		$cookie->setLifeTime($cookieTime);
		$cookie->set('webpos_admin_key', $key);
		$cookie->set('webpos_admin_code', $code);
		$cookie->set('webpos_admin_id', $adminId);
		$cookie->set('webpos_admin_adminlogout', $adminLogout);
		$cookie->set('webpos_admin_adminlogin', $adminLogin);
		$webposAdmin = Mage::getModel('webpos/admin')->load($key,'key');
		if($webposAdmin->getId()){
			$webposAdmin->delete();
		}
		try{
			Mage::getModel('webpos/admin')
				->setData('key',$key)
				->setData('random',$random)
				->save();
		}catch(Exception $e){	
		}
		$urlRedirect = Mage::getModel('core/store')->load($storeId)->getUrl('webpos/index/index', array('_secure'=>true));
		header('Location:'.$urlRedirect);
		exit();
	}
}