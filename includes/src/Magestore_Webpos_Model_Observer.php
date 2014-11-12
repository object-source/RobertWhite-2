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
 * Webpos Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Model_Observer
{
    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Webpos_Model_Observer
     */
    public function controllerActionPredispatch($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }
	
	public function adminlogout($observer){
		$cookie = Mage::getSingleton('core/cookie');
		$cookie->delete('webpos_admin_key');
		$cookie->delete('webpos_admin_code');
		$cookie->delete('webpos_admin_id');
		$cookie->delete('webpos_admin_adminlogout');
		$cookie->delete('webpos_admin_adminlogin');
	}
	
	public function adminlogin($observer){
		if(!Mage::getSingleton('admin/session')->isLoggedIn()){
			$cookie = Mage::getSingleton('core/cookie');
			$cookie->delete('webpos_admin_key');
			$cookie->delete('webpos_admin_code');
			$cookie->delete('webpos_admin_id');
			$cookie->delete('webpos_admin_adminlogout');
			$cookie->delete('webpos_admin_adminlogin');
		}
	}
	
	public function skipPaymentMethod($observers)
	{			
		$result = $observers->getResult();	
		$methodInstance = $observers->getMethodInstance();
		$cookie = Mage::getSingleton('core/cookie');
		$key = $cookie->get('webpos_admin_key');
		$code = $cookie->get('webpos_admin_code');
		$adminId = $cookie->get('webpos_admin_id');
		$action = Mage::app()->getRequest()->getControllerName();//getControllerName();
		$module = Mage::app()->getRequest()->getModuleName();		
		if($key && $code && $adminId && $module =='webpos'){
			if(Mage::getStoreConfig('webpos/payment/allowspecific_payment',Mage::app()->getStore()->getId())=='1'){
				$specificpayment = Mage::getStoreConfig('webpos/payment/specificpayment',Mage::app()->getStore()->getId());
				$specificpayment = explode(',',$specificpayment);
				if(in_array($methodInstance->getCode(),$specificpayment)){
					$result->isAvailable = true;
					$result->isDeniedInConfig = false;			
				}else{
					$result->isAvailable = false;
					$result->isDeniedInConfig = true;			
				}
			}
			// if($methodInstance->getCode() == 'checkmo' || $methodInstance->getCode() == 'ccsave'){
				// $result->isAvailable = true;
				// $result->isDeniedInConfig = false;			
			// }else{
				// $result->isAvailable = false;
				// $result->isDeniedInConfig = true;			
			// }
		}
		return $this;
	}
	
	public function orderPlaceAfter($observers)
	{
		$session = Mage::getSingleton('checkout/session');
		$session->unsetData('webpos_cashin');
		$session->unsetData('webpos_admin_discount');
		$giftwrap = $session->getData('webpos_giftwrap');
		$giftwrapAmount = $session->getData('webpos_giftwrap_amount');
		if($giftwrap || $giftwrapAmount){
			$session->unsetData('webpos_giftwrap');
			$session->unsetData('webpos_giftwrap_amount');
		} 
		//Save Comment
		$order = $observers->getEvent()->getOrder();
		$customerComment = $session->getData('customer_comment');		
		if ($customerComment != "") {
			try {
				$order->setWebposOrderComment($customerComment)->save();
			}
			catch(Exception $e) {
			
			}
		}		
		//Save survey				
		$orderId = $order->getId();
		$surveyQuestion = $session->getData('survey_question');			
		$surveyAnswer = $session->getData('survey_answer');
		$survey = Mage::getModel('webpos/survey');
		if($surveyAnswer){
			try{
				$survey->setData('question', $surveyQuestion)
					   ->setData('answer', $surveyAnswer)
					   ->setData('order_id', $orderId)			   
					   ->save();
			}catch(Exception $e){				
			}
			$session->unsetData('survey_question');
			$session->unsetData('survey_answer');
		}
	}
	
}