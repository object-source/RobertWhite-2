<?php 
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 2.0
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) October 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
/**
 * ThemeControlHelper Class 
 * 
 */
define( "VES_CSS_CACHE", Mage::getSingleton('core/design_package')->getSkinBaseDir()."/cache/" );
define( "VES_SUB_PATH", '/ves-asset/' );

class Ves_Tempcp_Helper_Framework extends Mage_Core_Helper_Abstract {
	
		/**
		 * @var Array $positions
		 * 
		 * @access private
		 */
		private $positions = array();
		
		/**
		 * @var Array $modulesList
		 * 
		 * @access private
		 */
		private $modulesList = array();
		
		/**
		 * @var Array $cparams
		 * 
		 * @access private
		 */
		public $cparams = array();
		
		/**
		 * @var Integer $layout_id
		 * 
		 * @access private
		 */
		private $layout_id = 0;
		
		/**
		 * @var String $theme
		 * 
		 * @access private
		 */
		private $theme = '';
		
		private $skin = '';
		/**
		 * @var String $pageClass
		 * 
		 * @access private
		 */
		private $pageClass = '';

		/**
		 * @var Array $_jsFiles
		 * 
		 * @access private
		 */
		private $_jsFiles = array();
		
		/**
		 * @var Array $positions
		 * 
		 * @access private
		 */
		private $_cssFiles = array();
		
		/**
		 * @var Array $positions
		 * 
		 * @access private
		 */
		private $_themeDir = '';

		private $_themeDefaultDir = '';

		/**
		 * @var String $_the_themeSassDirmeLessDir
		 * 
		 * @access private
		 */
		private $_themeSassDir = '';

		private $_themeDefaultSassDir = '';

		private $_spans = array();
 
		/**
		 * @var String $_themeURL
		 * 
		 * @access private
		 */
		private $_themeURL = '';

		public $themeDefaultURL = "";

		/**
		 * @var String $_lessDevURL
		 * 
		 * @access private
		 */
		private $_tmp_css = array();

		/**
		 * @var String $_lessDevDir
		 * 
		 * @access private
		 */
		private $_lessDevDir = '';

		private $_cache_uri = '';

		/**
		 * @var String $_direction language direction;
		 * 
		 * @access private
		 */
		private  $_direction = 'ltr';

		private $_data = array();

		private $_theme_object = null;

		private $_internal_modules = array();

		public function getFramework( $theme_object = null){
			$framework = false;

			if(!empty($theme_object) && $framework =  Mage::registry('framework_data')){
				$framework->setThemeObject( $theme_object );
				Mage::unregister('framework_data');
				Mage::register('framework_data', $framework);
				
				return $framework;

			}elseif($framework =  Mage::registry('framework_data')){
				return $framework;
			}
			return false;
		}


		/**
		 * Constructor 
		 */
		public function initFramework( $theme,  $config, $registry = true){

				/* list of venus framework positions */
				$this->positions = array( 'mainmenu',
										  'slideshow',
										  'promotion',
										  'showcase',
										  'top',
										  'leftColumn',
										  'rightColumn',
										  'content_bottom',
										  'mass_bottom',
										  'footer',
										  'footer_top',
										  'footer_center',
										  'footer_bottom'
											
				);

				$this->_spans = array( 'full' 			=> array("3", "6", "3" ), /* for layout has left - center - right */
									'center-right'  => array("0", "9", "3" ), /* for layout has center - right */
									'center-left'   => array("3", "9", "0" ), /* for layout has left -center */
									'center'		=> array("0", "12", "0" ) /* for layout only have center */
				);
				

				$this->setData('config', $config);

				$positions = $config->get("positions");

				$this->positions = array_merge( $this->positions, $positions);

				$direction = $config->get("direction", "ltr"); /**/

				$tmp_direction = isset($_GET['dr'])?$_GET['dr']:"";
				
				if($tmp_direction) {
					$direction = $tmp_direction;
				}

				$package = $this->getPackageName();

				$themeName =  Mage::getDesign()->getTheme('frontend');

				$theme_dir = $config->get("theme_path","");

				$tmp_theme = explode("/", $theme);
				if(count($tmp_theme) == 1) {
					$theme = "default/".$theme;
				}

				$theme_dir = !empty($theme_dir)?$theme_dir: Mage::getBaseDir('skin') . '/frontend/'.$theme;

				$this->_themeDefaultDir =  Mage::getBaseDir('skin') . '/frontend/'.$package."/default" ; 

				$this->setTheme( $theme );
			 
			 	$this->setThemeDir( $theme_dir ); 

				$this->addParam( 'skin', $config->get("skin") );

				$this->addParam('layout', $config->get("layout","fullwidth") );

				$this->addParam( 'body_pattern', $config->get('body_pattern') );

				$this->themeURL =  $config->get("theme_url","");

				$this->themeURL = !empty($this->themeURL)?$this->themeURL: Mage::getBaseUrl(). 'skin/frontend/'.$this->get("theme");

				$this->themeDefaultURL = Mage::getBaseUrl(). 'skin/frontend/'.$package."/default" ; 

				$this->themeCssURL = $this->themeURL.'/css/';



				$this->setDirection( $direction );
				$params = array('layout', 'body_pattern', 'skin', 'header_layout', 'direction', 'footer_layout', 'default_list_layout', 'default_view_layout') ;
				if( $config->get('enable_paneltool',0) ){
					$vesreset = Mage::getSingleton('core/app')->getRequest()->getParam('vesreset');
					if( $vesreset && $config->get('enable_development_mode') ){
						$files = glob( Mage::getBaseDir('cache').'/'.$theme.'/*.css' );  
						if ($files) {
							foreach ($files as $file) {
								if (file_exists($file)) {
									unlink($file);
								}
							}
						}
					}

					$this->triggerUserParams(  $params );
				}

				/** ENABLE DEVELOPMENT MODE s**/
				$this->skin = $this->getParam('skin');

				$this->autoLoadThemeCss();

				$this->loadLocalThemeCss();
				$this->_internal_modules = $this->loadInternalModules();
				if($registry){
					 Mage::unregister('framework_data');
					 Mage::register('framework_data', $this);
				}
			return $this;
		}
		
