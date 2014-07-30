<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Lof
 * @package     Lof_Slider
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Banner base block
 *
 * @category    Lof
 * @package     Lof_Slider
 * @author    LandOfCoder (landofcoder@gmail.com)
 */

class Ves_Tabs_Block_List extends Mage_Catalog_Block_Product_Abstract 
{
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_config = '';
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_listDesc = array();
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_show = 0;
	protected $_theme = "";
    protected $_categories = array();


    public function __construct($attributes = array()) {
        $helper =  Mage::helper('ves_tabs/data');
		$this->_config = $helper->get($attributes);
		$this->_show = $this->getConfig("show");
		if(!$this->_show) return;
		/*End init meida files*/
		$mediaHelper =  Mage::helper('ves_tabs/media');
		$mediaHelper->addMediaFile("skin_css", "ves_tabs/default/style.css" );

		Mage::dispatchEvent('custom_event', array('object'=>$this));  
		parent::__construct();
    }
	/**
     * Rendering block content
     *
     * @return string
     */
	function _toHtml() 
	{
		$config = $this->_config;
		if( !$this->_config["show"] ){	return ;	}
		$my_template = $this->getTemplate();
		if(empty($my_template)) {
			$theme = (isset($config['theme'])) ? $config['theme']: "default";
       	 	$this->setTemplate('ves/tabs/' . $theme . '/list.phtml');
		}
        
		/*Verify*/
		$list = $this->getListProducts ();
		
		$data = array();
		$i = 0;
		foreach($list as $key =>$list_item){
			$items = $list_item["products"];
			$data[ $i ]["mainImage"] = "";
			$data[ $i ]["link"] = $list_item['category_url'];
			$data[ $i ]["title"] = $list_item["category_name"];
			$data[ $i ]["products"] = $items;
			/*
			$block = $this->getLayout()->createBlock("ves_tabs/products","ves.tabs.products".$key, $this->_config)
										->setTheme( $this->_config['theme'] )
										->setListProducts($products);*/
			//$data[ $i ]["description"] = $block->toHtml();
			$data[ $i ]["category_id"] = $key;
			$i++;
		}
		$this->assign( 'items', $data );
		// render html
		$cms = "";

 		$cms_block_id = $this->getConfig('cmsblock');
 		if($cms_block_id){
 			$cms = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($cms_block_id)->toHtml();
 		}

 		$this->assign( 'cms', $cms );
		$this->assign('config', $this->_config);	
		$this->assign("theme", $theme);	
		return parent::_toHtml();
	}

