<?php
/******************************************************
 * @package Venustheme Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://venustheme.com
 * @copyright	Copyright (C) December 2010 venustheme.com <@emai:venustheme@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class Ves_Tempcp_Model_Product extends Mage_Catalog_Block_Product_Abstract{

	protected $_config = '';
	const DEFAULT_STORE_ID = 0;
	public $_current_page = 1;
	public function getConfig( $key, $val=0) 
    {
		return (isset($this->_config[$key])?$this->_config[$key]:$val);
    }
    public function setCurPage($page = 1) {
    	$this->_current_page = (int)$page;
    }

	public function getListSpecialProducts( $config = array() ){
		$this->_config = $config;
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');
		if($cateids && $cateids != "1") {
			$productIds = $this->getProductByCategory();
			$products = Mage::getModel('catalog/product')->getCollection();
			$products->addAttributeToSelect('*')
			                   ->addFieldToFilter('visibility', array(
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
			                   )) //showing just products visible in catalog or both search and catalog
			                   ->addMinimalPrice()
							   ->addUrlRewrite()
							   ->addTaxPercents()
							   ->addStoreFilter($storeId)
							   ->addIdFilter($productIds)
			                   ->addFinalPrice()
			                   ->getSelect()
			                   ->where('price_index.final_price < price_index.price')
			                   ;
    	} else {
		    $products = Mage::getModel('catalog/product')->getCollection();
			$products->addAttributeToSelect('*')
			                   ->addFieldToFilter('visibility', array(
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
			                   )) //showing just products visible in catalog or both search and catalog
			                   ->addMinimalPrice()
							   ->addUrlRewrite()
							   ->addTaxPercents()
							   ->addStoreFilter($storeId)
			                   ->addFinalPrice()
			                   ->getSelect()
			                   ->where('price_index.final_price < price_index.price')
			                   ;
    	}

    	Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage( $this->_current_page );
        $this->setProductCollection($products);

		$this->_addProductAttributesAndPrices($products);
        $list = array();                  
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
			$list = $products;
		}
		
		return $list;
	}
    
    public function getListLatestProducts(  $config = array())
    {
    	$fieldorder = 'created_at';
    	$order = 'desc';
    	$this->_config = $config;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid');
    	if($cateids && $cateids != "1") {
    	    $productIds = $this->getProductByCategory();
			$products = Mage::getResourceModel('catalog/product_collection')
		    ->addAttributeToSelect('*')
		    ->addMinimalPrice()
		    ->addUrlRewrite()
		    ->addTaxPercents()
		    ->addStoreFilter()
		    ->addIdFilter($productIds)
		    ->setOrder ($fieldorder,$order);
    	} else {
	    $products = Mage::getResourceModel('catalog/product_collection')
		    ->addAttributeToSelect('*')
		    ->addMinimalPrice()
		    ->addFinalPrice()
		    ->addStoreFilter()
		    ->addUrlRewrite()
		    ->addTaxPercents()
		    ->setOrder ($fieldorder,$order);
    	}		
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage($this->_current_page);
        $this->setProductCollection($products);
		
		$this->_addProductAttributesAndPrices($products);
        $list = array();                  
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
			$list = $products;
		}
		
		return $list;
    }

    public function getListRelatedProducts(  $config = array())
    {
    	$list = array();
    	$product_id = Mage::registry('current_product')->getId();
    	$fieldorder = 'created_at';
    	$order = Varien_Db_Select::SQL_DESC;
    	if($product_id) {

    		$product = Mage::registry('current_product');
	        /* @var $product Mage_Catalog_Model_Product */

	        $_itemCollection = $product->getRelatedProductCollection()
	            ->addAttributeToSelect('required_options')
	            ->setPositionOrder()
	            ->addStoreFilter()
	        ;

	        if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
	            Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($_itemCollection,
	                Mage::getSingleton('checkout/session')->getQuoteId()
	            );
	            $this->_addProductAttributesAndPrices($_itemCollection);
	        }
	//        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
	        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_itemCollection);

	        $_itemCollection->load();

	        foreach ($_itemCollection as $product) {
	            $product->setDoNotUseCategoryId(true);
	        }

    		$list = $_itemCollection;
    	}
		
		return $list;
    }
    public function getListUpsellProducts(  $config = array())
    {
    	$list = array();
    	$fieldorder = 'created_at';
    	$order = Varien_Db_Select::SQL_DESC;
		// Get product object.
		$product_id = Mage::registry('current_product')->getId();
		if($product_id) {
			$object = Mage::getModel('catalog/product');
	   
		    //Get product detail using product id  (Suppose you have product id is : $product_id)
		    $_product = $object->load($product_id);
	  
	   		// Fetch list of upsell product using query.
	   		$upsell_product = $_product->getUpSellProductCollection()->addAttributeToSort('position', Varien_Db_Select::SQL_ASC)->addStoreFilter();

		   //check if record is empty or not
		   $count = count($upsell_product);
		   if(!empty($count)) {
		   		//if result is not empty then get  upsell product detail using foreach loop
		   		$productIds = array();
		      	foreach($upsell_product as $_upsell){
		      		$productIds[] = $_upsell->getId();
		      	}
		         
		        if($productIds) {
					$products = Mage::getResourceModel('catalog/product_collection')
				    ->addAttributeToSelect('*')
				    ->addMinimalPrice()
				    ->addUrlRewrite()
				    ->addTaxPercents()
				    ->addStoreFilter()
				    ->addIdFilter($productIds)
				    ->setOrder ($fieldorder,$order);

				    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
			        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
			        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage($this->_current_page);
			        $this->setProductCollection($products);
					
					$this->_addProductAttributesAndPrices($products);
			        $list = array();                  
					if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
						$list = $products;
					}
				}
		   }
	   
		}
	  

		return $list;
    }
    
    public function getListBestSellerProducts(  $config = array())
    {
    	$this->_config = $config;
    	$fieldorder = 'ordered_qty';
    	$order = 'desc';
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid');
    	if($cateids && $cateids != "1") {
    	    $productIds = $this->getProductByCategory();

    	    $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

	        $attributesToSelect = array('name', 'small_image','short_description','price');

	        $productFlatTable = Mage::getResourceModel('catalog/product_flat')->getFlatTableName($storeId);
	        try{
	        	$products = Mage::getResourceModel('reports/product_collection')
	            						->addOrderedQty();
	            if(Mage::helper('catalog/product_flat')->isEnabled()){
	            	
	                $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
	                $products->setVisibility($visibility);

	            }else{
	                $products->addAttributeToSelect($attributesToSelect);
	            }
	 	
	            $products
		            /** Add store filter! */
		            ->addMinimalPrice()
				    ->addUrlRewrite()
				    ->addTaxPercents()
				    ->setStoreId($storeId)
				    ->addStoreFilter($storeId)
				    ->addIdFilter($productIds)
				    ->setOrder ($fieldorder,$order);
	        }catch (Exception $e){
	            Mage::logException($e);
	        }

    	} else {

    		$visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

	        $attributesToSelect = array('name', 'small_image','short_description','price');

	        $productFlatTable = Mage::getResourceModel('catalog/product_flat')->getFlatTableName($storeId);
	        try{
	            /**
	             * init resource singleton collection
	             */
	            $products = Mage::getResourceModel('reports/product_collection')
	            						->addOrderedQty();
	
	            if(Mage::helper('catalog/product_flat')->isEnabled()){
	            	
	                $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
	                $products->setVisibility($visibility);

	            }else{
	                $products->addAttributeToSelect($attributesToSelect);
	            }
	 		
	            $products
		            /** Add store filter! */
		            ->addMinimalPrice()
				    ->addUrlRewrite()
				    ->addTaxPercents()
				    ->setStoreId($storeId)
				    ->addStoreFilter($storeId)
				    ->setOrder ($fieldorder,$order);
	        }catch (Exception $e){
	            Mage::logException($e);
	        }
    	}
    	$list = array();

    	if(!Mage::helper('catalog/product_flat')->isEnabled()){

	        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
	        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
	        
	        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage(1);

	        $this->setProductCollection($products);

			$this->_addProductAttributesAndPrices($products);

			if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
				$list = $products;
			}

		} elseif($products->getSize()) {
			$products->setPageSize($this->getConfig('limit_item',6))->setCurPage(1);

			$list = $products;
		}

		return $list;
    }
    
    public function getListMostViewedProducts(  $config = array())
    {
    	$this->_config = $config;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid');
    	if($cateids && $cateids != "1") {
		    $productIds = $this->getProductByCategory();

		    $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

	        $attributesToSelect = array('name', 'small_image','short_description','price');

	        $productFlatTable = Mage::getResourceModel('catalog/product_flat')->getFlatTableName($storeId);
	        try{
	        	$products = Mage::getResourceModel('reports/product_collection')
	            						->addViewsCount();
	            if(Mage::helper('catalog/product_flat')->isEnabled()){
	                $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
	                $products->setVisibility($visibility);

	            }else{
	                $products->addAttributeToSelect($attributesToSelect);
	            }
	 
	            $products
		            /** Add store filter! */
		            ->addMinimalPrice()
				    ->addUrlRewrite()
				    ->addTaxPercents()
				    ->setStoreId($storeId)
				    ->addStoreFilter($storeId)
				    ->addIdFilter($productIds);
	        }catch (Exception $e){
	            Mage::logException($e);
	        }


    	} else {

    		$visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

	        $attributesToSelect = array('name', 'small_image','short_description','price');

	        $productFlatTable = Mage::getResourceModel('catalog/product_flat')->getFlatTableName($storeId);
	        try{
	        	$products = Mage::getResourceModel('reports/product_collection')
	            						->addViewsCount();

	            if(Mage::helper('catalog/product_flat')->isEnabled()){
	            	
	                $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
	                $products->setVisibility($visibility);

	            }else{
	                $products->addAttributeToSelect($attributesToSelect);
	            }
	 
	            $products
		            /** Add store filter! */
		            ->addMinimalPrice()
				    ->addUrlRewrite()
				    ->addTaxPercents()
				    ->setStoreId($storeId)
				    ->addStoreFilter($storeId);
	        }catch (Exception $e){
	            Mage::logException($e);
	        }
    	}
    	
    	$list = array();


    	if(!Mage::helper('catalog/product_flat')->isEnabled()){

	        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
	        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
	        
	        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage(1);

	        $this->setProductCollection($products);
			$this->_addProductAttributesAndPrices($products);
	                      
			if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
				$list = $products;
			}
		} elseif($products->getSize()) {
			$products->setPageSize($this->getConfig('limit_item',6))->setCurPage(1);
			$list = $products;
		}
		
		return $list;
    }
    
    public function getListFeaturedProducts(  $config = array())
    { 
    	$this->_config = $config;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid');
    	if($cateids && $cateids != "1") {
	    $productIds = $this->getProductByCategory();
	    $products = Mage::getResourceModel('catalog/product_collection')
		    ->addAttributeToSelect('*')
		    ->addMinimalPrice()
		    ->addUrlRewrite()
		    ->addTaxPercents()
		    ->addStoreFilter()
		    ->addIdFilter($productIds)
		    ->addAttributeToFilter("featured", 1);
	    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
	    Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($products);		
    	} else {
	    $products = Mage::getResourceModel('catalog/product_collection')
		    ->addAttributeToSelect('*')
		    ->addMinimalPrice()
		    ->addUrlRewrite()
		    ->addTaxPercents()
		    ->addStoreFilter()
		    ->addAttributeToFilter("featured", 1);
	    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
	    Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($products);		
    	}
    	
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage(1);
        $this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);
        $list = array();
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
			       
			$list = $products;
		}
		
		return $list;
    }
    
    public function getListNewProducts(  $config = array())
    {
    	$this->_config = $config;
    	$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid');
    	if($cateids && $cateids != "1") {
	    $productIds = $this->getProductByCategory();
		    $products = Mage::getResourceModel('catalog/product_collection')
			    ->addAttributeToSelect('*')
			    ->addMinimalPrice()
			    ->addUrlRewrite()
			    ->addTaxPercents()
			    ->addStoreFilter()
			    ->addIdFilter($productIds)
			    ->addAttributeToFilter('news_from_date', array('date'=>true, 'to'=> $todayDate))
			    ->addAttributeToFilter(array(array('attribute'=>'news_to_date', 'date'=>true, 'from'=>$todayDate), array('attribute'=>'news_to_date', 'is' => new Zend_Db_Expr('null'))),'','left')
			    ->addAttributeToSort('news_from_date','desc');
    	} else {
	    $products = Mage::getResourceModel('catalog/product_collection')
		    ->addAttributeToSelect('*')
		    ->addMinimalPrice()
		    ->addUrlRewrite()
		    ->addTaxPercents()
		    ->addStoreFilter()
		    ->addAttributeToFilter('news_from_date', array('date'=>true, 'to'=> $todayDate))
		    ->addAttributeToFilter(array(array('attribute'=>'news_to_date', 'date'=>true, 'from'=>$todayDate), array('attribute'=>'news_to_date', 'is' => new Zend_Db_Expr('null'))),'','left')
		    ->addAttributeToSort('news_from_date','desc');
    	}
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $products->setPageSize($this->getConfig('limit_item',6))->setCurPage(1);
        $this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);
        $list = array();                  
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
			$list = $products;
		}
		
		return $list;
    }
    
    function inArray($source, $target) {
		for($i = 0; $i < sizeof ( $source ); $i ++) {
			if (in_array ( $source [$i], $target )) {
			return true;
			}
		}
    }
	
    function getProductByCategory(){
        $return = array(); 
        $pids = array();
        $catsid=$this->getConfig('catsid');
        $products = Mage::getResourceModel ( 'catalog/product_collection' );
         
        foreach ($products->getItems() as $key => $_product){
            $arr_categoryids[$key] = $_product->getCategoryIds();
            
            if($catsid && $catsid !="1"){    
                if(stristr($catsid, ',') === FALSE) {
                    $arr_catsid[$key] =  array(0 => $catsid);
                }else{
                    $arr_catsid[$key] = explode(",", $catsid);
                }
                
                $return[$key] = $this->inArray($arr_catsid[$key], $arr_categoryids[$key]);
            }
        }
        
        foreach ($return as $k => $v){ 
            if($v==1) $pids[] = $k;
        }    
        
        return $pids;   
    }
}


?>