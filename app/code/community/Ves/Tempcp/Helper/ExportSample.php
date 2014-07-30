<?php

class Ves_Tempcp_Helper_ExportSample extends Mage_Core_Helper_Abstract {

    var $export_static_blocks = "static_blocks.csv";
    var $export_cms_pages = "cms_pages.csv";
    var $export_ves_tempcp = "ves_tempcp.csv";

    public function checkModuleInstalled( $module_name = "") {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        if($modulesArray) {
            $tmp = array();
            foreach($modulesArray as $key=>$value) {
                $tmp[$key] = $value;
            }
            $modulesArray = $tmp;
        }

        if(isset($modulesArray[$module_name])) {

            if((string)$modulesArray[$module_name]->active == "true") {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }
    /**
    * Write Sample Data to File. Store in folder: "skin/frontend/default/ves theme name/import/"
    */
    public function writeSampleDataFile($importDir, $file_name, $content = "") {
        $file = new Varien_Io_File();
        //Create import_ready folder
        if(!file_exists($importDir)) {
            $importReadyDirResult = $file->mkdir($importDir);
            $error = false;
            if (!$importReadyDirResult) {
                //Handle error
                $error = true;
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_tempcp')->__('Can not create folder "%s".', $importDir));
            }
        }

        if (!$file->write($importDir.$file_name, $content)) {
            //Handle error
            $error = true;
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_tempcp')->__('Can not save import sample file "%s".', $file_name));
        }

        if(!$error) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('Successfully, Stored sample data file "%s".', $file_name));
        }

        return !$error;
    }
    /**
    *
    *Read config.xml in skin folder of the theme to get export information before export sample data
    **/
	public function readExportInfo( $theme_name = "", $section = "default" ) {
		$theme_path = Mage::getBaseDir('skin') . '/frontend/'.$section.'/'.$theme_name;
		$config_xml = $theme_path.'/config.xml';
		$result = array();
		/*get config from xml file*/
		if( file_exists($config_xml) ){
			$info = simplexml_load_file( $config_xml, 'SimpleXMLElement', LIBXML_NOCDATA );
			/*get Export Sample Data*/
			if(isset($info->export)){
                if(isset($info->export->theme)) {
                    $result['theme'] = (string)$info->export->theme;
                }

                if(isset($info->export->cms_page)) {
                    $result['cms_page'] = (string)$info->export->cms_page;
                }

                if(isset($info->export->static_block)) {
                    $result['static_block'] = (string)$info->export->static_block;
                }

                if(isset($info->export->modules) && is_object($info->export->modules)) {
                    $attributes = $info->export->modules->attributes();
                    $section = isset($attributes['section'])?trim($attributes['section']):"community";
                    $modules = $info->export->modules->module;
                    $result['modules'] = array();
                    if($modules) {
                        foreach($modules as $module) {
                            $attributes = $module->attributes();
                            $name = isset($attributes['name'])?trim($attributes['name']):"";
                            $type = isset($attributes['type'])?trim($attributes['type']):"json";
                            $module_section = isset($attributes['section'])?trim($attributes['section']):$section;

                            if($name) {
                               $result['modules'][$name] = array();
                               $result['modules'][$name]['section'] = $module_section;
                               $result['modules'][$name]['type'] = $type;
                               $tmp = trim($module);
                               $tmp = str_replace("\n",",", $tmp);
                               $tmp_array = explode(",", $tmp);

                               $result['modules'][$name]['tables'] = $tmp_array;

                            }
                            
                        }
                    }
                    
                }
				
			}
		}
		return $result;
	}

