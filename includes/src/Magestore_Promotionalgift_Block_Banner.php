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
 * @package     Magestore_Promotionalgift
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Promotionalgift Block
 * 
 * @category    Magestore
 * @package     Magestore_Promotionalgift
 * @author      Magestore Developer
 */
class Magestore_Promotionalgift_Block_Banner extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_Promotionalgift_Block_Shoppingcart
     */
    public function _prepareLayout() {
        $controller = Mage::app()->getRequest()->getControllerName();
        $module = Mage::app()->getRequest()->getModuleName();
        if ($module == 'onestepcheckout') {
            $this->setTemplate('promotionalgift/multiplecheckoutbanner.phtml');
        } else {
            if ($controller == 'onepage') {
                $this->setTemplate('promotionalgift/multiplecheckoutbanner.phtml');
            } else {
                $this->setTemplate('promotionalgift/banner.phtml');
            }
        }
        return parent::_prepareLayout();
    }

    public function getTotalItem($rule) {
        $item = array();
        if ($rule != false) {
            $ruleItems = Mage::getModel('promotionalgift/shoppingcartitem')->load($rule->getId(), 'rule_id')
                ->getProductIds();
            $items = explode(',', $ruleItems);
            $totalItems = count($items);
            return $totalItems;
        }
        return false;
    }

    public function getShoppingcartRule() {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $rules = Mage::getModel('promotionalgift/shoppingcartrule')->validateQuote($quote);
        $session = Mage::getModel('checkout/session');
        $quoteId = Mage::getModel('checkout/session')->getQuote()->getId();
        $ruleIds = array();
        $isMultiple = Mage::getStoreConfig('promotionalgift/general/multipleshoppingcartrule');
        if ($isMultiple != 1) {
            if ($this->getCouponCodeRule()) {
                return $this->getCouponCodeRule();
            }
            if ($rules != false) {
                $rule = $rules[0];
                $maxItems = $rule->getNumberItemFree();
                $checkCalendar = Mage::helper('promotionalgift')->checkCalendar($rule);
                $ruleUsed = Mage::getModel('promotionalgift/shoppingquote')
                    ->getCollection()
                    ->addFieldToFilter('shoppingcartrule_id', $rule->getId())
                    ->addFieldToFilter('quote_id', $quoteId);
                if (count($ruleUsed) == 0 || count($ruleUsed) < $maxItems) {
                    if ($checkCalendar == true) {
                        $ruleIds[] = $rule->getId();
                    }
                }
                return $ruleIds;
            } else {
                return false;
            }
        } else {
            $number = Mage::getStoreConfig('promotionalgift/general/numberofshoppingcartrule');
            $checkRulesUsed = array();
            if ($this->getCouponCodeRule()) {
                $couponRules = $this->getCouponCodeRule();
                foreach ($couponRules as $couponRule) {
                    $rules[] = Mage::getModel('promotionalgift/shoppingcartrule')->load($couponRule);
                }
            }
            foreach ($rules as $rule) {
                $checkCalendar = Mage::helper('promotionalgift')->checkCalendar($rule);
                $ruleUsed = Mage::getModel('promotionalgift/shoppingquote')
                    ->getCollection()
                    ->addFieldToFilter('shoppingcartrule_id', $rule->getId())
                    ->addFieldToFilter('quote_id', $quoteId);
                $maxItems = $rule->getNumberItemFree();
                if (count($ruleUsed)) {
                    $checkRulesUsed[] = $rule->getId();
                }
                if (count($ruleUsed) == 0 || (int)(count($ruleUsed)) < (int)$maxItems) {
                    if ($checkCalendar == true) {
                        $ruleIds[] = $rule->getId();
                    }
                }
                if (count($checkRulesUsed) == $number)
                    break;
            }
            if (count($checkRulesUsed) == $number) {
                $ruleIds = array();
                foreach ($checkRulesUsed as $checkRuleUsed) {
                    $numberItemsAdded = Mage::getModel('promotionalgift/shoppingquote')
                        ->getCollection()
                        ->addFieldToFilter('shoppingcartrule_id', $checkRuleUsed)
                        ->addFieldToFilter('quote_id', $quoteId);
                    $numberItemFree = Mage::getModel('promotionalgift/shoppingcartrule')
                        ->load($checkRuleUsed)
                        ->getNumberItemFree();
                    if ((int)(count($numberItemsAdded)) < (int)$numberItemFree) {
                        $ruleIds[] = $checkRuleUsed;
                    }
                }
            }
            if (count($ruleIds) > 0) {
                $session->setData('promotionalgift_shoppingcart_use_full_rule', null);
                return $ruleIds;
            } else {
                $session->setData('promotionalgift_shoppingcart_use_full_rule', null);
                if (count($checkRulesUsed) == $number) {
                    $session->setData('promotionalgift_shoppingcart_use_full_rule', true);
                }
                return false;
            }
        }
    }

    public function getCouponCodeRule() {
        $session = Mage::getModel('checkout/session');
        $ruleId = $session->getData('promotionalgift_shoppingcart_rule_id');
        $ruleUsed = $session->getData('promotionalgift_shoppingcart_rule_used');
        $ruleIds = array();
        // Zend_debug::dump($ruleUsed);die();
        if ($ruleUsed)
            return false;
        if ($ruleId)
            $rule = Mage::getModel('promotionalgift/shoppingcartrule')->load($ruleId);
        if ($rule != false) {
            $ruleIds[] = $ruleId;
            return $ruleIds;
        }else
            return false;
    }

    public function getRulePriorityLess($rule, $couponRule = false) {
        $ruleIds = array($rule->getId());
        $rules = Mage::getModel('promotionalgift/shoppingcartrule')->getCollection()
            ->addFieldToFilter('priority', array('gteq' => $rule->getPriority(),
            ));

        if ($couponRule) {
            $ruleIds = array_merge_recursive(array($rule->getId()), array($couponRule->getId()));
            $rules = Mage::getModel('promotionalgift/shoppingcartrule')->getCollection()
                ->addFieldToFilter('priority', array('gteq' => $rule->getPriority(),
                'gteq' => $couponRule->getPriority()
                ));
        }
        $ruleIds = array_merge_recursive($ruleIds, $rules->getAllIds());
        $ruleIds = array_unique($ruleIds);
        return $ruleIds;
    }

    public function getQtyProductRule($product, $rule) {
        if ($rule != false) {
            $giftitems = Mage::helper('promotionalgift')->getShoppingcartFreeGifts($rule->getId());
            // Zend_debug::dump($giftitems);die('32');
            foreach ($giftitems as $giftitem) {
                if ($giftitem['product_id'] == $product->getId())
                    return $giftitem['gift_qty'];
            }
        }
        return false;
    }

    public function getListShoppingcartRule() {
        $couponRuleActivedIds = $this->getCouponCodeRule();
        if ($couponRuleActivedIds) {
            $couponRuleId = $couponRuleActivedIds[0];
            $couponRuleActived = Mage::getModel('promotionalgift/shoppingcartrule')->load($couponRuleId);
        }

        $ruleActivedIds = $this->getShoppingcartRule();
        if ($ruleActivedIds) {
            foreach ($ruleActivedIds as $ruleId) {
                $rule = Mage::getModel('promotionalgift/shoppingcartrule')->load($ruleId);
                $shoppingcartRules = Mage::getModel('promotionalgift/shoppingcartrule')
                    ->getAvailableRule();
                if (isset($ruleId) && $ruleId > 0) {
                    $rulePriorityLess = $this->getRulePriorityLess($rule, $couponRuleActived);
                    // Zend_debug::dump($rulePriorityLess);die();
                    $shoppingcartRules = Mage::getModel('promotionalgift/shoppingcartrule')
                        ->getAvailableRule($rulePriorityLess);
                }
            }
            return $shoppingcartRules;
        }
    }

    public function getRuleName($name) {
        if (strlen($name) >= 85) {
            $name = substr($name, 0, 84);
            $name = $name . '...';
        }
        return $name;
    }

}