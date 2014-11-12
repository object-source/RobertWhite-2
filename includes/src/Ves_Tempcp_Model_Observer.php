<?php

class Ves_Tempcp_Model_Observer {

	public function initControllerPredispatch(Varien_Event_Observer $observer) {

		$this->_initFramework();
    }
    public function beforeRender(Varien_Event_Observer $observer) {

		$this->_initFramework();
    }

    function _initFramework() {
		Mage::getSingleton('core/session', array('name'=>'adminhtml'));
		if(Mage::getSingleton('admin/session')->isLoggedIn()){
		  //do stuff
		} else {
			$helper = Mage::helper("ves_tempcp/framework")->getFramework( );

			if(!$helper || !is_object($helper)) {
				$packageName =  Mage::getSingleton('core/design_package')->getPackageName();
				if(!$packageName) {
					$packageName = "default";
				}

				$themeName =  Mage::getDesign()->getTheme('frontend');
				$themeName = $packageName."/".$themeName;
				$themeConfig = Mage::helper('ves_tempcp/theme')->getCurrentTheme();
				$helper = Mage::helper("ves_tempcp/framework")->initFramework( $themeName, $themeConfig );
			}
		}
    }

}