	public function export($theme_name = "", $export_mode = "full", $section = "default") {
		if($theme_name) {
			$export_list = $this->readExportInfo( $theme_name, $section );
            $importDir = Mage::getBaseDir('skin') . '/frontend/'.$section.'/'.$theme_name.'/import/';
            $module_import_dir = $importDir.'modules/';
            $cms_import_dir = $importDir.'cms/';

            if($export_mode == "setting") {
                $importDir = Mage::getBaseDir('cache') ."/backup_".$theme_name.'/';
                $module_import_dir = $importDir;
            }
            
            
            
			/*Export modules*/
            if(isset($export_list['modules'])) {
                foreach($export_list['modules'] as $key => $module) {
                    if($module) {
                        $type = isset($module['type'])?$module['type']:"json";
                        $tables = isset($module['tables'])?$module['tables']:array();
                        if($tables) {
                            $tmp = array();
                            foreach($tables as $table) {
                                $table = trim($table);
                                if(!empty($table)) {
                                    $tmp[] = $key."/".trim($table);
                                }
                            }
                            $tables = $tmp;
                        }

                        if( $module_sample_data = $this->exportSample( $key, $tables, $type, $export_mode ) ) {
                            $this->writeSampleDataFile( $module_import_dir, $key.".".$type, $module_sample_data);
                        }
                    }
                }
            }
            if($export_mode == "full") {
    			/*Export cms pages*/
                if(isset($export_list['cms_page'])) {

                    $content    = Mage::app()->getLayout()->createBlock('ves_tempcp/adminhtml_cms_enhanced_page_grid')->getCsvFile($this->export_cms_pages);
                    $cms_content = "";
                    if(!empty($content) && isset($content['value']) && file_exists($content['value'])) {
                        $cms_content = file_get_contents($content['value']);
                    }
                    if($cms_content) {
                        $this->writeSampleDataFile( $cms_import_dir, $this->export_cms_pages, $cms_content);
                    }
                }
    			/*Export static blocks*/
                if(isset($export_list['static_block'])) {

                    $content    = $content    = Mage::app()->getLayout()->createBlock('ves_tempcp/adminhtml_cms_enhanced_block_grid')->getCsvFile($this->export_static_blocks);

                    $static_content = "";
                    if(!empty($content) && isset($content['value']) && file_exists($content['value'])) {
                        $static_content = file_get_contents($content['value']);
                    }
                    if($static_content) {
                        $this->writeSampleDataFile( $cms_import_dir, $this->export_static_blocks, $static_content);
                    }

                }
    			/*Export theme setting*/
                if(isset($export_list['theme'])) {

                    $content    = Mage::app()->getLayout()->createBlock('ves_tempcp/adminhtml_cms_enhanced_theme_grid')->getCsvFile( $this->export_ves_tempcp );

                    $theme_content = "";

                    if(!empty($content) && isset($content['value']) && file_exists($content['value'])) {
                        $theme_content = file_get_contents($content['value']);
                    }

                    if($theme_content) {
                        $this->writeSampleDataFile( $importDir, $this->export_ves_tempcp, $theme_content);
                    }

                }
            }
		}
	}
	/**
    * Export module sample data: support CSV and JSON
    * @module: Name of module which you want export data (for example: ves_megamenu)
    * @tables: List table name which you want export sample (for example a table name: ves_megamenu/megamenu)
    * @type: type of data which you want export
    * @return: return string of CSV or JSON
    **/
    public function exportSample($module = "", $tables = array(), $type = "json", $export_mode = "full") {

        if(!$this->checkModuleInstalled($module)) 
            return false;

        $result = "";
        switch ($type) {
        	case 'csv' :
        	
        	break;
        	case 'sql' :
        		
        	break;
            case 'json' :
            default:
                $data = array();
                if($export_mode == "full") {
                    /**
                     * Get the resource model
                     */
                    $resource = Mage::getSingleton('core/resource');
                     
                    /**
                     * Retrieve the read connection
                     */
                    $readConnection = $resource->getConnection('core_read');
                    
                    if($tables) {
                        foreach ($tables as $table_name) {
                            $table_name = trim($table_name);
                            $table_name = strtolower($table_name);
                            $query = 'SELECT * FROM ' . $resource->getTableName($table_name);
                        
                            /**
                             * Execute the query and store the results in $results
                             */
                            $megamenus = $readConnection->fetchAll($query);

                            $data[ $table_name ] = $megamenus;  
                        }
                    }
                }
                $module = strtolower($module);

                $config = Mage::getStoreConfig($module); //array

                $data['config'] = $config;
                
                $result = Mage::helper('core')->jsonEncode($data);

                break;
        }
        
        return $result;
    }
}