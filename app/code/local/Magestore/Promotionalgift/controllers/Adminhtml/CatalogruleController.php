<?php
class Magestore_Promotionalgift_Adminhtml_CatalogruleController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promotionalgift/catalogrule')
            ->_addBreadcrumb(Mage::helper('promotionalgift')->__('Catalog Rules'), Mage::helper('promotionalgift')->__('Catalog Rules'));
        return $this;
    }

    public function indexAction()
    {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
        $this->_title($this->__('Catalog Rules'))->_title($this->__('Catalog Rules'));
        $this->_initAction()
             ->renderLayout();
    }
	
	public function editAction() 
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('promotionalgift/catalogrule')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			$model->getConditions()->setJsFormObject('rule_conditions_fieldset');
			Mage::register('catalogrule_data', $model);
			
			$this->_title($this->__('Promotional Gift'))
				->_title($this->__('Manage rule'));
			if ($model->getId()){
				$this->_title($model->getName());
			}else{
				$this->_title($this->__('New rule'));
			}

			$this->loadLayout();
			$this->_setActiveMenu('promotionalgift/catalogrule');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule News'), Mage::helper('adminhtml')->__('Rule News'));

			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true);

			$this->_addContent($this->getLayout()->createBlock('promotionalgift/adminhtml_catalogrule_edit'))
				->_addLeft($this->getLayout()->createBlock('promotionalgift/adminhtml_catalogrule_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promotionalgift')->__('Rule does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() 
	{
		$this->_forward('edit');
	}
	
	public function giftitemAction()
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$this->loadLayout();
		$this->getLayout()->getBlock('promotionalgift.catalogrule.edit.tab.giftitem')
			->setGiftitems($this->getRequest()->getPost('pcgiftitem',null));
		$this->renderLayout();
	}
	
	public function giftitemGridAction()
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		$this->loadLayout();
		$this->getLayout()->getBlock('promotionalgift.catalogrule.edit.tab.giftitem')
			->setGiftitems($this->getRequest()->getPost('pcgiftitem',null));
		$this->renderLayout();
	}
	
	public function saveAction()
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		if ($data = $this->getRequest()->getPost()) {
			$data = $this->_filterDates($data, array('from_date', 'to_date'));
			if (isset($data['from_date']) && $data['from_date'] == '') $data['from_date'] = null;
			if (isset($data['to_date']) && $data['to_date'] == '') $data['to_date'] = null;
			$model = Mage::getModel('promotionalgift/catalogrule');
			if (isset($data['rule'])){
				$rules = $data['rule'];
				if (isset($rules['conditions']))
					$data['conditions'] = $rules['conditions'];
				if (isset($rules['actions']))
					$data['actions'] = $rules['actions'];
				unset($data['rule']);
			}
			if($data['discount_product']>100)
				$data['discount_product'] = 100;
			if($data['discount_product']<0)
				$data['discount_product'] = 0;
			if(!$data['uses_limit'] && $data['uses_limit'] != '0') $data['uses_limit'] = null;
			if(!$data['time_used'] && $data['time_used'] != '0') $data['time_used'] = null;
			// add data to model
			$model->addData($data)
				->setId($this->getRequest()->getParam('id'));
				//zend_debug::dump($model->getData());die();
			try {
				$model->loadPost($data);
				//save date
				$model->setData('from_date',$data['from_date']);
				$model->setData('to_date',$data['to_date']);
			
				$model->save();
				 //zend_debug::dump($model->getData());die();
				//save list of gift items
				if (isset($data['catalogrule_giftitem'])){
					$giftItems = array();
					parse_str(urldecode($data['catalogrule_giftitem']),$giftItems);
					if (count($giftItems)){
						$productIds = '';
						$qtys = '';
						$count = 0;
						foreach ($giftItems as $pId => $enCoded){
							$codeArr = array(); 
							parse_str(base64_decode($enCoded),$codeArr);
							if(!$codeArr['gift_qty']) $codeArr['gift_qty'] = 1;
							if($count == 0){
								$productIds .= $pId;
								$qtys .=  $codeArr['gift_qty'];
							}else{
								$productIds .= ','.$pId;
								$qtys .=  ','.$codeArr['gift_qty'];
							}
							$count++;
						}
						$catalogItem = Mage::getModel('promotionalgift/catalogitem')
											->getCollection()
											->addFieldToFilter('rule_id',$model->getId())
											->getFirstItem();
						if($catalogItem->getId()){
							$catalogItem->setRuleId($model->getId())
										->setProductIds($productIds)
										->setGiftQty($qtys)
										->save();
						}else{
							Mage::getModel('promotionalgift/catalogitem')
								->setRuleId($model->getId())
								->setProductIds($productIds)
								->setGiftQty($qtys)
								->save();
						}
					}
				
				}
				Mage::getModel('promotionalgift/catalogrule')->updateRuleProductData($model);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('promotionalgift')->__('Catalog rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array(
						'id' => $model->getId(),
					));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                	'id' 	=> $this->getRequest()->getParam('id'),
                ));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promotionalgift')->__('Unable to find catalog rule to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() 
	{
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {return;}
		if( $this->getRequest()->getParam('id') > 0 ){
			try {
				$model = Mage::getModel('promotionalgift/catalogrule');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rule was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array(
					'id' 	=> $this->getRequest()->getParam('id'),
					'store' => $this->getRequest()->getParam('store'),
				));
			}
		}
		$this->_redirect('*/*/',array('store' => $this->getRequest()->getParam('store')));
	}
}