		public function getSPAN($layout_page = ""){
			$layout = "";
			switch ($layout_page) {
				case 'col3':
					$layout = 'full';
					break;
				case 'col2-left':
					$layout = 'center-left';
					break;
				case 'col2-right':
					$layout = 'center-right';
					break;
				default:
					$layout = 'center';	
					break;
			}
			
			return isset($this->_spans[$layout])?$this->_spans[$layout]:"";
		}

		public function setCacheUri($cache = ""){
			$this->_cache_uri = $cache;
		}
		public function setThemeObject( $object = null){
			$this->_theme_object = $object;
		}

		public function getTmpCss(){
			return $this->_tmp_css;
		}
		/*
		 * set direction language (rtl or ltr)
		 */
		public function setDirection( $direction ){
			$this->_direction = $direction;
		}

		public function getDirection(){
			return $this->getParam("direction", $this->_direction);
		}
		/**
		 * set base path and less path of current theme. 
		 */
		public function setThemeDir( $dir ){
			$this->_themeDir = $dir; 
			$this->_themeSassDir = $dir.DS.'sass'.DS;
			$this->_themeDefaultSassDir = $this->getThemeDefaultDir().DS.'sass'.DS;
		}
		public function getThemeDefaultDir() {
			return $this->_themeDefaultDir;
		}
		public function getThemeDir(){
			return $this->_themeDir;
		}

		public function getThemeURL(){
			return $this->themeURL;
		}
		public function getThemeDefaultURL(){
			return $this->themeDefaultURL;
		}
		public function getThemeDefaultSassDir() {
			return $this->_themeDefaultSassDir;
		}
		public function getThemeSassDir(){
			return $this->_themeSassDir;
		}
		/**
		 * set name of actived theme.
		 */
		public function setTheme( $theme){
			$this->theme = $theme;
			return $this;
		}
		
		public function getPackageName() {
			return Mage::getSingleton('core/design_package')->getPackageName();
		}
		/**
		 *  add script files to collection.
		 */
		public function addScript( $path ){
			$this->_jsFiles[$path] = $path;
		}

		public function hasData($key = ""){
			return isset($this->_data[$key])?true:false;
		}

		public function setData($key, $value =""){
			$this->_data[$key] = $value;
		}

		public function getJs($path = "", $folder = "jquery"){
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'venustheme/ves_tempcp/'.$folder.'/'.$path;
		}

		public function getSkinJs($path = "") {
			$package = $this->getPackageName();

			$default_skin_dir = Mage::getBaseDir("skin")."/frontend/".$package."/default/";
			$base_skin_dir = Mage::getBaseDir("skin")."/frontend/base/default/";
			
			$default_skin_url = Mage::getBaseUrl('skin')."frontend/".$package."/default/";
			$base_skin_url = Mage::getBaseUrl('skin')."frontend/base/default/";

			if(file_exists($default_skin_dir.$path)) {
				return array("dir" => $default_skin_dir.$path,
							 "url" => $default_skin_url.$path);
			} elseif(file_exists($base_skin_dir.$path)) {
				return array("dir" => $base_skin_dir.$path,
							 "url" => $base_skin_url.$path);
			}

			return false;
		}
		
