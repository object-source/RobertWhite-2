<?php

class Magestore_Promotionalgift_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        if (!Mage::helper('promotionalgift')->enablePromotionalgift())
            return $this;
        $this->_title($this->__('Promotional Gift'))->_title($this->__('Promotional Gift'));
        $this->loadLayout()
            ->renderLayout();
    }

    public function beforeSearchAction() {
        $data = $this->getRequest()->getPost();
        $url = Mage::getUrl('promotionalgift/index/search');
        if ($data['from_date']) {
            $url .= 'fromdate/' . $data['from_date'] . '/';
        }

        if ($data['to_date']) {
            $url .= 'todate/' . $data['to_date'] . '/';
        }
        $this->getResponse()->setRedirect($url);
    }

    public function searchAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        if (!Mage::helper('promotionalgift')->enablePromotionalgift())
            return $this;
        $this->_title($this->__('Promotional Campaigns'))->_title($this->__('Promotional Campaigns'));
        $this->loadLayout()
            ->renderLayout();
    }

    public function showCatalogRuleAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $catalogRuleId = $this->getRequest()->getParam('catalogrule');
        $rule = Mage::getModel('promotionalgift/catalogrule')->load($catalogRuleId);
        if (!Mage::helper('promotionalgift')->enablePromotionalgift())
            return $this;
        $this->_title($this->__($rule->getName()))->_title($this->__($rule->getName()));
        $this->loadLayout()
            ->renderLayout();
    }

    public function testAction() {
        var_dump(unserialize(Mage::getModel('checkout/session')->getData('product_parent_id')));
        //Mage::getModel('checkout/session')->setData('abc',null);
        // var_dump((Mage::getModel('checkout/session')->getData('abc')));
        // Mage::getModel('checkout/session')->setData('abc',null);
        // Mage::getModel('checkout/session')->setData('catalog_rule_quote',null);
    }

    public function addPromotionalGiftsCategoryRuleAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $session = Mage::getModel('checkout/session');
        $abc = $session->getData('def');
        if ($abc == 1) {
            $abc = $abc + 1;
            $session->setData('def', $abc);
        } else if ($abc > 1) {
            $this->_redirect('checkout/cart/index');
            $session->setData('def', null);
            return;
        }
        $items = $this->getRequest()->getParam('items');
        $ruleId = $this->getRequest()->getParam('categoryruleid');
        $rule = Mage::getModel('promotionalgift/catalogrule')->load($ruleId);
        $maxItems = 1;
        $ruleItem = Mage::getModel('promotionalgift/catalogitem')->load($ruleId, 'rule_id');
        $ruleItemIds = $ruleItem->getProductIds();
        $ruleItemIds = explode(',', $ruleItemIds);

        $requestInfo = $this->getRequest()->getPost();
        if ($items) {
            $items = explode(',', $items);

            //zend_debug::dump($items);die();
            $i = 0;
            $j = 0;
            foreach ($items as $item) {//zend_debug::dump($maxItems);die();
                if ($i < $maxItems) {
                    $item = explode('_', $item);
                    $productId = $item[0];
                    $qty = $item[1];
                    $requestInfo['qty'] = $qty;
                    ;
                    if (in_array($productId, $ruleItemIds)) {
                        $product = new Mage_Catalog_Model_Product();
                        $product->load($productId);
                        if (!in_array($product->getTypeId(), array('downloadable', 'virtual', 'bundle', 'configurable'))) {
                            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            $qtyStock = $stockItem->getQty();
                            if ($qtyStock <= 0 && $stockItem->getData('is_in_stock'))
                                continue;
                        }
                        /* item has options */
                        if ($product->getOptions()) {
                            // continue;
                            $year = date('Y');
                            $month = date('m');
                            $day = date('j');
                            $hour = date('g');
                            $minute = date('i');
                            $day_part = date('a');
                            $options = Mage::helper('core')->decorateArray($product->getOptions());
                            $optionAdds = array();
                            foreach ($options as $option) {
                                if ($option->getData('is_require') != '1')
                                    continue;
                                if (in_array($option->getType(), array('area', 'field')))
                                    $optionAdds[$option->getOptionId()] = Mage::helper('promotionalgift')->__('Promotional Gift');
                                if ($option->getType() == 'date_time')
                                    $optionAdds[$option->getOptionId()] = array(
                                        'month' => $month,
                                        'day' => $day,
                                        'year' => $year,
                                        'hour' => $hour,
                                        'minute' => $minute,
                                        'day_part' => $day_part
                                    );
                                if ($option->getType() == 'date')
                                    $optionAdds[$option->getOptionId()] = array(
                                        'month' => $month,
                                        'day' => $day,
                                        'year' => $year
                                    );
                                if ($option->getType() == 'time')
                                    $optionAdds[$option->getOptionId()] = array(
                                        'hour' => $hour,
                                        'minute' => $minute,
                                        'day_part' => $day_part
                                    );
                                if (in_array($option->getType(), array('drop_down', 'checkbox', 'multiple', 'radio'))) {
                                    foreach ($option->getValues() as $value) {
                                        $optionAdds[$option->getOptionId()] = $value->getData('option_type_id');
                                        break;
                                    }
                                }
                            }
                            $requestInfo['options'] = $optionAdds;
                        }

                        $session->setData('free_gift_item', $productId);
                        $session->setData('free_gift_item_qty', $qty);
                        $session->setData('catalog_rule_id', $ruleId);
                        $session->setData('catalog_rule_product_id', $ruleId);
                        // if($session->getData('promotionalgift_shoppingcart_rule_id')){
                        // $session->setData('shoppingcart_couponcode_rule_id',$ruleId);
                        // }

                        /* Add product special type */
                        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                            $requestInfo['qty'] = $qty;
                            $typeInstance = $product->getTypeInstance(true);
                            $typeInstance->setStoreFilter($product->getStoreId(), $product);

                            $optionCollection = $typeInstance->getOptionsCollection($product);

                            $selectionCollection = $typeInstance->getSelectionsCollection(
                                $typeInstance->getOptionsIds($product), $product
                            );
                            // $options = $optionCollection->appendSelections($selectionCollection, false, false);
                            // $bundleOptions = array();
                            // foreach ($options as $option){
                            // if(!$option->getSelections()) continue;								
                            // $option_id = $option->getData('option_id');
                            // $selections = $option->getData('selections');								
                            // if($option->getType()!='checkbox' && $option->getType()!='multi'){
                            // foreach($selections as $selection){
                            // $bundleOptions[$option_id] = $selection->getData('selection_id');
                            // break;
                            // }
                            // }else{
                            // foreach($selections as $selection){
                            // $bundleOptions[$option_id] = array($selection->getData('selection_id'));
                            // break;
                            // }
                            // }
                            // }
                            //$requestInfo['product'] = $product->getId();
                            Mage::getModel('checkout/session')->setData('product_bundle', $productId);
                            $requestInfo['related_product'] = '';
                            //$requestInfo['bundle_option'] = $bundleOptions;
                            $cart = Mage::getModel('checkout/cart');
                            $productBundle = new Mage_Catalog_Model_Product();
                            $productBundle->load($productId);
                            $cart->addProduct($productBundle, $requestInfo);
                            $result = $cart->save();
                            $j++;
                        } elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                            $productGiftId = $productId;
                            $requestInfo['qty'] = $qty;
                            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                            $allProducts = $product->getTypeInstance(true)
                                ->getUsedProducts(null, $product);
                            foreach ($allProducts as $productConfig) {
                                $productId = $productConfig->getId();
                                $allowAttributes = $product->getTypeInstance(true)
                                    ->getConfigurableAttributes($product);
                                foreach ($allowAttributes as $attribute) {
                                    $productAttribute = $attribute->getProductAttribute();
                                    $productAttributeId = $productAttribute->getId();
                                    $attributeValue = $productConfig->getData($productAttribute->getAttributeCode());
                                    if (!isset($options[$productAttributeId])) {
                                        $options[$productAttributeId] = array();
                                    }

                                    if (!isset($options[$productAttributeId][$attributeValue])) {
                                        $options[$productAttributeId][$attributeValue] = array();
                                    }
                                    $options[$productAttributeId][$attributeValue][] = $productId;
                                }
                            }
                            if ($options) {
                                foreach ($options as $optionId => $keys) {
                                    if (count($options) == '1') {
                                        foreach ($keys as $k1 => $key) {
                                            //$requestInfo['super_attribute'] = array($optionId=>$k1);
                                            break;
                                        }
                                        break;
                                    } else {
                                        $check = '';
                                        foreach ($keys as $k1 => $key) {
                                            $check = 1;
                                            break;
                                        }
                                        if ($check == '')
                                            continue;
                                        if (count($keys) < 1)
                                            continue;
                                        $id1 = $optionId;
                                        foreach ($keys as $k1 => $key) {
                                            foreach ($key as $k) {
                                                foreach ($options as $optionId2 => $key2s) {
                                                    if ($optionId2 == $id1)
                                                        continue;
                                                    foreach ($key2s as $k2 => $key2) {
                                                        foreach ($key2 as $_k2) {
                                                            if ($_k2 == $k) {
                                                                $key1 = $k1;
                                                                $key2 = $k2;
                                                                $id2 = $optionId2;
                                                                $next = 1;
                                                                break;
                                                            }
                                                            if ($next == '1')
                                                                break;
                                                        }
                                                        if ($next == '1')
                                                            break;
                                                    }
                                                    if ($next == '1')
                                                        break;
                                                }
                                                if ($next == '1')
                                                    break;
                                            }
                                            if ($next == '1')
                                                break;
                                        }
                                        //$requestInfo['super_attribute'] = array($id1=>$key1,$id2=>$key2);
                                        break;
                                    }
                                }
                                //$requestInfo['product'] = $product->getId();
                                $requestInfo['related_product'] = '';
                                $cart = Mage::getModel('checkout/cart');
                                $productConfig = new Mage_Catalog_Model_Product();
                                $productConfig->load($productGiftId);
                                $cart->addProduct($productConfig, $requestInfo);
                                $result = $cart->save();
                            }
                            $j++;
                        }else {
                            try {
                                $result = Mage::getModel('checkout/cart')->addProduct($product, $requestInfo)->save();
                                $j++;
                            } catch (Exception $e) {
                                Mage::getSingleton('checkout/session')->addError($this->__('This promotional gift cannot be added to cart.'));
                                $this->_redirect('checkout/cart/index');
                            }
                        }
                        if ($result != null && !$result->hasError()) {
                            $message = Mage::helper('promotionalgift')->__($product->getName() . ' was added to your shopping cart.');
                            Mage::getSingleton('checkout/session')->addSuccess($message);
                        }
                    }
                    $i++;
                } else {
                    break;
                }
            }
        }
        if ($j > 0) {
            if (Mage::getModel('checkout/session')->getData('promotionalgift_shoppingcart_rule_id') &&
                !Mage::getModel('checkout/session')->getData('promotionalgift_shoppingcart_rule_used')) {
                Mage::getModel('checkout/session')->setData('promotionalgift_shoppingcart_rule_used', true);
            }
        }
        $this->_redirect('checkout/cart/index');
    }

    public function addPromotionalGiftsAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $session = Mage::getModel('checkout/session');
        $abc = $session->getData('abc');
        if ($abc == 1) {
            $abc = $abc + 1;
            $session->setData('abc', $abc);
        } else if ($abc > 1) {
            $this->_redirect('checkout/cart/index');
            $session->setData('abc', null);
            return;
        }
        $codeRuleId = $session->getData('shoppingcart_rule_id');
        $quote = $session->getQuote();
        if (!$quote->getId()) {
            $this->_redirect('checkout/cart/index');
        } else {
            $shoppingRule = Mage::getModel('promotionalgift/shoppingcartrule')->validateQuote($quote);
            $shoppingCodeRule = Mage::getModel('promotionalgift/shoppingcartrule')->load($codeRuleId, 'coupon_code');
            if (!$shoppingRule && !$shoppingCodeRule) {
                $this->_redirect('checkout/cart/index');
            }
        }
        $productId = $this->getRequest()->getParam('product_id');
        $ruleId = $this->getRequest()->getParam('ruleId');
        $rule = Mage::getModel('promotionalgift/shoppingcartrule')->load($ruleId);
        $maxItems = $rule->getNumberItemFree();
        $ruleItem = Mage::getModel('promotionalgift/shoppingcartitem')->load($ruleId, 'rule_id');
        $qtys = $ruleItem->getGiftQty();
        $qtys = explode(',', $qtys);
        $ruleItemIds = $ruleItem->getProductIds();
        $ruleItemIds = explode(',', $ruleItemIds);
        $keys = array_keys($ruleItemIds, $productId);
        $key = $keys[0];
        $qty = $qtys[$key];
        $requestInfo = $this->getRequest()->getPost();
        $j = 0;
        if (in_array($productId, $ruleItemIds)) {
            $product = new Mage_Catalog_Model_Product();
            $product->load($productId);
            if (!in_array($product->getTypeId(), array('downloadable', 'virtual'))) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $qtyStock = $stockItem->getQty();
                if ($qtyStock <= 0 && $stockItem->getData('is_in_stock'))
                    ;
            }
            /* item has options */
            if ($product->getOptions()) {
                // continue;
                $year = date('Y');
                $month = date('m');
                $day = date('j');
                $hour = date('g');
                $minute = date('i');
                $day_part = date('a');
                $options = Mage::helper('core')->decorateArray($product->getOptions());
                $optionAdds = array();
                foreach ($options as $option) {
                    if ($option->getData('is_require') != '1')
                        continue;
                    if (in_array($option->getType(), array('area', 'field')))
                        $optionAdds[$option->getOptionId()] = Mage::helper('promotionalgift')->__('Promotional Gift');
                    if ($option->getType() == 'date_time')
                        $optionAdds[$option->getOptionId()] = array(
                            'month' => $month,
                            'day' => $day,
                            'year' => $year,
                            'hour' => $hour,
                            'minute' => $minute,
                            'day_part' => $day_part
                        );
                    if ($option->getType() == 'date')
                        $optionAdds[$option->getOptionId()] = array(
                            'month' => $month,
                            'day' => $day,
                            'year' => $year
                        );
                    if ($option->getType() == 'time')
                        $optionAdds[$option->getOptionId()] = array(
                            'hour' => $hour,
                            'minute' => $minute,
                            'day_part' => $day_part
                        );
                    if (in_array($option->getType(), array('drop_down', 'checkbox', 'multiple', 'radio'))) {
                        foreach ($option->getValues() as $value) {
                            $optionAdds[$option->getOptionId()] = $value->getData('option_type_id');
                            break;
                        }
                    }
                }
                $requestInfo['options'] = $optionAdds;
            }
            $session->setData('shoppingcart_gift_item', $productId);
            $session->setData('shoppingcart_gift_item_qty', $qty);
            $session->setData('shoppingcart_rule_id', $ruleId);
            if ($session->getData('promotionalgift_shoppingcart_rule_id')) {
                $session->setData('shoppingcart_couponcode_rule_id', $ruleId);
            }

            /* Add product special type */
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $requestInfo['qty'] = $qty;
                $typeInstance = $product->getTypeInstance(true);
                $typeInstance->setStoreFilter($product->getStoreId(), $product);

                $optionCollection = $typeInstance->getOptionsCollection($product);

                $selectionCollection = $typeInstance->getSelectionsCollection(
                    $typeInstance->getOptionsIds($product), $product
                );
                $options = $optionCollection->appendSelections($selectionCollection, false, false);
                $bundleOptions = array();
                foreach ($options as $option) {
                    if (!$option->getSelections())
                        continue;
                    $option_id = $option->getData('option_id');
                    $selections = $option->getData('selections');
                    if ($option->getType() != 'checkbox' && $option->getType() != 'multi') {
                        foreach ($selections as $selection) {
                            $bundleOptions[$option_id] = $selection->getData('selection_id');
                            break;
                        }
                    } else {
                        foreach ($selections as $selection) {
                            $bundleOptions[$option_id] = array($selection->getData('selection_id'));
                            break;
                        }
                    }
                }
                //$requestInfo['product'] = $product->getId();
                Mage::getModel('checkout/session')->setData('product_bundle', $productId);
                $requestInfo['related_product'] = '';
                //$requestInfo['bundle_option'] = $bundleOptions;
                $cart = Mage::getModel('checkout/cart');
                $productBundle = new Mage_Catalog_Model_Product();
                $productBundle->load($productId);
                $cart->addProduct($productBundle, $requestInfo);
                $result = $cart->save();
                $j++;
            } elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $productGiftId = $productId;
                $requestInfo['qty'] = $qty;
                $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                $allProducts = $product->getTypeInstance(true)
                    ->getUsedProducts(null, $product);
                foreach ($allProducts as $productConfig) {
                    $productId = $productConfig->getId();
                    $allowAttributes = $product->getTypeInstance(true)
                        ->getConfigurableAttributes($product);
                    foreach ($allowAttributes as $attribute) {
                        $productAttribute = $attribute->getProductAttribute();
                        $productAttributeId = $productAttribute->getId();
                        $attributeValue = $productConfig->getData($productAttribute->getAttributeCode());
                        if (!isset($options[$productAttributeId])) {
                            $options[$productAttributeId] = array();
                        }

                        if (!isset($options[$productAttributeId][$attributeValue])) {
                            $options[$productAttributeId][$attributeValue] = array();
                        }
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
                if ($options) {
                    foreach ($options as $optionId => $keys) {
                        if (count($options) == '1') {
                            foreach ($keys as $k1 => $key) {
                                //$requestInfo['super_attribute'] = array($optionId => $k1);
                                break;
                            }
                            break;
                        } else {
                            $check = '';
                            foreach ($keys as $k1 => $key) {
                                $check = 1;
                                break;
                            }
                            if ($check == '')
                                continue;
                            if (count($keys) < 1)
                                continue;
                            $id1 = $optionId;
                            foreach ($keys as $k1 => $key) {
                                foreach ($key as $k) {
                                    foreach ($options as $optionId2 => $key2s) {
                                        if ($optionId2 == $id1)
                                            continue;
                                        foreach ($key2s as $k2 => $key2) {
                                            foreach ($key2 as $_k2) {
                                                if ($_k2 == $k) {
                                                    $key1 = $k1;
                                                    $key2 = $k2;
                                                    $id2 = $optionId2;
                                                    $next = 1;
                                                    break;
                                                }
                                                if ($next == '1')
                                                    break;
                                            }
                                            if ($next == '1')
                                                break;
                                        }
                                        if ($next == '1')
                                            break;
                                    }
                                    if ($next == '1')
                                        break;
                                }
                                if ($next == '1')
                                    break;
                            }
                            //$requestInfo['super_attribute'] = array($id1 => $key1, $id2 => $key2);
                            break;
                        }
                    }
                    //$requestInfo['product'] = $product->getId();
                    $requestInfo['related_product'] = '';
                    $cart = Mage::getModel('checkout/cart');
                    $productConfig = new Mage_Catalog_Model_Product();
                    $productConfig->load($productGiftId);
                    $cart->addProduct($productConfig, $requestInfo);
                    $result = $cart->save();
                }
                $j++;
            }elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                $associatedProducts = $product->getTypeInstance(true)
                    ->getAssociatedProducts($product);
                $hasAssociatedProducts = count($associatedProducts);
                if ($product->isAvailable() && $hasAssociatedProducts) {
                    // $count = 0;
                    // foreach ($associatedProducts as $associatedProduct) {
                    // if ($count == 0)
                    // $requestInfo['super_group'][$associatedProduct->getId()] = (string) ($qty);
                    // else
                    // $requestInfo['super_group'][$associatedProduct->getId()] = 0;
                    // $count++;
                    // }
                    // Mage::getModel('checkout/session')->setData('promotionalgift_bundle', 1);
                    // Mage::getModel('checkout/session')->setData('free_gift_item', null);
                    $productGroup = array();
                    $productGroup[] = $product->getId();
                    $productGroup[] = $requestInfo['super_group'];
                    Mage::getModel('checkout/session')->setData('promotionalgift_shoppingcart_grouped', serialize($productGroup));
                    $requestInfo['product'] = $productId;
                    $requestInfo["related_product"] = "";
                    $cart = Mage::getModel('checkout/cart');
                    $productGroup = new Mage_Catalog_Model_Product();
                    $productGroup->load($productId);
                    $cart->addProduct($productGroup, $requestInfo);
                    $result = $cart->save();
                }
                $j++;
                /* End add product special type */
            } else {
                try {
                    $requestInfo['qty'] = $qty;
                    $cart = Mage::getModel('checkout/cart');
                    $cart->addProduct($product, $requestInfo);
                    $result = $cart->save();
                    $j++;
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($this->__('This promotional gift cannot be added to cart.'));
                    $this->_redirect('checkout/cart/index');
                }
            }
            if ($result != null && !$result->hasError()) {
                $message = Mage::helper('promotionalgift')->__($product->getName() . ' was added to your shopping cart.');
                Mage::getSingleton('checkout/session')->addSuccess($message);
            }
        }
        if ($j > 0) {
            if (Mage::getModel('checkout/session')->getData('promotionalgift_shoppingcart_rule_id') &&
                !Mage::getModel('checkout/session')->getData('promotionalgift_shoppingcart_rule_used')) {
                Mage::getModel('checkout/session')->setData('promotionalgift_shoppingcart_rule_used', true);
            }
        }
        $this->_redirect('checkout/cart/index');
    }

    public function addPromotionalGiftsCheckoutAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        $backUrl = Mage::getModel('checkout/session')->getData('back_url_promotionalgift');
        $session = Mage::getModel('checkout/session');
        $abc = $session->getData('abc');
        if ($abc == 1) {
            $abc = $abc + 1;
            $session->setData('abc', $abc);
        } else if ($abc > 1) {
            $this->_redirectUrl($backUrl);
            //zend_debug::dump($backUrl);die();
            $session->setData('abc', null);
            return;
        }
        $codeRuleId = $session->getData('shoppingcart_rule_id');
        $quote = $session->getQuote();
        if (!$quote->getId()) {
            $this->_redirect('checkout/cart/index');
        } else {
            $shoppingRule = Mage::getModel('promotionalgift/shoppingcartrule')->validateQuote($quote);
            $shoppingCodeRule = Mage::getModel('promotionalgift/shoppingcartrule')->load($codeRuleId, 'coupon_code');
            if (!$shoppingRule && !$shoppingCodeRule) {
                $this->_redirectUrl($backUrl);
                return;
            }
        }
        $items = $this->getRequest()->getParam('items');
        $ruleId = $this->getRequest()->getParam('ruleId');
        $rule = Mage::getModel('promotionalgift/shoppingcartrule')->load($ruleId);
        $maxItems = $rule->getNumberItemFree();
        $ruleItem = Mage::getModel('promotionalgift/shoppingcartitem')->load($ruleId, 'rule_id');
        $ruleItemIds = $ruleItem->getProductIds();
        $ruleItemIds = explode(',', $ruleItemIds);

        $requestInfo = $this->getRequest()->getPost();
        if ($items) {
            $items = explode(',', $items);
            $i = 0;
            $j = 0;
            foreach ($items as $item) {
                if ($i < $maxItems) {
                    $item = explode('_', $item);
                    $productId = $item[0];
                    $product = new Mage_Catalog_Model_Product();
                    $product->load($productId);
                    $qty = Mage::helper('promotionalgift')->getQtyProductRule($product, $rule);
                    $requestInfo['qty'] = $qty;
                    if (in_array($productId, $ruleItemIds)) {
                        if (!in_array($product->getTypeId(), array('downloadable', 'virtual'))) {
                            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            $qtyStock = $stockItem->getQty();
                            if ($qtyStock <= 0 && $stockItem->getData('is_in_stock'))
                                continue;
                        }
                        /* item has options */
                        if ($product->getOptions()) {
                            // continue;
                            $year = date('Y');
                            $month = date('m');
                            $day = date('j');
                            $hour = date('g');
                            $minute = date('i');
                            $day_part = date('a');
                            $options = Mage::helper('core')->decorateArray($product->getOptions());
                            $optionAdds = array();
                            foreach ($options as $option) {
                                if ($option->getData('is_require') != '1')
                                    continue;
                                if (in_array($option->getType(), array('area', 'field')))
                                    $optionAdds[$option->getOptionId()] = Mage::helper('promotionalgift')->__('Promotional Gift');
                                if ($option->getType() == 'date_time')
                                    $optionAdds[$option->getOptionId()] = array(
                                        'month' => $month,
                                        'day' => $day,
                                        'year' => $year,
                                        'hour' => $hour,
                                        'minute' => $minute,
                                        'day_part' => $day_part
                                    );
                                if ($option->getType() == 'date')
                                    $optionAdds[$option->getOptionId()] = array(
                                        'month' => $month,
                                        'day' => $day,
                                        'year' => $year
                                    );
                                if ($option->getType() == 'time')
                                    $optionAdds[$option->getOptionId()] = array(
                                        'hour' => $hour,
                                        'minute' => $minute,
                                        'day_part' => $day_part
                                    );
                                if (in_array($option->getType(), array('drop_down', 'checkbox', 'multiple', 'radio'))) {
                                    foreach ($option->getValues() as $value) {
                                        $optionAdds[$option->getOptionId()] = $value->getData('option_type_id');
                                        break;
                                    }
                                }
                            }
                            $requestInfo['options'] = $optionAdds;
                        }
                        $session->setData('shoppingcart_gift_item', $productId);
                        $session->setData('shoppingcart_gift_item_qty', $qty);
                        $session->setData('shoppingcart_rule_id', $ruleId);
                        if ($session->getData('promotionalgift_shoppingcart_rule_id')) {
                            $session->setData('shoppingcart_couponcode_rule_id', $ruleId);
                        }

                        /* Add product special type */

                        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                            $requestInfo['qty'] = $qty;
                            $typeInstance = $product->getTypeInstance(true);
                            $typeInstance->setStoreFilter($product->getStoreId(), $product);

                            $optionCollection = $typeInstance->getOptionsCollection($product);

                            $selectionCollection = $typeInstance->getSelectionsCollection(
                                $typeInstance->getOptionsIds($product), $product
                            );
                            $options = $optionCollection->appendSelections($selectionCollection, false, false);
                            $bundleOptions = array();
                            foreach ($options as $option) {
                                if (!$option->getSelections())
                                    continue;
                                $option_id = $option->getData('option_id');
                                $selections = $option->getData('selections');
                                if ($option->getType() != 'checkbox' && $option->getType() != 'multi') {
                                    foreach ($selections as $selection) {
                                        $bundleOptions[$option_id] = $selection->getData('selection_id');
                                        break;
                                    }
                                } else {
                                    foreach ($selections as $selection) {
                                        $bundleOptions[$option_id] = array($selection->getData('selection_id'));
                                        break;
                                    }
                                }
                            }
                            //$requestInfo['product'] = $product->getId();
                            Mage::getModel('checkout/session')->setData('product_bundle', $productId);
                            $requestInfo['related_product'] = '';
                            //$requestInfo['bundle_option'] = $bundleOptions;
                            $cart = Mage::getModel('checkout/cart');
                            $productBundle = new Mage_Catalog_Model_Product();
                            $productBundle->load($productId);
                            $cart->addProduct($productBundle, $requestInfo);
                            $result = $cart->save();
                            $j++;
                        } elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                            $productGiftId = $productId;
                            $requestInfo['qty'] = $qty;
                            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                            $allProducts = $product->getTypeInstance(true)
                                ->getUsedProducts(null, $product);
                            foreach ($allProducts as $productConfig) {
                                $productId = $productConfig->getId();
                                $allowAttributes = $product->getTypeInstance(true)
                                    ->getConfigurableAttributes($product);
                                foreach ($allowAttributes as $attribute) {
                                    $productAttribute = $attribute->getProductAttribute();
                                    $productAttributeId = $productAttribute->getId();
                                    $attributeValue = $productConfig->getData($productAttribute->getAttributeCode());
                                    if (!isset($options[$productAttributeId])) {
                                        $options[$productAttributeId] = array();
                                    }

                                    if (!isset($options[$productAttributeId][$attributeValue])) {
                                        $options[$productAttributeId][$attributeValue] = array();
                                    }
                                    $options[$productAttributeId][$attributeValue][] = $productId;
                                }
                            }
                            if ($options) {
                                foreach ($options as $optionId => $keys) {
                                    if (count($options) == '1') {
                                        foreach ($keys as $k1 => $key) {
                                            // $requestInfo['super_attribute'] = array($optionId => $k1);
                                            break;
                                        }
                                        break;
                                    } else {
                                        $check = '';
                                        foreach ($keys as $k1 => $key) {
                                            $check = 1;
                                            break;
                                        }
                                        if ($check == '')
                                            continue;
                                        if (count($keys) < 1)
                                            continue;
                                        $id1 = $optionId;
                                        foreach ($keys as $k1 => $key) {
                                            foreach ($key as $k) {
                                                foreach ($options as $optionId2 => $key2s) {
                                                    if ($optionId2 == $id1)
                                                        continue;
                                                    foreach ($key2s as $k2 => $key2) {
                                                        foreach ($key2 as $_k2) {
                                                            if ($_k2 == $k) {
                                                                $key1 = $k1;
                                                                $key2 = $k2;
                                                                $id2 = $optionId2;
                                                                $next = 1;
                                                                break;
                                                            }
                                                            if ($next == '1')
                                                                break;
                                                        }
                                                        if ($next == '1')
                                                            break;
                                                    }
                                                    if ($next == '1')
                                                        break;
                                                }
                                                if ($next == '1')
                                                    break;
                                            }
                                            if ($next == '1')
                                                break;
                                        }
                                        //$requestInfo['super_attribute'] = array($id1 => $key1, $id2 => $key2);
                                        break;
                                    }
                                }
                                //$requestInfo['product'] = $product->getId();
                                $requestInfo['related_product'] = '';
                                $cart = Mage::getModel('checkout/cart');
                                $productConfig = new Mage_Catalog_Model_Product();
                                $productConfig->load($productGiftId);
                                $cart->addProduct($productConfig, $requestInfo);
                                $result = $cart->save();
                            }
                            $j++;
                        }elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                            $associatedProducts = $product->getTypeInstance(true)
                                ->getAssociatedProducts($product);
                            $hasAssociatedProducts = count($associatedProducts);
                            if ($product->isAvailable() && $hasAssociatedProducts) {
                                // $count = 0;
                                // foreach ($associatedProducts as $associatedProduct) {
                                // if ($count == 0)
                                // $requestInfo['super_group'][$associatedProduct->getId()] = (string) ($qty);
                                // else
                                // $requestInfo['super_group'][$associatedProduct->getId()] = 0;
                                // $count++;
                                // }
                                // Mage::getModel('checkout/session')->setData('promotionalgift_bundle', 1);
                                // Mage::getModel('checkout/session')->setData('free_gift_item', null);
                                $productGroup = array();
                                $productGroup[] = $product->getId();
                                $productGroup[] = $requestInfo['super_group'];
                                Mage::getModel('checkout/session')->setData('promotionalgift_shoppingcart_grouped', serialize($productGroup));
                                $requestInfo['product'] = $productId;
                                $requestInfo["related_product"] = "";
                                $cart = Mage::getModel('checkout/cart');
                                $productGroup = new Mage_Catalog_Model_Product();
                                $productGroup->load($productId);
                                $cart->addProduct($productGroup, $requestInfo);
                                $result = $cart->save();
                            }
                            $j++;
                            /* End add product special type */
                        } else {
                            try {
                                $result = Mage::getModel('checkout/cart')->addProduct($product, $requestInfo)->save();
                                $j++;
                            } catch (Exception $e) {
                                Mage::getSingleton('checkout/session')->addError($this->__('This promotional gift cannot be added to cart.'));
                                $this->_redirectUrl($backUrl);
                                return;
                            }
                        }
                        if ($result != null && !$result->hasError()) {
                            $message = Mage::helper('promotionalgift')->__($product->getName() . ' was added to your shopping cart.');
                            Mage::getSingleton('core/session')->addSuccess($message);
                        }
                    }
                    $i++;
                } else {
                    break;
                }
            }
        }
        if ($j > 0) {
            if (Mage::getModel('checkout/session')->getData('promotionalgift_shoppingcart_rule_id') &&
                !Mage::getModel('checkout/session')->getData('promotionalgift_shoppingcart_rule_used')) {
                Mage::getModel('checkout/session')->setData('promotionalgift_shoppingcart_rule_used', true);
            }
        }
        $this->_redirectUrl($backUrl);
        return;
    }

    public function getdataforcartAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        if (!Mage::helper('promotionalgift')->enablePromotionalgift())
            return $this;
        $result = array();
        $block = Mage::getBlockSingleton('promotionalgift/shoppingcart');
        $itemEditIds = $block->getEidtItemIds();
        $itemEditOptionIds = $block->getEidtItemOptionIds();
        $itemIds = $block->getItemIds();
        $result['itemEditIds'] = $itemEditIds;
        $result['itemEditOptionIds'] = $itemEditOptionIds;
        $result['itemIds'] = $itemIds;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updatepromotionalposAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
            return;
        }
        if (!Mage::helper('promotionalgift')->enablePromotionalgift())
            return $this;
        $this->loadLayout(false);
        $this->renderLayout();
    }

}
