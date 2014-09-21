<?php
/**
 * ||GEISSWEB| EU-VAT-GROUPER
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@geissweb.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.geissweb.de/ for more information
 * or send an email to support@geissweb.de or visit our customer forum at
 * http://forum.geissweb.de to make a feature request.
 *
 * @category     Mage
 * @package      Geissweb_Euvatgrouper
 * @copyright    Copyright (c) 2012 GEISS Weblösungen (http://www.geissweb.de)
 * @license      http://www.geissweb.de/eula.html GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Block_System_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected $_dummyElement;
	protected $_fieldRenderer;
	protected $_values;

	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$html = '
					<tr>
						<td class="label">Extension Version</td>
						<td class="value">'.Mage::getConfig()->getNode('modules/Geissweb_Euvatgrouper')->version.'</td>
					</tr>
		';
		return $html;
	}

	protected function _getFieldRenderer()
	{
		if (empty($this->_fieldRenderer)) {
			$this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
		}
		return $this->_fieldRenderer;
	}

}
