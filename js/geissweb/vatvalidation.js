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
 * @category   Mage
 * @package    Geissweb_Euvatgrouper
 * @copyright  Copyright (c) 2013 GEISS Weblösungen (http://www.geissweb.de)
 * @license    http://www.geissweb.de/eula.html GEISSWEB End User License Agreement
 */
var validateVat = function(vat, op_mode, address_type, address_id)
{
    if(typeof(address_id) == 'undefined') { address_id = 0; }

    try
    {
        if( typeof(vat) == "string" )
        {
            if( vat.match(new RegExp('^[A-Z][A-Z]'))) {
                new Ajax.Request(gw_vat_check_url, {
                    method: 'post',
                    parameters: 'taxvat=' + vat + '&op_mode=' + op_mode + '&address_type=' + address_type + '&address_id=' + address_id,
                    onLoading: function () {
                        switch (op_mode) {
                            case 'SINGLE':
                                $('vatLoader').show();
                                break;
                            case 'MULTI':
                                $(address_type + ':vatLoader').show();
                                break;
                        }
                    },
                    onComplete: function () {
                        switch (op_mode) {
                            case 'SINGLE':
                                $('vatLoader').hide();
                                break;
                            case 'MULTI':
                                $(address_type + ':vatLoader').hide();
                                break;
                        }
                    },
                    onSuccess: function (transport) {
                        // Evaluate validation result
                        var response = transport.responseText.evalJSON();
                        var output = '<ul class="vat_validation-messages" style="margin-top:5px;">';

                        if (response.valid_vat == true && typeof(response.faultstring) == "undefined") {
                            output += '<li class="success-msg">';
                            output += (Translator.translate('Your VAT-ID is valid.'));
                            output += ' ';

                            if (response.is_vat_free == true) {
                                output += (Translator.translate('We have identified you as EU business customer, you can order VAT-exempt in our shop now.'));
                            } else if (response.is_vat_free == false) {
                                output += (Translator.translate('We have identified you as business customer.'));
                            }
                            output += '</li>';
                            output += '</ul>';

                        } else if (response.valid_vat == false && typeof(response.faultstring) == "undefined") {
                            output += '<li class="error-msg">';
                            output += (Translator.translate('Your VAT-ID is invalid, please check the syntax.'));
                            output += '</li></ul>';
                        } else {
                            output += '<li class="notice-msg">';
                            switch (response.faultstring) {
                                case "INVALID_INPUT":
                                    output += (Translator.translate('The given VAT-ID is invalid, please check the syntax. If this error remains please contact us directly to register a customer account with exempt from taxation with us.'));
                                    break;
                                case "SERVICE_UNAVAILABLE":
                                case "SERVER_BUSY":
                                    output += (Translator.translate('Currently the European VIES service is unavailable, but you can proceed with your registration and validate later from your customer account management.'));
                                    break;
                                case "MS_UNAVAILABLE":
                                case "TIMEOUT":
                                    output += (Translator.translate('Currently the member state service is unavailable, we could not validate your VAT-ID to issue an VAT exempt order. Anyhow you can proceed with your registration and validate later in your customer account.'));
                                    break;
                                default:
                                    output += (Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.'));
                                    break;
                            }
                            output += '</li></ul>';
                        }

                        // Validation output
                        switch (op_mode) {
                            case 'SINGLE':
                                $('checkrsp').update(output);
                                break;
                            case 'MULTI':
                                $(address_type + ':checkrsp').update(output);
                                break;
                            default:
                                break;
                        }

                        // OSC Integrations
                        if (gw_osc_integration != '') {
                            this.handleOSC();
                        }


                    },
                    onFailure: function () {
                        switch (op_mode) {
                            case 'SINGLE':
                                $('checkrsp').update('<ul><li class="error-msg">' + Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.') + '</li></ul>');
                                break;
                            case 'MULTI':
                                $(address_type + ':checkrsp').update('<ul><li class="error-msg">' + Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.') + '</li></ul>');
                                break;
                        }
                    }
                });//endajax

            } else if( vat == "") {
                new Ajax.Request(gw_vat_check_url, {
                    method:'post',
                    parameters:'vatid=removed'+'&address_type=' + address_type + '&address_id=' + address_id,
                });//endajax

                switch(op_mode)
                {
                    case 'SINGLE':
                        $('checkrsp').update();
                        break;
                    case 'MULTI':
                        $(address_type+':checkrsp').update();
                        break;
                }

                // OSC Integrations
                if (gw_osc_integration != '') {
                    this.handleOSC();
                }

            } else {
                switch(op_mode)
                {
                    case 'SINGLE':
                        $('checkrsp').update('<ul><li class="notice-msg">'+Translator.translate('Please enter your VAT-ID including the ISO-3166 two letter country code.')+'</li></ul>');
                        break;
                    case 'MULTI':
                        $(address_type+':checkrsp').update('<ul><li class="notice-msg">'+Translator.translate('Please enter your VAT-ID including the ISO-3166 two letter country code.')+'</li></ul>');
                        break;
                }
            }
        }

    } catch (error) {
        switch(op_mode)
        {
            case 'SINGLE':
                $('checkrsp').update('<ul><li class="error-msg">'+Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.')+' '+error+'</li></ul>');
                break;
            case 'MULTI':
                $(address_type+':checkrsp').update('<ul><li class="error-msg">'+Translator.translate('There was an error processing your request. If this error remains please contact us directly to register a customer account with exempt from taxation with us.')+' '+error+'</li></ul>');
                break;
        }
    }


    this.handleOSC = function()
    {
        switch (gw_osc_integration) {
            case 'ONESTEP_CHECKOUT': // Onestepcheckout compatibility (http://www.onestepcheckout.com/?aid=194)
                if (typeof(document.getElementById('onestepcheckout-form')) != "undefined") {
                    get_save_billing_function(document.location.href + 'ajax/save_billing', document.location.href + 'ajax/set_methods_separate')();
                }
                break;

            case 'IWD_CHECKOUT': // For IWD Checkout
                if (typeof(checkout) == 'object') {
                    checkout.update({'review': 1});
                }
                break;

            case 'FME_CHECKOUT': // Compatibility for FME
                if (typeof(document.getElementById('onestepcheckout-form')) != "undefined") {
                    billing.saveCountry();
                }
                break;

            case 'AHEADWORKS_CHECKOUT': // Compatibility with Aheadworks OnePageCheckout
                if (typeof(AWOnestepcheckoutCoreUpdater) != "undefined") {
                    AWOnestepcheckoutCoreUpdater.startRequest(Mage.Cookies.path + '/onestepcheckout/ajax/saveAddress/');
                }
                break;

            case 'ECOMDEV_CHECKOUT':  // EcomDev CheckItOut compatibility
                if (typeof(review) == 'object') review.load();
                break;

            case 'VINAGENTO_CHECKOUT': // Compatibility with Vinagento_Oscheckout
                if (typeof(onestepcheckout) == 'object') onestepcheckout.reloadReview();
                break;

            case 'TM_CHECKOUT': // Compatibility with FireCheckout - Thanks to templates-master
                if (typeof(checkout) == 'object') { // make sure that it's a firecheckout
                    checkout.update(checkout.urls.shopping_cart);
                }
                break;

            case 'GOMAGE_CHECKOUT': // Compat with GoMage Lightcheckout
                if (typeof(checkout) == 'object') { // make sure that it's a firecheckout
                    checkout.submit(checkout.getFormData(), 'get_totals');
                }
                break;

            case 'MAGESTORE_CHECKOUT':  //Compat with MageStore Onestepcheckout
                if (typeof(document.getElementById('checkout-review-load')) != "undefined") {
                    $$('select[name="billing[country_id]"] option').each(function (o) {
                        if (o.readAttribute('value') == response.countryCode) {
                            o.selected = true;
                        }
                    });

                    save_address_information(save_address_url);
                }
                break;

            case 'ECOMTEAM_CHECKOUT': //Compat with Ecommerceteam EasyCheckout
                if (typeof(EasyCheckout) != 'undefined') {
                    EasyCheckout.instance.addressChangedEvent();
                }
                break;

            case 'NEXTBITS_CHECKOUTNEXT': //Compat with Nextbits Checkoutnext

                if (typeof(checkoutnext) != 'undefined') {
                    checkoutnext.reloadReview();
                }

                break;

            case '':
            default:
                break;
        }
    }

}


var vatValidation = function() {
    var op_mode = 'SINGLE';
    this.field_id = 'taxvat';
    this.prefix = '';
    this.address_id = 0;

    this.setOpMode = function ()
    {
        if( document.body.contains(document.getElementById('billing:vat_id'))) {
            this.field_id = 'vat_id';
            this.prefix = 'billing:';
            op_mode = 'MULTI';

        } else if( document.body.contains(document.getElementById('vat_id'))) {
            this.field_id = 'vat_id';
            this.prefix = '';

        } else {
            if( document.body.contains(document.getElementById('billing:taxvat'))) {
                this.field_id = 'taxvat';
                this.prefix = 'billing:';
            } else if( document.body.contains(document.getElementById('taxvat')) ) {
                this.field_id = 'taxvat';
                this.prefix = '';
            }
        }
    };

    this.setListener = function()
    {
        var addr_id = this.address_id;
        if( this.field_id == 'taxvat' )
        {
            $(this.prefix+this.field_id).on('blur', function() {
                var vat = this.value.toUpperCase();
                validateVat(vat, op_mode, 'billing', addr_id);
            });

        } else {
            $(this.prefix+this.field_id).on('blur', function() {
                var vat = this.value.toUpperCase();
                validateVat(vat, op_mode, 'billing', addr_id);
            });

            if( op_mode == 'MULTI' && document.body.contains(document.getElementById('shipping:vat_id')) )
            {
                $('shipping:vat_id').on('blur', function() {
                    var vat = this.value.toUpperCase();
                    validateVat(vat, op_mode, 'shipping', addr_id);
                });
            }

        }
    }

    this.addResponseFields = function(gw_loader_src)
    {
        this.wait_message = '<img class="v-middle" title="'+Translator.translate('Please wait while we validate your VAT-ID')+'" alt="'+Translator.translate('Please wait while we validate your VAT-ID')+'" src="'+ gw_loader_src +'">' +
                            '<span> '+Translator.translate('Please wait while we validate your VAT-ID')+'</span></div>';

        switch(op_mode)
        {
            case 'SINGLE':
                $(this.prefix+this.field_id).insert({
                    after: '<div id="vatLoader">' +
                        this.wait_message +
                        '<div id="checkrsp"></div>'
                });
                $('vatLoader').hide();
            break;

            case 'MULTI':
                $('billing:'+this.field_id).insert({
                    after: '<div id="billing:vatLoader">' +
                        this.wait_message +
                        '<div id="billing:checkrsp"></div>'
                });
                $('billing:vatLoader').hide();

                if( document.body.contains(document.getElementById('shipping:vat_id')) )
                {
                    $('shipping:'+this.field_id).insert({
                        after: '<div id="shipping:vatLoader">' +
                            this.wait_message +
                            '<div id="shipping:checkrsp"></div>'
                    });
                    $('shipping:vatLoader').hide();
                }

            break;
        }
    }

    this.getParams = function(url)
    {
        var parts = url.split('/').slice(1);
        if( url.indexOf("customer/address/edit/id") != -1 )
        {
            this.address_id = parts[parts.length-2];
        }
    }

}

document.observe("dom:loaded", function()
{
    var vatValidator = new vatValidation();
    vatValidator.setOpMode();
    vatValidator.getParams(location.pathname);
    vatValidator.setListener();
    vatValidator.addResponseFields(gw_loader_src);
});