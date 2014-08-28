<?php

class Magestore_Promotionalgift_Block_Toplink extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
    }

    public function _prepareLayout() {
        $store = Mage::app()->getStore()->getId();
        parent::_prepareLayout();
        if (!Mage::getStoreConfig('promotionalgift/general/enable', $store)) {
            return $this;
        }
        $this->addPromotionalGiftToplink();
    }

    public function addPromotionalGiftToplink() {
        $block = $this->getLayout()->getBlock('top.links');
        $block->addLink('Promotional Gift', Mage::helper('promotionalgift')->getPromotionalgiftUrl(), Mage::helper('promotionalgift')->__('Promotional Gift'), '', '', 10);
    }
}