		public function compressJsCss( $assets = array()) {

			$enable_compress_css = $this->getConfig()->get("enable_compress_css");
			$enable_compress_js = $this->getConfig()->get("enable_compress_js");

			$tmp_css = array();
			$tmp_js = array();
			$tmp_assets = array();
			$js_excludes = $excludes = array();

			if($enable_compress_js) {
				$exclude_js_files = $this->getConfig()->get("exclude_js_files");
				$exclude_js_files = trim($exclude_js_files);
				$js_excludes = explode( ",", $exclude_js_files );
				if(!empty($js_excludes)) {
					foreach($js_excludes as $k=>$item) {
						$js_excludes[$k] = trim($item);
					}
				}
			}
			
			if($enable_compress_css) {
				$exclude_css_files = $this->getConfig()->get("exclude_css_files");
				$exclude_css_files = trim($exclude_css_files);
				$excludes = explode( ",", $exclude_css_files );
				if(!empty($excludes)) {
					foreach($excludes as $k=>$item) {
						$excludes[$k] = trim($item);
					}
				}
			}

			foreach ($assets as $key => $item) {
			  if (!isset($item['name'])) {
			      continue;
			   }
			  $if     = !empty($item['if']) ? $item['if'] : '';
			  $params = !empty($item['params']) ? $item['params'] : '';

			  switch ($item['type']) {
			      case 'js':        // js/*.js
			      	if($enable_compress_js) {
			      		if(!in_array("js/".$item['name'], $js_excludes) ) {
			      			$tmp_js[] = array("dir" => Mage::getBaseDir()."/js/".$item['name'],
			      							"url" => Mage::getBaseUrl('js').$item['name']) ;
				      	} else {
				      		$tmp_assets[] = $item;
				      	}
			      	} else {
			      		$tmp_assets[] = $item;
			      	}
			      	
			      	break;
			      case 'skin_js':   // skin/*/*.js
				      if($enable_compress_js) {
					      	if(!in_array("skin_js/".$item['name'], $js_excludes)) {
					      		if(file_exists($this->getThemeDir()."/".$item['name'])) {
						      		$tmp_js[] = array("dir" => $this->getThemeDir()."/".$item['name'],
						      							"url" => $this->getThemeURL()."/".$item['name']) ;
						      	} elseif($skin_js = $this->getSkinJs($item['name'])) {
						      		$tmp_js[] = array("dir" => $skin_js['dir'],
						      							"url" => $skin_js['url']) ;
						      	}
					      	} else {
					      		$tmp_assets[] = $item;
					      	}
				      } else {
				      		$tmp_assets[] = $item;
				      }
			        break;
			      case 'skin_css':
			      	if($enable_compress_css) {
				      	if(!in_array("skin_css/".$item['name'], $excludes) && (!$item['params'] || $item['params'] == 'media="all"') && file_exists($this->getThemeDir()."/".$item['name']) ) {
				      		$tmp_css[] = array("dir" => $this->getThemeDir()."/".$item['name'],
				      							"url" => $this->getThemeURL()."/".$item['name']) ;
				      	} else {
				      		$tmp_assets[] = $item;
				      	}
		      		} else {
		      			$tmp_assets[] = $item;
		      		}
		      		break;
		      	  case 'js_css':
			      	if($enable_compress_css) {
			      	  	if(!in_array("js_css/".$item['name'], $excludes) && (!$item['params'] || $item['params'] == 'media="all"')) {
			      			$tmp_css[] = array("dir" => Mage::getBaseDir()."/js/".$item['name'],
				      							"url" => Mage::getBaseUrl('js').$item['name']) ;
			      		} else {
			      			$tmp_assets[] = $item;
			      		}
			      	} else {
			      		$tmp_assets[] = $item;
			      	}
		      		break;
			      default:
			        break;
			  }
			}

			/*Compress Js*/
			if(!empty($tmp_js) && trim($enable_compress_js)) {
				$pcache = new VesTempcp_Cache();
				$pcache->setExtension( 'js' );

				if($enable_compress_js == 'compress-merge') {
					$all = '';
					$aKey = md5(serialize($tmp_js).serialize($js_excludes).Mage::getBaseUrl());
					
					if( !$pcache->isExisted( $aKey ) ){
						foreach( $tmp_js as $key => $file ){
							
							if(is_array($file)){
							 	$content = $pcache->read( $file['dir'] );
							 	if(!empty($content)) {
							 		$content = VesTempcp_JSMin::minify($content);
									$all .= ";".$content;
							 	}
										
							}
								
						}
						$pcache->set( $aKey, $all );
					}

					$tmp_assets["skin_js/cache/".md5($key).".js"] = array("type" => "skin_js",
							 														"name" => "cache/".$aKey.".js",
							 														"params" => '',
							 														"if" => "",
							 														"cond" => "");

				} else {
					
					foreach( $tmp_js as $key => $file ){
						if(is_array($file)){
							$check_existed = false;
							if( !$pcache->isExisted( md5($key) ) ){
							 	$content = $pcache->read( $file['dir'] );
							 	if( !empty($content)  ){
									$content = VesTempcp_JSMin::minify($content);
									$pcache->set( md5($key), $content );
									$check_existed = true;
								}
							} else 
								$check_existed = true;

							if($check_existed) {
								$tmp_assets["skin_js/cache/".md5($key).".css"] = array("type" => "skin_js",
							 														"name" => "cache/".md5($key).".js",
							 														"params" => '',
							 														"if" => "",
							 														"cond" => "");
							}
							
						}
					}
				}

			}
			/*Compress css*/
			
			if(!empty($tmp_css) && trim($enable_compress_css)) {
				$pcache = new VesTempcp_Cache();
				$pcache->setExtension( 'css' );

				if($enable_compress_css == 'compress-merge') {
					$all = '';
					$aKey = md5(serialize($tmp_css).serialize($excludes).Mage::getBaseUrl());
					
					if( !$pcache->isExisted( $aKey ) ){
						foreach( $tmp_css as $key => $file ){
							
							if(is_array($file)){
							 	$content = $pcache->read( $file['dir'] );
							 	if(!empty($content)) {
							 		$content  = VesTempcp_Csscompressor::process( $content, $file['url'] ); 
									$all .= $content;
							 	}
										
							}
								
						}
						$pcache->set( $aKey, $all );
					}

					$tmp_assets["skin_css/cache/".md5($key).".css"] = array("type" => "skin_css",
							 														"name" => "cache/".$aKey.".css",
							 														"params" => 'media="all"',
							 														"if" => "",
							 														"cond" => "");

				} else {
					
					foreach( $tmp_css as $key => $file ){
						if(is_array($file)){
							$check_existed = false;
							if( !$pcache->isExisted( md5($key) ) ){
							 	$content = $pcache->read( $file['dir'] );
							 	if( !empty($content)  ){
									$content  = VesTempcp_Csscompressor::process( $content, $file['url'] ); 
									$pcache->set( md5($key), $content );
									$check_existed = true;
								}
							} else 
								$check_existed = true;

							if($check_existed) {
								$tmp_assets["skin_css/cache/".md5($key).".css"] = array("type" => "skin_css",
							 														"name" => "cache/".md5($key).".css",
							 														"params" => 'media="all"',
							 														"if" => "",
							 														"cond" => "");
							}
							
						}
					}
				}

				

			}

			if(!empty($tmp_assets)) {
				return $tmp_assets;
			}

			return $assets;
		}
		/*
		Set framework scripts to list asset of magento 
		*/
		public function setFrameworkScripts($assets = array()){
			$tmp_assets = array();
			foreach ($assets as $key => $item) {
			  if (!isset($item['name'])) {
			      continue;
			   }
			  $if     = !empty($item['if']) ? $item['if'] : '';
			  $params = !empty($item['params']) ? $item['params'] : '';

			  switch ($item['type']) {
			      case 'js':        // js/*.js
			      case 'skin_js':   // skin/*/*.js
			      	  $nametmp = strtolower($item['name']);
			      	  $tmp_assets[$key] = $item;
			      	  if($nametmp == "prototype/prototype.js"){

			      	  	$tmp_assets["js/venustheme/ves_tempcp/jquery/jquery.min.js"] = array("type"=> "js",
			            "name" => "venustheme/ves_tempcp/jquery/jquery.min.js",
			            "params" => "", 
			            "if" => "",
			            "cond" => "");

			            $tmp_assets["js/venustheme/ves_tempcp/jquery/conflict.js"] = array("type"=> "js",
			            "name" => "venustheme/ves_tempcp/jquery/conflict.js",
			            "params" => "", 
			            "if" => "",
			            "cond" => "");

			            $tmp_assets["js/venustheme/ves_tempcp/jquery/ui/jquery-ui-1.8.16.custom.min.js"] = array("type"=> "js",
			            "name" => "venustheme/ves_tempcp/jquery/ui/jquery-ui-1.8.16.custom.min.js",
			            "params" => "", 
			            "if" => "",
			            "cond" => "");

			            $tmp_assets["js/venustheme/ves_tempcp/jquery/ui/external/jquery.cookie.js"] = array("type"=> "js",
			            "name" => "venustheme/ves_tempcp/jquery/ui/external/jquery.cookie.js",
			            "params" => "", 
			            "if" => "",
			            "cond" => "");

			            $tmp_assets["js/venustheme/ves_tempcp/jquery/bootstrap/bootstrap.min.js"] = array("type"=> "js",
			            "name" => "venustheme/ves_tempcp/jquery/bootstrap/bootstrap.min.js",
			            "params" => "", 
			            "if" => "",
			            "cond" => "");

			      	  }elseif(strpos($nametmp, "jquery.js") || strpos($nametmp, "jquery.min.js") || strpos($nametmp, "jquery-1.7.1.min.js") || strpos($nametmp, "jquery-1.8.0.min.js")){
			      	  	unset($tmp_assets[$key]);
			      	  }
			          break;
			      case 'skin_css':
			      		$nametmp = strtolower($item['name']);
			      		$tmp_assets[$key] = $item;
			      		$this->addCss($item['type'], $item['name'], $item['params'], $item['if'], $item['cond']);
			      	  	//if($nametmp == "css/styles.css"){
			      	  	unset($tmp_assets[$key]);
			      	  	//}
			      		break;
			      default:
			      		$tmp_assets[$key] = $item;
			          break;
			  }
			}

			return $tmp_assets;

		}
		public function getData($key, $default = ""){
			return isset($this->_data[$key])?$this->_data[$key]: $default;
		}

