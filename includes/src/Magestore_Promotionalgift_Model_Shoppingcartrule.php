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
 * Promotionalgift Model
 * 
 * @category    Magestore
 * @package     Magestore_Promotionalgift
 * @author      Magestore Developer
 */
class Magestore_Promotionalgift_Model_Shoppingcartrule extends Mage_Rule_Model_Rule {

    public function _construct() {
        parent::_construct();
        $this->_init('promotionalgift/shoppingcartrule');
    }

    public function getConditionsInstance() {
        return Mage::getModel('salesrule/rule_condition_combine');
    }

    public function getActionsInstance() {
        return Mage::getModel('salesrule/rule_condition_product_combine');
    }

    /**
     * Fix error when load and save with collection
     */
    protected function _afterLoad() {
        $this->setConditions(null);
        $this->setActions(null);
        return parent::_afterLoad();
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }
        if ($this->hasDaily()) {
            $daily = $this->getDaily();
            if (is_array($daily) && !empty($daily)) {
                $this->setDaily(implode(',', $daily));
            }
        }
        if ($this->hasMonthly()) {
            $monthly = $this->getMonthly();
            if (is_array($monthly) && !empty($monthly)) {
                $this->setMonthly(implode(',', $monthly));
            }
        }
        if ($this->hasWeekly()) {
            $weekly = $this->getWeekly();
            if (is_array($weekly) && !empty($weekly)) {
                $this->setWeekly(implode(',', $weekly));
            }
        }
        if ($this->hasYearly()) {
            $yearly = $this->getYearly();
            if (is_array($yearly) && !empty($yearly)) {
                $this->setYearly(implode(',', $yearly));
            }
        }
        return $this;
    }

    /*
      List available shoppingcart rules
     */

    public function getAvailableRule($ruleIds = array()) {
        // if(!Mage::helper('promotionalgift')->checkModuleEnable()) return null;
        $availableRules = $this->getCollection();
        if (isset($ruleIds) && count($ruleIds) > 0) {
            $availableRules = $availableRules->addFieldToFilter('rule_id', array('nin' => array($ruleIds)));
        }
        $availableRules->addFieldToFilter('website_ids', array('finset' => Mage::app()->getStore()->getWebsiteId()));
        $availableRules->addFieldToFilter('status', '1');
        $availableRules->addFieldToFilter('number_item_free', array('gt' => 0));
        $availableRules->addFieldToFilter('coupon_type', '1');
        // $availableRules->addFieldToFilter('coupon_code', array('null'=>1));
        // $availableRules->addFieldToFilter('uses_per_coupon', array('gt'=>0));
        if (Mage::getModel('customer/session')->isLoggedIn()) {
            $customer = Mage::getModel('customer/customer')->load(Mage::getModel('customer/session')->getCustomerId());
            $availableRules->addFieldToFilter('customer_group_ids', array('finset' => $customer->getGroupId()));
        } else {
            $availableRules->addFieldToFilter('customer_group_ids', array('finset' => Mage_Customer_Model_Group::NOT_LOGGED_IN_ID));
        }
        $availableRules->getSelect()->where('(uses_per_coupon IS NULL) OR (uses_per_coupon > 0)');
        $availableRules->getSelect()->where('(from_date IS NULL) OR (date(from_date) <= date(?))', date("Y-m-d", strtotime(now())));
        $availableRules->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', date("Y-m-d", strtotime(now())));
        $availableRules->setOrder('priority', 'ASC')->setOrder('rule_id', 'DESC');
        if (count($availableRules))
            return $availableRules;
        return null;
    }

    public function getAvailableCouponRule($ruleIds = array()) {
        // if(!Mage::helper('promotionalgift')->checkModuleEnable()) return null;
        $availableRules = $this->getCollection();
        if (isset($ruleIds) && count($ruleIds) > 0) {
            $availableRules = $availableRules->addFieldToFilter('rule_id', array('nin' => array($ruleIds)));
        }
        $availableRules->addFieldToFilter('website_ids', array('finset' => Mage::app()->getStore()->getWebsiteId()));
        $availableRules->addFieldToFilter('status', '1');
        $availableRules->addFieldToFilter('number_item_free', array('gt' => 0));
        if (Mage::getModel('customer/session')->isLoggedIn()) {
            $customer = Mage::getModel('customer/customer')->load(Mage::getModel('customer/session')->getCustomerId());
            $availableRules->addFieldToFilter('customer_group_ids', array('finset' => $customer->getGroupId()));
        } else {
            $availableRules->addFieldToFilter('customer_group_ids', array('finset' => Mage_Customer_Model_Group::NOT_LOGGED_IN_ID));
        }
        $availableRules->getSelect()->where('(uses_per_coupon IS NULL) OR (uses_per_coupon > 0)');
        $availableRules->getSelect()->where('(from_date IS NULL) OR (date(from_date) <= date(?))', date("Y-m-d", strtotime(now())));
        $availableRules->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', date("Y-m-d", strtotime(now())));
        $availableRules->setOrder('priority', 'ASC')->setOrder('rule_id', 'DESC');
        if (count($availableRules))
            return $availableRules;
        return null;
    }

    public function validateQuote($quote) {
        $oldsubtotal = Mage::getModel('checkout/cart')->getQuote()->getSubtotal();
        $newsubtotal = Mage::getModel('checkout/cart')->getQuote()->getSubtotal();
        $quoteId = Mage::getModel('checkout/cart')->getQuote()->getId();
        $shoppingGifts = array();
        foreach (Mage::getModel('checkout/cart')->getItems() as $item) {
            $itemId = $item->getId();
            if ($item->getQuote()->isVirtual()) {
                $address = $item->getQuote()->getBillingAddress();
            } else {
                $address = $item->getQuote()->getShippingAddress();
            }
            $itemGift = Mage::getModel('checkout/cart')->getQuote()->getItemById($itemId);
            if ($itemGift) {
                $itemOptions = $itemGift->getOptions();
                foreach ($itemOptions as $option) {
                    $oData = $option->getData();
                    if(!$itemGift->getParentItemId()) {
                        if ($oData['code'] == 'option_promotionalgift_catalogrule') {
                            $next = $oData['item_id'];
                            $giftValue = $itemGift->getRowTotal();
                            $realValue = $newsubtotal - $giftValue;
                            if ((int) $realValue >= 0) {
                                $newsubtotal -= $giftValue;
                            }
                        }
                        if ($oData['code'] == 'option_promotionalgift_shoppingcartrule') {
                            $shoppingGifts[] = $itemGift->getId();
                            $giftValue = $itemGift->getRowTotal();
                            $realValue = $newsubtotal - $giftValue;
                            if ((int) $realValue >= 0) {
                                $newsubtotal -= $giftValue;
                            }
                        }
                    }
                    if ($next == $itemId)
                        continue;
                }
            }else {
                continue;
            }
        }
        if ((int) $newsubtotal > 0) {
            $address->setSubtotal($newsubtotal);
            $address->setBaseSubtotal($newsubtotal);
            $availableRules = $this->getAvailableRule();
            $newAvailableRules = array();
            $ruleIds = array();
            if (count($availableRules)) {
                foreach ($availableRules as $availableRule) {
					$availableRule->afterLoad();
                    if ($availableRule->validate($address)) {
                        $newAvailableRules[] = $availableRule;
                        $ruleIds[] = $availableRule->getId();
                    }
                }
                $address->setSubtotal($oldsubtotal);
                $address->setBaseSubtotal($oldsubtotal);
                if (count($newAvailableRules)) {
                    Mage::helper('promotionalgift')->deleteGiftItemOfRule($ruleIds);
                    return $newAvailableRules;
                } else {
                    Mage::helper('promotionalgift')->deleteGiftItemOfRule($ruleIds);
                    return false;
                }
            } else {
                $address->setSubtotal($oldsubtotal);
                $address->setBaseSubtotal($oldsubtotal);
                return false;
            }
        } else {
            foreach ($shoppingGifts as $gItem) {
                Mage::getModel('promotionalgift/shoppingquote')->getCollection()
                    ->addFieldToFilter('item_id', $gItem)
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->getFirstItem()
                    ->delete();
                Mage::getModel('checkout/cart')->getQuote()->removeItem($gItem)->save();
            }
//            Mage::getModel('checkout/cart')->truncate()->save();
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart/index'));
        }
    }

    public function checkRule($order) {
        if ($this->getIsActive()) {
            $this->afterLoad();
            return $this->validate($order);
        }
        return false;
    }

    public function checkCouponCodeExist($couponCode, $ruleId) {
        $rules = $this->getCollection()->addFieldToFilter('coupon_code', $couponCode)
            ->addFieldToFilter('rule_id', array('nin' => array($ruleId)));
        if (isset($rules) and $rules->getSize() > 0) {
            return true;
        }
        return false;
    }

}