	 public function getCategoryImage($category_id = 0, $width = 50, $height = 50)
    {
	    $cur_category = Mage::getModel('catalog/category')->load($category_id);
	    $_file_name = $cur_category->getThumbnail();
	    $_file_name = $_file_name?$_file_name: $cur_category->getImage();
	    $_media_dir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
	    $cache_dir = $_media_dir . 'cache' . DS;

	    if($_file_name) {
		    if (file_exists($cache_dir . $_file_name)) {
		        return Mage::getBaseUrl('media') .'/catalog/category/cache/' . $_file_name;
		    } elseif (file_exists($_media_dir . $_file_name)) {
		        if (!is_dir($cache_dir)) {
		            mkdir($cache_dir);
		        }

		        $_image = new Varien_Image($_media_dir . $_file_name);
		        $_image->constrainOnly(true);
		        $_image->keepAspectRatio(true);
		        $_image->keepTransparency(true);
		        $_image->resize((int)$width, (int)$height);
		        $_image->save($cache_dir . $_file_name);

		        return Mage::getBaseUrl('media') . '/catalog/category/cache/'. $_file_name;
		    }
		}
	    return "";
    } 
	
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $val=0) 
    {
		return (isset($this->_config[$key])?$this->_config[$key]:$val);
    }

    /**
	 * overrde the value of the extension's configuration
	 *
	 * @return string
	 */
	function setConfig( $key, $value ){
		$this->_config[$key] = $value;
		return $this;
	}
	
	function getListProducts() {
		$listall = null;
		
		switch ( $this->getConfig('sourceProductsMode') ) {
			case 'latest' :
				$listall = $this->getListLatestProducts ();
				break;
			case 'best_buy' :
				$listall = $this->getListBestSellerProducts ();
				break;
			case 'most_viewed' :
				$listall = $this->getListMostViewedProducts ();
				break;
			case 'attribute' :
				$listall = $this->getListFeaturedProducts ();
				break;
			case 'special' :
				$listall = $this->getListSpecialProducts ();
				break;
		}
		
		return $listall;
	}
	public function getListFeaturedProducts()
    {
    	if ($perPage === NULL)
			$perPage = ( int ) $this->getConfig( 'limit_item' );
    	 /*
			Show all the product list in the current store			
		*/
		$storeId = Mage::app ()->getStore ()->getStoreId ();
		$data = array();
		$this->setStoreId ( $storeId );
		if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
         }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
        if( !empty($arr_catsid)){

        	foreach($arr_catsid as $catid){
	        	  // get array product_id
	          	  $arr_productids = $this->getProductByCategory( $catid );
	        	  $products = Mage::getResourceModel('catalog/product_collection')
						    ->addAttributeToSelect('*')
						    ->addMinimalPrice()
						    ->addUrlRewrite()
						    ->addTaxPercents()
						    ->addStoreFilter()
						    ->addIdFilter($arr_productids)
						    ->addAttributeToFilter("featured", 1);
				  	Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
	    			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($products);	
			        $products->setPageSize($perPage)->setCurPage(1);
			        $this->setProductCollection($products);
					$this->_addProductAttributesAndPrices($products);
			        $list = array();                  
					if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
						$list = $products;
					}
	        	  	$category = $this->_categories[ $catid ];
	        	  	$data[$catid]['products'] = $list;
	        	  	$data[$catid]['category_name'] = $category->getName();
	        	  	$data[$catid]['category_url'] = $category->getUrl();
	        	}
        }
		return $data;
    }
	public function getListMostViewedProducts()
    {
    	if ($perPage === NULL)
			$perPage = ( int ) $this->getConfig( 'limit_item' );
    	 /*
			Show all the product list in the current store			
		*/
		$storeId = Mage::app ()->getStore ()->getStoreId ();
		$data = array();
		$this->setStoreId ( $storeId );
		if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
         }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
        if( !empty($arr_catsid)){

        	foreach($arr_catsid as $catid){
	        	  // get array product_id
	          	  $arr_productids = $this->getProductByCategory( $catid );
	        	  $products = Mage::getResourceModel('reports/product_collection')
							->addAttributeToSelect('*')
							->addMinimalPrice()
							->addUrlRewrite()
							->addTaxPercents()
							->addAttributeToSelect(array('name', 'price', 'small_image')) //edit to suit tastes            
							->setStoreId($storeId)
							->addStoreFilter($storeId)
							->addViewsCount()
							->addIdFilter($arr_productids);
				  	Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
			        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
			        $products->setPageSize($perPage)->setCurPage(1);
			        $this->setProductCollection($products);
					$this->_addProductAttributesAndPrices($products);
			        $list = array();   
					if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
						$list = $products;
					}
	        	  	$category = $this->_categories[ $catid ];
	        	  	$data[$catid]['products'] = $list;
	        	  	$data[$catid]['category_name'] = $category->getName();
	        	  	$data[$catid]['category_url'] = $category->getUrl();
	        	}
        }
		return $data;
    }
	public function getListBestSellerProducts($fieldorder = 'ordered_qty', $order = 'desc')
    {
    	if ($perPage === NULL)
			$perPage = ( int ) $this->getConfig( 'limit_item' );
    	 /*
			Show all the product list in the current store			
		*/
		$storeId = Mage::app ()->getStore ()->getStoreId ();
		$data = array();
		$this->setStoreId ( $storeId );
		if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
         }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
        if( !empty($arr_catsid)){

        	foreach($arr_catsid as $catid){
	        	  // get array product_id
	          	  $arr_productids = $this->getProductByCategory( $catid );
	        	  $products = Mage::getResourceModel('reports/product_collection')
							->addOrderedQty()
							->addAttributeToSelect('*')
							->addMinimalPrice()
							->addUrlRewrite()
							->addTaxPercents()
							->addAttributeToSelect(array('name', 'price', 'small_image')) //edit to suit tastes
							->addIdFilter($arr_productids)// id product
							->setStoreId($storeId)
							->addStoreFilter($storeId)
							->setOrder ($fieldorder,$order);

				  	Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
			        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
			        $products->setPageSize($perPage)->setCurPage(1);
			        $this->setProductCollection($products);
					
					$this->_addProductAttributesAndPrices($products);
			        $list = array();                  
					if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
						$list = $products;
					}
	        	  	$category = $this->_categories[ $catid ];
	        	  	$data[$catid]['products'] = $list;
	        	  	$data[$catid]['category_name'] = $category->getName();
	        	  	$data[$catid]['category_url'] = $category->getUrl();
	        	}
        }
		return $data;

    }
	public function getListSpecialProducts(){
		if ($perPage === NULL)
			$perPage = ( int ) $this->getConfig( 'limit_item' );
    	 /*
			Show all the product list in the current store			
		*/

		$storeId = Mage::app ()->getStore ()->getStoreId ();
		$data = array();
		$this->setStoreId ( $storeId );
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
         }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
        if( !empty($arr_catsid)){

        	foreach($arr_catsid as $catid){
	        	  // get array product_id
	          	  $arr_productids = $this->getProductByCategory( $catid );

	        	  $special = Mage::getModel('catalog/product')->getCollection();
				  $special->addAttributeToSelect('*')
	                   ->addFieldToFilter('visibility', array(
	                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
	                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
	                   )) //showing just products visible in catalog or both search and catalog
	                   ->addMinimalPrice()
					   ->addUrlRewrite()
					   ->addTaxPercents()
					   ->addStoreFilter($storeId)
					   ->addIdFilter($arr_productids)
	                   ->addFinalPrice()
	                   ->getSelect()
	                   ->where('price_index.final_price < price_index.price')
	                   ;

					Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($special);
			        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($special);
			        $special->setPageSize($perPage)->setCurPage(1);
			        $this->setProductCollection($special);

					$this->_addProductAttributesAndPrices($special);
			        $list = array();                  
					if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
						$list = $special;
					}

	        	  	$category = $this->_categories[ $catid ];
	        	  	$data[$catid]['products'] = $list;
	        	  	$data[$catid]['category_name'] = $category->getName();
	        	  	$data[$catid]['category_url'] = $category->getUrl();
	        	}
        }
		return $data;
	}

	public function getListLatestProducts($fieldorder = 'updated_at', $order = 'desc')
    {
    	if ($perPage === NULL)
			$perPage = ( int ) $this->getConfig( 'limit_item' );
    	 /*
			Show all the product list in the current store			
		*/
		$storeId = Mage::app ()->getStore ()->getStoreId ();
		$data = array();
		$this->setStoreId ( $storeId );
		if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
         }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
        if( !empty($arr_catsid)){

        	foreach($arr_catsid as $catid){
	        	  // get array product_id
	          	  $arr_productids = $this->getProductByCategory( $catid );
	        	  $products = Mage::getResourceModel('catalog/product_collection')
										    ->addAttributeToSelect('*')
										    ->addMinimalPrice()
										    ->addFinalPrice()
										    ->addStoreFilter()
										    ->addUrlRewrite()
										    ->addTaxPercents()
										    ->addIdFilter($arr_productids)
										    ->setOrder ($fieldorder,$order);
				 Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
		        $products->setPageSize($perPage)->setCurPage(1);
		        $this->setProductCollection($products);
				
				$this->_addProductAttributesAndPrices($products);
		        $list = array();              
				if (($_products = $this->getProductCollection()) && $_products->getSize ()) {            
					$list = $_products;
				}
	        	  $category = $this->_categories[ $catid ];
	        	  $data[$catid]['products'] = $list;
	        	  $data[$catid]['category_name'] = $category->getName();
	        	  $data[$catid]['category_url'] = $category->getUrl();
	        	}
        }
		return $data;
    }
	
	function inArray($source, $target) {
		for($j = 0; $j < sizeof ( $source ); $j ++) {
			if (in_array ( $source [$j], $target )) {
				return true;
			}
		}
	}
	function getCategories(){
		$categories = array();
		if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
         }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
		if(!empty($arr_catsid)){
			foreach ($arr_catsid as $categoryId){
				 $category = Mage::getModel('catalog/category')->load($categoryId);
				 if($category){
				 	$categories[$categoryId] = $category->getName();
				 }
			}
		}
		return $categories;
	}
    function getProductByCategory($catid = null){
        $return = array(); 
        $pids = array();
  		
        if(!empty($catid)){
        	$category = Mage::getModel('catalog/category')->load($catid);
        	$products = Mage::getResourceModel ( 'catalog/product_collection' )->addCategoryFilter($category);
        	$this->_categories[$catid] = $category;
	        foreach ($products as $key => $_product){
	            $arr_categoryids[$key] = $_product->getCategoryIds();
	            $return[$key] = 1;
	        }
	        foreach ($return as $k => $v){ 
	            if($v==1) $pids[] = $k;
	        }    
        }
        return $pids;   
    }

    public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
    		$text = ($is_striped==true)?strip_tags($text):$text;
    		if(strlen($text) <= $length){
    			return $text;
    		}
    		$text = substr($text,0,$length);
    		$pos_space = strrpos($text,' ');
    		return substr($text,0,$pos_space).$replacer;
	}
}
