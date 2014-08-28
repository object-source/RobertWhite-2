<?php

class Magestore_Promotionalgift_Model_Catalogproductlayer extends Mage_Catalog_Model_Layer {

    public function getProductCollection() {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $this->getProductIds()));
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }

        // Zend_Debug::dump($collection->getSelect()->__toString());die();
        return $collection;
    }

    public function getProductIds() {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        // $installer = Mage::getModel('core/resource_setup');
        $catalogId = Mage::app()->getRequest()->getParam('catalogrule');
        $productIds = array();
        if ($catalogId) {
            $collection_query = '';
            $collection_query = 'SELECT `product_id` FROM '
                . $resource->getTableName("promotionalgift/catalogproduct") .
                ' WHERE `rule_id` = ' . $catalogId . ';';
            $catalogProductsCollection = $readConnection->fetchAll($collection_query);
            foreach ($catalogProductsCollection as $catalogProduct) {
                if (!in_array($catalogProduct['product_id'], $productIds)) {
                    $productIds[] = $catalogProduct['product_id'];
                }
            }
        } else {
            $availableRules = Mage::getModel('promotionalgift/catalogrule')->getAvailableRule();
            foreach ($availableRules as $availableRule) {
                $collection_query = '';
                $collection_query = 'SELECT `product_id` FROM '
                    . $resource->getTableName("promotionalgift/catalogproduct") .
                    ' WHERE `rule_id` = ' . $availableRule->getRuleId() . ';';
                $catalogProductsCollection = $readConnection->fetchAll($collection_query);
                foreach ($catalogProductsCollection as $catalogProduct) {
                    if (!in_array($catalogProduct['product_id'], $productIds)) {
                        $productIds[] = $catalogProduct['product_id'];
                    }
                }
            }
        }
        return $productIds;
    }

}