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
 * Promotionalgift Helper
 * 
 * @category    Magestore
 * @package     Magestore_Promotionalgift
 * @author      Magestore Developer
 */
class Magestore_Promotionalgift_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getShoppingcartFreeGifts($ruleId) {
        $freeGifts = Mage::getModel('promotionalgift/shoppingcartitem')
            ->load($ruleId, 'rule_id');
        $productIds = explode(',', $freeGifts->getProductIds());
        $qtyItems = explode(',', $freeGifts->getGiftQty());
        $i = 0;
        $giftitems = array();
        if ($freeGifts->getProductIds()) {
            foreach ($productIds as $productId) {
                $giftitems[] = array('gift_qty' => $qtyItems[$i],
                    'product_id' => $productId,
                );
                $i++;
            }
        }
        return $giftitems;
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
    
    public function getCategoryRuleFreeGifts($ruleId) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $freeGifts = Mage::getModel('promotionalgift/catalogitem')
            ->load($ruleId, 'rule_id');
        $productIds = explode(',', $freeGifts->getProductIds());
        $qtyProduct = $this->qtyProduct($productIds, $ruleId);
        $qtyItems = explode(',', $freeGifts->getGiftQty());
        $i = 0;
        $giftitems = array();
        if ($freeGifts->getProductIds()) {
            foreach ($productIds as $productId) {
                $product = new Mage_Catalog_Model_Product();
                $product->load($productId);
                if ($product->getStatus() != 2) {
                    $qtyItem = $qtyProduct * $qtyItems[$i] - ($this->checkItem($productId, $ruleId));
                    if ($qtyItem > 0) {
                        $giftitems[] = array(
                            'rule' => $ruleId,
                            'gift_qty' => $qtyItem,
                            'product_id' => $productId,
                        );
                    }
                    $i++;
                }
            }
        }//zend_debug::dump($giftitems);die();
        return $giftitems;
    }

    public function qtyProduct($productIds, $ruleId) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $i = 0;
        $productGroup = 0;
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $availableRule = Mage::getModel('promotionalgift/catalogrule')->validateItem($item->getProductId());
                if ($availableRule) {
                    if ($availableRule->getId() == $ruleId) {
                        $freeGifts = Mage::getModel('promotionalgift/quote')->getCollection();
                        $freeGifts->addFieldToFilter('item_id', $item->getId());
                        $shoppingcartQuote = Mage::getModel('promotionalgift/shoppingquote')->getCollection()
                            ->addFieldToFilter('item_id', $item->getId());
                        if (!count($freeGifts) && !count($shoppingcartQuote)) {
                            $quoteOptions = Mage::getModel('sales/quote_item_option')->getCollection()
                                ->addFieldToFilter('item_id', $item->getId())
                                ->addFieldToFilter('code', 'product_type')
                                ->addFieldToFilter('value', 'grouped')
                                ->getFirstItem();

                            if ($quoteOptions && $quoteOptions->getId()) {
                                if ($productGroup != $quoteOptions->getProductId()) {
                                    $i++;
                                    $productGroup = $quoteOptions->getProductId();
                                }
                            } else {
                                $i+= $item->getQty();
                            }
                        }
                    }
                }
            }
        }//zend_debug::dump($i);die();
        return $i;
    }

    public function checkItem($productId, $ruleId) {
        $product = new Mage_Catalog_Model_Product();
        $product->load($productId);
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($product->getTypeId() == 'grouped') {
            foreach ($quote->getAllItems() as $item) {
                $freeGifts = Mage::getModel('promotionalgift/quote')->getCollection();
                $freeGifts->addFieldToFilter('item_id', $item->getId())
                    ->addFieldToFilter('grouped_id', $productId)
                    ->addFieldToFilter('catalog_rule_id', $ruleId);
                if (count($freeGifts)) {
                    $qtyGift = $item->getQty();
                    return $qtyGift;
                }
            }
        } else {
            //$quote = Mage::getSingleton('checkout/session')->getQuote();
            $qtyGift = 0;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductId() == $productId) {
                    $freeGifts = Mage::getModel('promotionalgift/quote')->getCollection();
                    $freeGifts->addFieldToFilter('item_id', $item->getId())
                        ->addFieldToFilter('catalog_rule_id', $ruleId);
                    if (count($freeGifts)) {
                        $qtyGift+= $item->getQty();
                    }
                }
            }
            return $qtyGift;
        }

        return 0;
    }
	public function getProductChild($parentItem,$productId,$quoteId)
	{
		$availableRule = Mage::getModel('promotionalgift/catalogrule')->validateItem($productId);
			if($availableRule){
				$totalItems = Mage::getModel('promotionalgift/catalogitem')
						->getCollection()
						->addFieldToFilter('rule_id', $availableRule->getId())
						->getFirstItem()
						->getProductIds();
					;
			}
		return $totalItems;
	}
	
    public function getProductChildId() {
        $productChild = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $productChildArray = array();
        foreach ($productChild as $proCh) {
            $parents = Mage::getModel('catalog/product_type_configurable')
                ->getParentIdsByChild($proCh->getId());
            if (count($parents))
                $productChildArray[] = $proCh->getId();
            else {
                $parents = Mage::getModel('catalog/product_type_grouped')
                    ->getParentIdsByChild($proCh->getId());
                if (count($parents))
                    $productChildArray[] = $proCh->getId();
                else {
                    $parents = Mage::getModel('bundle/product_type')
                        ->getParentIdsByChild($proCh->getId());
                    if (count($parents))
                        $productChildArray[] = $proCh->getId();
                }
            }
        }
        return $productChildArray;
    }

    public function getPromotionalgiftUrl() {
        $url = $this->_getUrl('promotionalgift/index/index', array());
        return $url;
    }

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function enablePromotionalgift() {
        if (!Mage::helper('magenotification')->checkLicenseKey('Promotionalgift')) {
            return false;
        }
        return Mage::getStoreConfig('promotionalgift/general/enable', $this->getStoreId());
    }

    public function getPromotionalIcon() {
        $showGiftLabel = Mage::getStoreConfig('promotionalgift/general/showgiftlabel', $this->getStoreId());
        if ($showGiftLabel)
            return Mage::getStoreConfig('promotionalgift/general/giftlabel', $this->getStoreId());
        return null;
    }

    public function showFreeGift() {
        return Mage::getStoreConfig('promotionalgift/general/showfreegift', $this->getStoreId());
    }

    public function getReportConfig($code, $store = null) {
        return Mage::getStoreConfig('promotionalgift/report/' . $code, $store);
    }

    public function getShoppingcartRule() {
        $session = Mage::getModel('checkout/session');
        $quote = Mage::getModel('checkout/session')->getQuote();
        $rule = Mage::getModel('promotionalgift/shoppingcartrule')->validateQuote($quote);
        if ($rule) {
            return $rule;
        } else {
            return false;
        }
    }

    /**
     * get Mini cart block class
     * 
     * @return string
     */
    public function getMiniCartClass() {
        if (!isset($this->_cache['mini_cart_class'])) {
            $minicartSelect = '';
            if ($minicartBlock = Mage::app()->getLayout()->getBlock('cart_sidebar')) {
                $xmlMinicart = simplexml_load_string($this->toXMLElement($minicartBlock->toHtml()));
                $attributes = $xmlMinicart->attributes();
                if ($id = (string) $attributes->id) {
                    $minicartSelect = "#$id";
                } elseif ($class = (string) $attributes->class) {
                    $minicartSelect = '[class="' . $class . '"]';
                }
            }
            $this->_cache['mini_cart_class'] = $minicartSelect;
        }
        return $this->_cache['mini_cart_class'];
    }

    public function toXMLElement($html) {
        $open = trim(substr($html, 0, strpos($html, '>') + 1));
        $close = '</' . substr($open, 1, strpos($open, ' ') - 1) . '>';
        if ($xml = $open . $close) {
            return $xml;
        }
        return '<div></div>';
    }

    public function deleteGiftItemOfRule($ruleId = null) {
        $session = Mage::getModel('checkout/session');
        $shoppingcartRuleId = $session->getData('promotionalgift_shoppingcart_rule_id');
        $quote = $session->getQuote();
        $quoteId = $quote->getId();
        $cart = Mage::getModel('checkout/cart');
        $shoppingQuotes = Mage::getModel('promotionalgift/shoppingquote')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId)
        ;
        if ($ruleId)
            $shoppingQuotes = $shoppingQuotes->addFieldToFilter('shoppingcartrule_id', array('nin' => $ruleId));
        if ($shoppingcartRuleId) {
            $rule = Mage::getModel('promotionalgift/shoppingcartrule')->load($shoppingcartRuleId);
            if (!$this->validateRuleQuote($rule)) {
                $session->setData('promotionalgift_shoppingcart_rule_id', null);
                $session->setData('shoppingcart_couponcode_rule_id', null);
                $session->setData('promotionalgift_shoppingcart_rule_used', null);
                $session->setData('promptionalgift_coupon_code', null);
            } else {
                $shoppingQuotes = $shoppingQuotes->addFieldToFilter('shoppingcartrule_id', array('nin' => $shoppingcartRuleId));
            }
        }
        $change = 0;
        if (count($shoppingQuotes)) {
            foreach ($shoppingQuotes as $shoppingQuote) {
                try {
                    $item = $cart->getQuote()->getItemById($shoppingQuote->getItemId());
                    if ($item) {
                        $item->delete();
                        $change = 1;
                    }
                    $shoppingQuote->delete();
                    $i++;
                } catch (Exception $e) {
                    
                }
            }
            if ($change == 1) {
                if ($this->enablePromotionalgift()) {
                    $url = Mage::getUrl('checkout/cart/index');
                    if (Mage::getModel('checkout/session')->getData('back_url_promotionalgift')) {
                        $url = Mage::getModel('checkout/session')->getData('back_url_promotionalgift');
                    }
                    header('Location:' . $url);
                    exit;
                }
            }
        }
    }

    public function checkCartRule($shoppingQuotes) {
        $ruleIds = array();
        foreach ($shoppingQuotes as $shoppingQuote) {
            $ruleIds[] = $shoppingQuote->getShoppingcartruleId();
        }
        if (count($ruleIds) == 0) {
            $this->deleteGiftItemOfRule();
        } elseif (count($ruleIds) > 0) {
            $this->deleteGiftItemOfRule($ruleIds);
        }
    }

    public function validateRuleQuote($availableRule) {
        $quote = Mage::getModel('checkout/session')->getQuote();
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $availableRule->afterLoad();
        if ($availableRule->validate($address))
            return $availableRule;
        return false;
    }

    public function checkCalendar($rule) {
        $checkCalendar = false;
        $gift_calendar = $rule->getGiftCalendar();
        $now = getdate();
        if ($gift_calendar == 'all') {
            $checkCalendar = true;
        }
        if ($gift_calendar == 'daily') {
            $daily = $rule->getDaily();
            $mday = $now['mday'];
            $daily = explode(',', $daily);
            if (in_array($mday, $daily)) {
                $checkCalendar = true;
            }
        }
        if ($gift_calendar == 'weekly') {
            $weekly = $rule->getWeekly();
            $wday = $now['weekday'];
            $weekly = explode(',', $weekly);
            if (in_array(strtolower($wday), $weekly)) {
                $checkCalendar = true;
            }
        }
        if ($gift_calendar == 'yearly') {
            $yearly = $rule->getYearly();
            $mon = $now['month'];
            $yearly = explode(',', $yearly);
            if (in_array(strtolower($mon), $yearly)) {
                $checkCalendar = true;
            }
        }
        if ($gift_calendar == 'monthly') {
            $date = date("Y-m-d", Mage::getModel('core/date')->timestamp(now()));
            $week_num = $this->getWeeks($date);
            $monthly = $rule->getMonthly();
            $monthly = explode(',', $monthly);
            if (in_array($week_num, $monthly)) {
                $checkCalendar = true;
            }
        }
        return $checkCalendar;
    }

    public function getWeeks($date) {
        $date_parts = explode('-', $date);
        $date_parts[2] = '01';
        $first_of_month = implode('-', $date_parts);
        $day_of_first = date('N', strtotime($first_of_month));
        $day_of_month = date('j', strtotime($date));
        return floor(($day_of_first + $day_of_month - 1) / 7) + 1;
    }

    public function getAllRuleIds() {
        $ruleIds = array();
        $shoppingcartRules = Mage::getModel('promotionalgift/shoppingcartrule')->getAvailableRule();
        foreach ($shoppingcartRules as $rule) {
            $ruleIds[] = $rule->getId();
        }
        return $ruleIds;
    }

    public function getModuleStatus() {
        $status = Mage::getStoreConfig('promotionalgift/general/enable');
        if ($status == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getCalendarInformation($rule) {
        $gift_calendar = $rule->getGiftCalendar();
        $stringCalendarInfo = '';
        $i = 0;
        if ($gift_calendar == 'all') {
            $checkCalendar = true;
            $stringCalendarInfo = 'All days';
        }
        //daily
        if ($gift_calendar == 'daily') {
            $daily = $rule->getDaily();
            $daily = explode(',', $daily);
            $stringCalendarInfo = 'On the';
            foreach ($daily as $d) {
                $i++;
                if ($i == count($daily)) {
                    if ($d == 1 || $d == 11 || $d == 21 || $d == 31) {
                        $stringCalendarInfo .= ' ' . $d . 'st';
                    } elseif ($d == 2 || $d == 12 || $d == 22) {
                        $stringCalendarInfo .= ' ' . $d . 'nd';
                    } elseif ($d == 3 || $d == 13 || $d == 23) {
                        $stringCalendarInfo .= ' ' . $d . 'rd';
                    } else {
                        $stringCalendarInfo .= ' ' . $d . 'th';
                    }
                } else {
                    if ($d == 1 || $d == 11 || $d == 21 || $d == 31) {
                        $stringCalendarInfo .= ' ' . $d . 'st, ';
                    } elseif ($d == 2 || $d == 12 || $d == 22) {
                        $stringCalendarInfo .= ' ' . $d . 'nd, ';
                    } elseif ($d == 3 || $d == 13 || $d == 23) {
                        $stringCalendarInfo .= ' ' . $d . 'rd, ';
                    } else {
                        $stringCalendarInfo .= ' ' . $d . 'th, ';
                    }
                }
            }
            $stringCalendarInfo .= ' of month';
        }
        //weekly
        if ($gift_calendar == 'weekly') {
            $weekly = $rule->getWeekly();
            $stringCalendarInfo = 'Every ';
            $weekly = explode(',', $weekly);
            foreach ($weekly as $w) {
                $i++;
                if ($i == count($weekly)) {
                    $stringCalendarInfo .= ucwords($w);
                } else {
                    $stringCalendarInfo .= ucwords($w) . ', ';
                }
            }
            $stringCalendarInfo .= ' of week';
        }
        //yearly
        if ($gift_calendar == 'yearly') {
            $yearly = $rule->getYearly();
            $stringCalendarInfo = 'In ';
            $yearly = explode(',', $yearly);
            foreach ($yearly as $y) {
                $i++;
                if ($i == count($yearly)) {
                    $stringCalendarInfo .= ucwords($y);
                } else {
                    $stringCalendarInfo .= ucwords($y) . ', ';
                }
            }
        }
        //monthly
        if ($gift_calendar == 'monthly') {
            $monthly = $rule->getMonthly();
            $monthly = explode(',', $monthly);
            $stringCalendarInfo = 'In ';
            foreach ($monthly as $m) {
                $i++;
                if ($i == count($monthly)) {
                    if ($m == 1) {
                        $stringCalendarInfo .= $m . 'st week ';
                    } elseif ($m == 2) {
                        $stringCalendarInfo .= $m . 'nd week ';
                    } elseif ($m == 3) {
                        $stringCalendarInfo .= $m . 'rd week ';
                    } else {
                        $stringCalendarInfo .= $m . 'th week ';
                    }
                } else {
                    if ($m == 1) {
                        $stringCalendarInfo .= $m . 'st week, ';
                    } elseif ($m == 2) {
                        $stringCalendarInfo .= $m . 'nd week, ';
                    } elseif ($m == 3) {
                        $stringCalendarInfo .= $m . 'rd week, ';
                    } else {
                        $stringCalendarInfo .= $m . 'th week, ';
                    }
                }
            }
            $monthly .= 'of month';
        }
        return $stringCalendarInfo;
    }

	public function checkCatalogRule($quotes) {
        $catRuleGifts = array();
        $notGifts = array();
        foreach ($quotes as $quote) {
            if (!in_array($quote->getItemId(), $catRuleGifts)) {
                $catRuleGifts[] = array('quote_id' => $quote->getQuoteId(), 'item_id' => $quote->getItemId(), 'catalog_rule_id' => $quote->getCatalogRuleId());
            }
        }

        foreach (Mage::getModel('checkout/cart')->getQuote()->getAllItems() as $item) {
            $isGift = false;
            $itemId = $item->getId();
            $itemOptions = $item->getOptions();
            $productId = $item->getProductId();
            foreach ($itemOptions as $option) {
                $oData = $option->getData();
                if (!$item->getParentItemId()) {
                    if ($oData['code'] == 'option_promotionalgift_shoppingcartrule' || $oData['code'] == 'option_promotionalgift_catalogrule') {
                        $isGift = true;
                    }
                }
            }
            if (!$isGift) {
                $notGifts[$item->getId()] = $productId;
            }
        }
        if (count($notGifts) > 0) {
            foreach ($notGifts as $itemId => $productId) {
                $availableRule = Mage::getModel('promotionalgift/catalogrule')->validateItem($productId);
                $catRuleId = $availableRule->getRuleId();
                if ($availableRule) {
                    foreach ($catRuleGifts as $catRuleGift) {
                        if ($catRuleGift['catalog_rule_id'] != $catRuleId) {
                            Mage::getModel('promotionalgift/quote')->getCollection()
                                ->addFieldToFilter('item_id', $catRuleGift['item_id'])
                                ->addFieldToFilter('quote_id', $catRuleGift['quote_id'])
                                ->getFirstItem()
                                ->delete();
                            Mage::getModel('checkout/cart')->getQuote()->removeItem($catRuleGift['item_id'])->save();
                        }
                    }
                }
            }
        } else {
            foreach ($catRuleGifts as $gItem) {
                Mage::getModel('promotionalgift/quote')->getCollection()
                    ->addFieldToFilter('item_id', $gItem['item_id'])
                    ->addFieldToFilter('quote_id', $gItem['quote_id'])
                    ->getFirstItem()
                    ->delete();
                Mage::getModel('checkout/cart')->getQuote()->removeItem($gItem['item_id'])->save();
            }
        }
    }
}
