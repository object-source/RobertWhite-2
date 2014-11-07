/**
 * ||GEISSWEB| EU-VAT-GROUPER
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@geissweb.de so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support at support@geissweb.de.
 *
 * @category     Mage
 * @package      Geissweb_Euvatgrouper
 * @copyright    Copyright (c) 2014 GEISS Weblösungen (http://www.geissweb.de)
 * @license      http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */
AdminOrder.prototype.validateVat = AdminOrder.prototype.validateVat.wrap(function(parentMethod, parameters) // Wraps method of concrete instance
{
    var params = {
        country: $(parameters.countryElementId).value,
        vat: $(parameters.vatElementId).value,
        op_mode: 'SINGLE'
    };

    if(params.vat == "") {
        alert(Translator.translate('Please declare a VAT number before validating.'))
        return;
    }

    if (this.storeId !== false) {
        params.store_id = this.storeId;
    }

    if(document.contains(parameters.groupIdHtmlId))
    {
        var currentCustomerGroupId = $(parameters.groupIdHtmlId).value;
    } else {
        var currentCustomerGroupId = 0;
        parameters.customer_group = 0;
    }

    if( $(parameters.vatElementId).id == 'order-billing_address_vat_id' )
    {
        params.address_type = 'billing';
    } else {
        params.address_type = 'shipping';
    }

    var response_field = 'response_'+$(parameters.vatElementId).id;
    if(typeof(address_id) == 'undefined') {
        address_id = 0;
    }

    new Ajax.Request(parameters.validateUrl, {
        parameters:'taxvat='+params.vat+'&op_mode='+params.op_mode+'&address_type='+params.address_type+'&address_id='+params.address_id,
        onSuccess: function(transport) {

//            try {
                var response = transport.responseText.evalJSON();
                var output = '<div class="vat_response">';
                groupChangeRequired = true;

                if (response.valid_vat == true && typeof(response.faultstring) == "undefined") {
                    output += (Translator.translate('The VAT-ID is valid.'));
                    output += '</div>';
                    message = parameters.vatValidMessage;

                } else if (response.valid_vat == false && typeof(response.faultstring) == "undefined") {
                    output += (Translator.translate('The VAT-ID is invalid.'));
                    output += '</div>';
                    message = parameters.vatInvalidMessage;
                } else {
                    switch (response.faultstring) {
                        case "INVALID_INPUT":
                            output += (Translator.translate('The VAT-ID is invalid, please check the syntax.'));
                            break;
                        case "SERVICE_UNAVAILABLE":
                        case "SERVER_BUSY":
                            output += (Translator.translate('Currently the European VIES service is unavailable.'));
                            break;
                        case "MS_UNAVAILABLE":
                        case "TIMEOUT":
                            output += (Translator.translate('Currently the member state service is unavailable.'));
                            break;
                        default:
                            output += (Translator.translate('There was an error processing your request.'));
                            break;
                    }
                    output += '</div>';
                    message = parameters.vatValidationFailedMessage;
                }
                $(response_field).update(output);

                if (currentCustomerGroupId != response.group) {
                    message = parameters.vatValidAndGroupChangeMessage;
                }
                if(groupChangeRequired) {
                    this.processCustomerGroupChange(parameters.groupIdHtmlId, message, response.group);
                }

                this.loadArea(['totals'], true);

//            } catch (e) {
//                alert(e);
//            }

        }.bind(this)
    });

});