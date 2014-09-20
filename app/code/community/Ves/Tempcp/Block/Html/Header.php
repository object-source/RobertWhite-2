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
 * @category    Mage
 * @package     Mage_Page
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ves_Tempcp_Block_Html_Header extends Mage_Page_Block_Html_Header
{
    public function _construct()
    {
        /*Check framework was loaded*/
        $helper = Mage::helper("ves_tempcp/framework")->getFramework( );
        if(!$helper || !is_object($helper)) {
            $packageName =  Mage::getSingleton('core/design_package')->getPackageName();
            if(!$packageName) {
                $packageName = "default";
            }

            $themeName =  Mage::getDesign()->getTheme('frontend');
            $themeName = $packageName."/".$themeName;
            $themeConfig = Mage::helper('ves_tempcp/theme')->getCurrentTheme();
            $helper = Mage::helper("ves_tempcp/framework")->initFramework( $themeName, $themeConfig );
        } else {
            $themeConfig = $helper->getConfig();
        }
        
        /*get header layout*/
        $header_layout = $helper->getParam("header_layout", $themeConfig->get("header_layout"));
        $header_template = 'page/html/header.phtml';
        /*check header layout file is exists*/
        if($header_layout) {
            if($header_file_path = Mage::helper("ves_tempcp/framework")->getLayoutPath("header/".$header_layout.".phtml","header/default.phtml")) {
                $header_template = 'common/header/'.$header_layout.'.phtml';
            }
        }

        $this->setTemplate($header_template);
    }
}
