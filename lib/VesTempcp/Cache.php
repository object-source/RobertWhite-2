<?php 
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class VesTempcp_Cache extends Varien_Io_File{
		
		/**
		 *
		 */
		private $expire = 3600; 
		
		/**
		 *
		 */
		private $ext = 'css';
		
		/**
		 *
		 */
		public function setExtension( $ext='css'){
			if( !is_dir(VES_CSS_CACHE) && !file_exists(VES_CSS_CACHE)){ 
				mkdir( VES_CSS_CACHE, 0755 );
			}

			$this->ext = $ext; 
			return $this;
		}
		
		/**
		 *
		 */
		public function get($key) {
			$files = glob(VES_CSS_CACHE . 'c-' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.'. $this->ext );

			if ($files) {
				$cache = file_get_contents($files[0]);
				
				$data = unserialize($cache);
				
				foreach ($files as $file) {
					$time = substr(strrchr($file, '.'), 1);

					if ($time < time()) {
						if (file_exists($file)) {
							unlink($file);
						}
					}
				}
				
				return $data;			
			}
		}
		
		/**
		 *
		 */
		public function isExisted( $key ){
			return is_file( VES_CSS_CACHE . $key.'.'.$this->ext ); 
		}
		
		/**
		 *
		 */
		public function set($key, $value) {
			$this->delete($key);
			$file = VES_CSS_CACHE . $key .'.'.$this->ext;
			$handle = fopen($file, 'w');
			fwrite($handle,($value));
			fclose($handle);
			@chmod($file, 0755);
			return true;
		}
		
		/**
		 *
		 */
		public function delete($key, $folder_path = "") {
			if($folder_path && file_exists($folder_path)) {
				$files = glob($folder_path . '*.'.$key);
			} else {
				$files = glob(VES_CSS_CACHE . $key . '.*');
			}
			
			if ($files) {
				foreach ($files as $file) {
					if (file_exists($file)) {
						unlink($file);
					}
				}
			}
		}

		/**
	     * Read a file to result, file or stream
	     *
	     * If $dest is null the output will be returned.
	     * Otherwise it will be saved to the file or stream and operation result is returned.
	     *
	     * @param string $filename
	     * @param string|resource $dest
	     * @return boolean|string
	     */
	    public function read($filename, $dest=null)
	    {
	        if (!is_null($dest)) {
	            @chdir($this->_cwd);
	            $result = @copy($filename, $dest);
	            @chdir($this->_iwd);
	            return $result;
	        }

	        @chdir($this->_cwd);
	        $result = @file_get_contents($filename);
	        @chdir($this->_iwd);

	        return $result;
	    }
	    
	    /**
	     * Write a file from string, file or stream
	     *
	     * @param string $filename
	     * @param string|resource $src
	     * @return int|boolean
	     */
	    public function write($filename, $src, $mode=null)
	    {
	        if (is_string($src) && is_readable($src)) {
	            $src = realpath($src);
	            $srcIsFile = true;
	        } elseif (is_string($src) || is_resource($src)) {
	            $srcIsFile = false;
	        } else {
	            return false;
	        }
	        @chdir($this->_cwd);

	        if (file_exists($filename)) {
	            if (!is_writeable($filename)) {
	                printf('File %s don\'t writeable', $filename);
	                return false;
	            }
	        } else {
	            if (!is_writable(dirname($filename))) {
	                printf('Folder %s don\'t writeable', dirname($filename));
	                return false;
	            }
	        }
	        if ($srcIsFile) {
	            $result = @copy($src, $filename);
	        } else {
	            $result = @file_put_contents($filename, $src);
	        }
	        if (!is_null($mode)) {
	            @chmod($filename, $mode);
	        }
	        @chdir($this->_iwd);
	        return $result;
	    }
	}
?>