		public function getConfig(){
			return $this->getData("config");
		}
		public function getLang(){
	        if (!$this->hasData('lang')) {
	             $this->setData('lang', substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));
	        }
	         return $this->getData('lang');
		}
		/**
		 * add list of script files
		 */
        public  function addScriptList( $scripts ){
			if( is_array($scripts) && !empty($scripts) ){
				$this->_jsFiles = array_merge( $this->_jsFiles, $scripts ); 
			}
        }
		
		/**
		 *  get list of theme script files and opencart script files.
		 */
		public function getScriptFiles(){
			return $this->_jsFiles;
		}

		/*
		 *  add single css file to collection
		 
		public function addCss( $path ){
			$this->_cssFiles[md5($path)] =  array( 'href' => $path, 'rel' => 'stylesheet', 'media' => 'screen' );
		}
		*/

		/**
		 *  add single css file to collection
		 */
		public function addCss( $type = 'skin_css', $name = "", $params = "", $if = "", $cond = "" ){
			$this->_cssFiles[$name] =  array(
											 'type'=> $type,
											 'name'=> $name,
											 'params'=> $params,
											 'if' => $if,
											 'cond' => $cond);
		}
		public function getCssLinks(){ 
			return $this->_cssFiles;
		}
		public function getThemeCssURL(){
			return $this->themeCssURL;
		}
		/**
		 * get all less files in development folder matching to load css files.
		 */
		private function autoLoadThemeCss(){

			/* load global and defaul stylesheets file */
			$files = glob( $this->_themeSassDir . '*.scss');
			$loaded_style = false;
			if( $this->getDirection() == 'rtl' && file_exists($this->_themeSassDir.'rtl'.DS.'bootstrap.scss')) {
				$this->addCss( 'skin_css', 'css/rtl/bootstrap.css', 'media="all"' );
				$loaded_style = true;
			} else if(file_exists($this->_themeSassDir . 'bootstrap.scss')) {
				$this->addCss( 'skin_css', 'css/bootstrap.css', 'media="all"' );
				$loaded_style = true;
			}

			if(!$loaded_style) {
				if( $this->getDirection() == 'rtl' && file_exists($this->_themeDefaultSassDir.'rtl'.DS.'bootstrap.scss')) {
					$this->addCss( 'skin_css', 'css/rtl/bootstrap.css', 'media="all"' );
					$loaded_style = true;
				} else if(file_exists($this->_themeDefaultSassDir . 'bootstrap.scss')) {
					$this->addCss( 'skin_css', 'css/bootstrap.css', 'media="all"' );
					$loaded_style = true;
				}
				$loaded_style = false;
			}
			/* add stylesheets for actived skin files */
			if( $this->skin && file_exists($this->_themeSassDir.'skins/'.$this->skin.'/styles.scss')){
				$this->addCss( 'skin_css', 'css/skins/'.$this->skin.'/styles.css', 'media="all"' );
				$loaded_style = true;
			}elseif($this->skin == 'default' || empty($this->skin)){
				$this->addCss( 'skin_css', 'css/styles.css', 'media="all"' );
				$loaded_style = true;
			}

			if(!$loaded_style) {
				if( $this->skin && file_exists($this->_themeDefaultSassDir.'skins/'.$this->skin.'/styles.scss')){
					$this->addCss( 'skin_css', 'css/skins/'.$this->skin.'/styles.css', 'media="all"' );
					$loaded_style = true;
				}elseif($this->skin == 'default' || empty($this->skin)){
					$this->addCss( 'skin_css', 'css/styles.css', 'media="all"' );
					$loaded_style = true;
				}
				$loaded_style = false;
			}

			$default_files = glob( $this->_themeDefaultSassDir . '*.scss');

			if ($default_files) {

				foreach ($default_files as $file) {
					$file = trim($file);
					if( !preg_match("#bootstrap\.#", $file) ) {
						if( $this->skin && preg_match("#styles\.#", $file)) { 
							continue;
						} elseif ( substr($file, 0, 1) != "_" && file_exists($this->_themeDir.DS.'css'.DS.str_replace(".scss", ".css", basename($file)))) {
								$this->addCss( 'skin_css', 'css/'.str_replace(".scss", ".css", basename($file)) );
						} elseif ( substr($file, 0, 1) != "_" && file_exists($this->_themeDefaultDir.DS.'css'.DS.str_replace(".scss", ".css", basename($file)))) {
								$this->addCss( 'skin_css', 'css/'.str_replace(".scss", ".css", basename($file)) );
						}
					}
				}
			}

			if ($files) {

				foreach ($files as $file) {
					$file = trim($file);
					if( !preg_match("#bootstrap\.#", $file) ) {
						if( $this->skin && preg_match("#styles\.#", $file)) { 
							continue;
						} elseif ( substr($file, 0, 1) != "_" && file_exists($this->_themeDir.DS.'css'.DS.str_replace(".scss", ".css", basename($file)))) {
								$this->addCss( 'skin_css', 'css/'.str_replace(".scss", ".css", basename($file)) );
						}
					}
				}
			}

			/* if current language is rtl */
		 	if( $this->getDirection() == 'rtl' && file_exists($this->_themeSassDir .'rtl'.DS .'styles.scss')){
		 		$this->addCss('skin_css', 'css/rtl/styles.css');
		 	} elseif($this->getDirection() == 'rtl' && file_exists($this->_themeDefaultSassDir .'rtl'.DS .'styles.scss')){
		 		$this->addCss('skin_css', 'css/rtl/styles.css');
		 	}
		 	

		}

		/**
		 * Local Custom Css;
		 */
		public function loadLocalThemeCss(){
			$files = glob( $this->_themeDir . '/css/local/*.css' );
			foreach( $files as $file ){
				if( filesize($file) ){
					$this->addCss('skin_css', 'css/local/'.basename($file));
				}
			}
		}

		/**
		 * trigger to process user paramters using for demostration
		 */
		public function triggerUserParams(  $params ){
			$cookie = Mage::getSingleton('core/cookie');
			$vesreset = Mage::getSingleton('core/app')->getRequest()->getParam('vesreset');
			$exp = time() + 60*60*24*355;
			$userparams = "";
			if( !empty($vesreset) ){
				foreach( $params as $param ){
					$kc = $this->theme."_".$param;
					$this->addParam($param,null);
					$cookie->set($kc, null , $exp,'/');
					if( isset($_COOKIE[$kc]) ){
						$this->cparams[$kc] = null;
						$_COOKIE[$kc] = null;
					}
				}
				
			} else {
				$userparams = Mage::getSingleton('core/app')->getRequest()->getParam('userparams');

				foreach( $params as $param ){
					$kc = $this->theme."_".$param;
					if(!empty($userparams) && $data = $userparams){
						if( isset($data[$param]) ){
							$cookie->set($kc, $data[$param] , $exp,'/');
							$this->cparams[$kc] = $data[$param];
						}
					}
					if( isset($_COOKIE[$kc]) ){ 
						$this->cparams[$kc] = $_COOKIE[$kc];
					}
				}

			}

			if( !empty($userparams) || !empty($vesreset) ){  
				Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl());
			}
		}
		
	 	/**
	 	 * get user parameter
	 	 */
		public function getParam( $param , $value= '' ){
			return isset($this->cparams[$this->theme."_".$param])?$this->cparams[$this->theme."_".$param]:$value;
		}
		
		/**
		 * add custom parameter 
		 */
		public function addParam( $key, $value ){
			$this->cparams[$this->theme."_".$key] = $value;
		}
		
		/**
		 * get current page class.
		 */
		public function getPageClass(){
			return $this->pageClass ;
		}

		public function checkModulueLayout( $module_layouts = array() ){

			if(empty($module_layouts ) || in_array("all", $module_layouts)){

				return true;
			}
			if($this->isHomePage() && in_array("home", $module_layouts)){

				return true;
			}

			$page =  Mage::app()->getFrontController()->getRequest()->getRouteName();
			switch ($page) {
				case 'catalog':
					$controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
					$page = $page."-".$controller;
					break;
				
				default:
					break;
			}
			if(in_array($page, $module_layouts))
				return true;

			return false;
		}

		public function isHomePage(){
			$ishome=false;
			$page = Mage::app()->getFrontController()->getRequest()->getRouteName();
			 
			if ($page == 'cms'){
				$storeId    = Mage::app()->getStore()->getId();
				if($storeId) {
					$homepage_identifier = Mage::getStoreConfig("web/default/cms_home_page", $storeId);
				} else {
					$homepage_identifier = Mage::getStoreConfig("web/default/cms_home_page");
				}
			    $ishome = (Mage::getSingleton('cms/page')->getIdentifier() == $homepage_identifier) ? true :false;
			}
			return $ishome;
		}

		public function loadInternalModules(){

			if(empty($this->_internal_modules)){
					$internal_modules = $this->getConfig()->get("internal_modules", array());
					$_module_collection = Mage::getModel('ves_tempcp/module')->getModulesByTheme($this->getConfig()->get("theme_id", 0), 1);
					$modules = array();
					$list_exists_modules = array();

					if(is_object($_module_collection) && $_module_collection->getSize() > 0) {
						foreach($_module_collection as $module ) {
							$position = $module->getPosition();
							$position = trim($position);
							if(!isset($internal_modules[$position]) && !isset($modules[$position])) {
								$modules[$position] = array();
								$modules[$position][] = $module;

							} elseif(isset($modules[$position])) {
								$modules[$position][] = $module;
							} else {
								if(!isset($list_exists_modules[$position])) {
									$list_exists_modules[$position] = array();
								}
								$list_exists_modules[$position][] = $module;
							}
						}
					}
					if(!empty($internal_modules)){
						if($internal_modules){
							foreach($internal_modules as $key=>$val){
								$this->_internal_modules[ $key ] = array();
								if(!empty($val) && is_array($val)){
									foreach($val as $k=>$module){
										
										if(!empty($module)){
											$status = $this->getConfig()->get("widget_".$k."_status");
											if($status == 1) {
												$static_block_id = $this->getConfig()->get("widget_".$k."_block_id");
												$this->_internal_modules[ $key ][$k]['order'] = $this->getConfig()->get("widget_".$k."_order", 0);
												$this->_internal_modules[ $key ][$k]['title'] = $this->getConfig()->get("widget_".$k."_name");
												$this->_internal_modules[ $key ][$k]['layout'] = $this->getConfig()->get("widget_".$k."_layout");
												if(!empty($static_block_id)){
													$this->_internal_modules[ $key ][$k]['content'] = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($static_block_id)->toHtml();
												}else{
													$this->_internal_modules[ $key ][$k]['content'] = $this->getConfig()->get("widget_".$k."_data","", true);
												}
											}
										}
									}
								}
								if(isset($list_exists_modules[ $key ]) && $list_exists_modules[ $key ]) {
									foreach($list_exists_modules[ $key ] as $module) {
										if($module){
											$static_block_id = $module->getBlockId();
											$module_name = $module->getModuleName();
											$module_data = $module->getModuleData();
											$tmp_module = array();
											$tmp_module['order'] = $module->getSortOrder();
											$tmp_module['title'] = $module->getModuleTitle();
											$tmp_module['layout'] = $module->getLayout();
											$tmp_module['layout'] = explode(",", $tmp_module['layout']);
											if(!empty($static_block_id)){
												$tmp_module['content'] = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($static_block_id)->toHtml();
											}else{
												$tmp_module['content'] = $this->getConfig()->get("widget_".$module_name."_data", $module_data , true);
											}

											$this->_internal_modules[ $key ][] = $tmp_module;
										}
									}
								}

							}
						}
					}

					if($modules) {
						foreach($modules as $key => $tmpModules) {
							$this->_internal_modules[ $key ] = array();
							if($tmpModules) {
								foreach($tmpModules as $module) {
									if($module){
										$static_block_id = $module->getBlockId();
										$module_name = $module->getModuleName();
										$module_data = $module->getModuleData();
										$tmp_module = array();
										$tmp_module['order'] = $module->getSortOrder();
										$tmp_module['title'] = $module->getModuleTitle();
										$tmp_module['layout'] = $module->getLayout();
										$tmp_module['layout'] = explode(",", $tmp_module['layout']);
										if(!empty($static_block_id)){
											$tmp_module['content'] = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($static_block_id)->toHtml();
										}else{
											$tmp_module['content'] = $this->getConfig()->get("widget_".$module_name."_data", $module_data , true);
										}
										$this->_internal_modules[ $key ][] = $tmp_module;
									}
								}
							}
							
						}
					}
			}

			return $this->_internal_modules;
		}

		public function loadInternalModule( $position = "", $col = ""){
			$modules = isset($this->_internal_modules[$position])?$this->_internal_modules[$position]:array();
			if($modules){
				$html = '';
				foreach($this->_internal_modules[$position] as $key => $module){
					if(empty($module['layout']) || $this->checkModulueLayout($module['layout'])){
						$html .= '<div class="block module_'.$key.' '.$col.'">';
						if(!empty($module['title'])) { 
							$html .= '<div class="block-title"><strong><span>'.$module['title'].'</span></strong></div>';
						}
						
						$html .= '<div class="block-content">'.$module['content'].'<div class="clear clr"></div></div>';
						$html .= '</div>';
					}

				}
				return $html;
			}
			return;
		}
		/**
		 * load all modules asigned for positions with current layout.
		 */
		public function loadModules (){ 
			$output = array();

			$this->modulesList = $output;
		}
		
		/**
		 * get collection of modules by position
		 */
		public function getModulesByPosition( $position ){

			if( isset($this->modulesList[$position]) ){
				return $this->modulesList[$position];
			}else{
				if($this->_theme_object){
					$this->moduleList[$position] = $this->_theme_object->getChildHtml( $position );
					$this->moduleList[$position] .= $this->loadInternalModule( $position );
					return $this->moduleList[$position];
				}
			}
			return ;
		}
		
		/**
		 * caculate span width of column base grid 12 of twitter.
		 * 
		 * @param Array $ospan 
		 * @param Numberic $cols number of columns
		 */
		public function calculateSpans( $ospans=array(), $cols ){
			$tmp = array_sum($ospans);
			$spans = array();
			$t = 0; 
			for( $i=1; $i<= $cols; $i++ ){
				if( array_key_exists($i,$ospans) ){
					$spans[$i] = 'col-lg-'.$ospans[$i]. ' col-md-'.$ospans[$i] ;
					
				}else{		
					if( (12-$tmp)%($cols-count($ospans)) == 0 ){
						$ts=((12-$tmp)/($cols-count($ospans)));
						$spans[$i] = "col-lg-".$ts.' col-md-'.$ts;
						
					}else {
						if( $t == 0 ) {
							$ts = ( floor((11-$tmp)/($cols-count($ospans))) + 1 ) ;
							$spans[$i] = "col-lg-".$ts;
						}else {
							$ts = ( floor((11-$tmp)/($cols-count($ospans))) + 0 );
							$spans[$i] = "col-lg-".$ts .' col-md-'.$ts;
						}
						$t++;
					}					
				}
			}
			return $spans;
		}

		/**
		 * 
		 */
		public function renderEdtiorThemeForm( ){ 
			$customizeXML = $this->_themeDir.'/development/customize/themeeditor.xml'; 
		 	$output = array( 'selectors' => array(), 'elements' => array() );
	 		if( file_exists($customizeXML) ){
				$info = simplexml_load_file( $customizeXML );
				if( isset($info->selectors->items) ){
					foreach( $info->selectors->items as $item ){
						$vars = get_object_vars($item);
						if( is_object($vars['item']) ){
							$tmp = get_object_vars( $vars['item'] );
							$vars['selector'][] = $tmp;
						}else {
							foreach( $vars['item'] as $selector ){
								$tmp = get_object_vars( $selector );
								if( is_array($tmp) && !empty($tmp) ){
									$vars['selector'][] = $tmp;
								}
							}
						}
						unset( $vars['item'] );
						$output['selectors'][$vars['match']] = $vars;
					}
				}

				if( isset($info->elements->items) ){
					foreach( $info->elements->items as $item ){
						$vars = get_object_vars($item);
						if( is_object($vars['item']) ){
							$tmp = get_object_vars( $vars['item'] );
							$vars['selector'][] = $tmp;
						}else {
							foreach( $vars['item'] as $selector ){
								$tmp = get_object_vars( $selector );
								if( is_array($tmp) && !empty($tmp) ){
									$vars['selector'][] = $tmp;
								}
							}
						}
						unset( $vars['item'] );
						$output['elements'][$vars['match']] = $vars;
					}
				}
			}

			return $output;
		}	

		/**
		 * 
		 */
		public function getPattern( $theme ){
			$output = array(); 

 			$path = DIR_TEMPLATE .$theme .'/image/pattern/'; 
			if( $theme && is_dir($path) ) {
				$files = glob( $path.'*' );
				foreach( $files as $dir ){
					if( preg_match("#.png|.jpg|.gif#", $dir)){
						$output[] = str_replace("","",basename( $dir ) );
					}
				}
			}
			return $output;
		}

		/**
		 * 
		 */
		public function renderAddon( $addon ) {
			$store_switcher = Mage::getSingleton('core/layout')->createBlock('page/switch', "store_switcher")->setTemplate('page/switch/stores.phtml')->toHtml();

			$output = Mage::getSingleton('core/layout')->createBlock('ves_tempcp/list', $addon)
						->assign("store_switcher", $store_switcher)
						->setTemplate('page/addon/'.$addon.'.phtml')->toHtml();
			return $output;
		}

		/**
		 * 
		 */
		public function renderModule( $module, $args = array() ) {
		 	if( file_exists(DIR_APPLICATION.'controller/'.$module.'.php') ){
				return $this->getChild( $module );
			}
			return ;
		}

		public function getMinicartBlock($options = array()) {
			$output = Mage::app()->getLayout()
				            ->createBlock("checkout/cart_sidebar")
				            ->setTemplate('checkout/cart/sidebar.phtml')
				            ->toHtml();
			return $output;
		}

		public function getMinicartHtml() {
			$output = Mage::app()->getLayout()
				            ->createBlock("checkout/cart_sidebar")
				            ->setTemplate('page/html/minicart.phtml')
				            ->toHtml();
			return $output;
		}

		public function getProductImage($product_id, $image_index = 0, $image_width = 200, $image_height = 200){
			$_product = Mage::getModel('catalog/product')->load($product_id);
			$collection = $_product->getMediaGalleryImages();
			if ( count($collection) > 0) {
				$image = null;
				$i = 0;
				foreach($collection as $_image){
					if($i == $image_index){
						$image = $_image;
						break;
					}
					$i++;
				}
				if($image){

					return (string)Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($image_width, $image_height);
				}
				
			}
			return;
		}

		public function getLayoutPath($filepath = "", $default_path = "") {
			$current_catalog_product_path = Mage::getSingleton('core/design_package')->getBaseDir(array('_area' => 'frontend', '_type'=>'template'));
			$current_catalog_product_path .= "/common/";

			$load_file_path = $current_catalog_product_path.$filepath;

			if(file_exists($load_file_path)) {
				return $load_file_path;
			} elseif(file_exists($current_catalog_product_path.$default_path)) {
				return $current_catalog_product_path.$default_path;
			}
			return false;
		}
	}
?>