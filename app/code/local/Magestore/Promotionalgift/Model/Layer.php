<?php

class Magestore_Promotionalgift_Model_Layer extends Mage_Catalog_Model_Layer {

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
        $catalogId = Mage::app()->getRequest()->getParam('catalogrule');
        $productsCollection = Mage::getResourceModel('catalog/product_collection');
        $productsCollection->setPageSize(200);
        $productIds = array();
        $pages = $productsCollection->getLastPageNumber();
        $currentPage = 1;
        do {
            $productsCollection->setCurPage($currentPage);
            $productsCollection->load();
            foreach ($productsCollection as $product) {
                if ($catalogId) {
                    $availableRule = Mage::getModel('promotionalgift/catalogrule')->load($catalogId);
                    $availableRule->afterLoad();
                    if ($availableRule->validate($product)) {
                        $productIds[] = $product->getId();
                    }
                } else {
                    $availableRule = Mage::getModel('promotionalgift/catalogrule')->validateItem($product->getId());
                    if ($availableRule) {
                        $productIds[] = $product->getId();
                    }
                }
            }
            $currentPage++;
            //clear collection and free memory
            $productsCollection->clear();
        } while ($currentPage <= $pages);
        return $productIds;
    }

    public function _getProductCollection() {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => $this->getProductIds()));
        return $collection;
    }

}
