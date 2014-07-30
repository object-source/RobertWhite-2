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
 * @category   Mage
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer resource setup model
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Geissweb_Euvatgrouper_Model_Entity_Setup extends Mage_Customer_Model_Entity_Setup
{

    /**
     * @return array
     */
    public function getDefaultEntities()
    {
        return array(
            'customer' => array(
                'entity_model' => 'customer/customer',
                'table' => 'customer/entity',
                'increment_model' => 'eav/entity_increment_numeric',
                'increment_per_store' => false,
                'attributes' => array(

                    'last_vat_validation_date' => array(
                        'label' => 'Date of last VAT validation',
                        'type' => 'datetime',
                        'input' => 'date',
                        'visible' => true,
                        'backend' => 'eav/entity_attribute_backend_datetime',
                        'frontend' => 'eav/entity_attribute_frontend_datetime',
                        'required' => false,
                        'user_defined' => true,
                        'default' => false,
                        'sort_order' => 12
                    ),
                    'vat_validation_result' => array(
                        'label' => 'Validation successful',
                        'type' => 'int',
                        'input' => 'select',
                        'visible' => true,
                        'source' => 'eav/entity_attribute_source_boolean',
                        'required' => false,
                        'user_defined' => true,
                        'default' => false,
                        'sort_order' => 13
                    ),
                    'vies_result_data' => array(
                        'label' => 'VIES results',
                        'type' => 'text',
                        'input' => 'textarea',
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'default' => false,
                        'sort_order' => 14
                    ),
                ),
            )
        );
    }

}

?>