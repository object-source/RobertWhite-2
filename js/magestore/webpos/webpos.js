var count_loading = 0;

//check empty fields
function checkEmptyFields(container)
{
    var empty = false;
    if (container.id == 'billing-new-address-form') {
        if ($('billing:country_id') && $('billing:country_id').value == '' && $('billing:country_id').style.display != 'none' && $('billing:country_id').classList.contains('validate-select'))
            empty = true;
        if ($('billing:region_id') && $('billing:region_id').value == '' && $('billing:region_id').style.display != 'none' && $('billing:region_id').classList.contains('validate-select'))
            empty = true;
        if ($('billing:region') && $('billing:region').value == '' && $('billing:region').style.display != 'none' && $('billing:region').classList.contains('required-entry'))
            empty = true;
        if ($('billing:postcode') && $('billing:postcode').value == '' && $('billing:postcode').classList.contains('required-entry'))
            empty = true;
        if ($('billing:city') && $('billing:city').value == '' && $('billing:city').classList.contains('required-entry'))
            empty = true;
        if ($('billing:telephone') && $('billing:telephone').value == '' && $('billing:telephone').classList.contains('required-entry'))
            empty = true;
    }
    if (container.id == 'shipping-new-address-form') {
        if ($('shipping:country_id') && $('shipping:country_id').value == '' && $('shipping:country_id').style.display != 'none' && $('shipping:country_id').classList.contains('validate-select'))
            empty = true;
        if ($('shipping:region_id') && $('shipping:region_id').value == '' && $('shipping:region_id').style.display != 'none' && $('shipping:region_id').classList.contains('validate-select'))
            empty = true;
        if ($('shipping:region') && $('shipping:region').value == '' && $('shipping:region').style.display != 'none' && $('shipping:region').classList.contains('required-entry'))
            empty = true;
        if ($('shipping:postcode') && $('shipping:postcode').value == '' && $('shipping:postcode').classList.contains('required-entry'))
            empty = true;
        if ($('shipping:city') && $('shipping:city').value == '' && $('shipping:city').classList.contains('required-entry'))
            empty = true;
        if ($('shipping:telephone') && $('shipping:telephone').value == '' && $('shipping:telephone').classList.contains('required-entry'))
            empty = true;
    }
    return empty;
}
function check_valid_email(transport) {
    var response = getResponseText(transport);
    var message = response.message;
    if (message == 'valid') {
        $('email-error-message').update('');
        $('valid_email_address_image').show();
    }
    else if (message == 'invalid') {
        $('valid_email_address_image').hide();
        $('email-error-message').update('<p>Invalid Email Address</p>');
    }
    else if (message == 'exists') {
        $('valid_email_address_image').hide();
        if (show_login_link)
            $('email-error-message').update('<p>Email address already registered. Please <a href="" onclick="login_popup.show(); return false;">log in</a> or use another email address.</p>');
        else {
            $('email-error-message').update('<p>Email address already registered. Please use another email address.</p>');
        }
    }
}

function getResponseText(transport) {
    if (transport && transport.responseText) {
        try {
            response = eval('(' + transport.responseText + ')');
        }
        catch (e) {
            response = {};
        }
    }
    return response;
}

function get_billing_data(parameters) {
    var input_billing_array = $$('input[name^=billing]');
    var select_billing_array = $$('select[name^=billing]');
    var textarea_billing_array = $$('textarea[name^=billing]');
    var street_count = 0;

    for (var i = 0; i < textarea_billing_array.length; i++) {
        var item = textarea_billing_array[i];
        parameters[item.name] = item.value;
    }

    for (var i = 0; i < input_billing_array.length; i++) {
        var item = input_billing_array[i];
        if (item.type == 'checkbox') {
            if (item.checked) {
                parameters[item.name] = item.value;
            }
        }
        else {
            if (item.name == 'billing[street][]') {
                var name = 'billing[street][' + street_count + ']';
                parameters[name] = item.value;
                street_count = street_count + 1;
            }
            else {
                parameters[item.name] = item.value;
            }
        }
    }

    var street_count = 0;
    for (var i = 0; i < select_billing_array.length; i++) {
        var item = select_billing_array[i];
        //data[item.name] = item.value;
        if (item.type == 'checkbox') {
            if (item.checked) {
                parameters[item.name] = item.value;
            }
        }
        else {
            if (item.name == 'billing[street][]') {
                var name = 'billing[street][' + street_count + ']';
                parameters[name] = item.value;
                street_count = street_count + 1;
            }
            else {
                parameters[item.name] = item.value;
            }
        }
    }
}

function get_shipping_data(parameters) {
    var input_shipping_fields = $$('input[name^=shipping]');
    var select_shipping_fields = $$('select[name^=shipping]');
    var street_count = 0;
    for (var i = 0; i < input_shipping_fields.length; i++) {
        var item = input_shipping_fields[i];
        if (item.type == 'checkbox') {
            if (item.checked) {
                parameters[item.name] = item.value;
            }
        }
        else {
            if (item.name != 'shipping_method') {
                if (item.name == 'shipping[street][]') {
                    var name = 'shipping[street][' + street_count + ']';
                    parameters[name] = item.value;
                    street_count = street_count + 1;
                }
                else {
                    parameters[item.name] = item.value;
                }
            }
        }
    }

    var street_count = 0;
    for (var i = 0; i < select_shipping_fields.length; i++) {
        var item = select_shipping_fields[i];
        //data[item.name] = item.value;
        if (item.type == 'checkbox') {
            if (item.checked) {
                parameters[item.name] = item.value;
            }
        }
        else {
            if (item.name != 'shipping_method') {
                if (item.name == 'shipping[street][]') {
                    var name = 'shipping[street][' + street_count + ']';
                    parameters[name] = item.value;
                    street_count = street_count + 1;
                }
                else {
                    parameters[item.name] = item.value;
                }
            }
        }
    }
}

function showLoading() {

}
var check = 0;
function save_address_information(save_address_url) {
    var form = $('one-step-checkout-form');
    var shipping_method = $RF(form, 'shipping_method');
    var parameters = {shipping_method: shipping_method};

    get_billing_data(parameters);
    get_shipping_data(parameters);

    var shipping_method_section = $$('div.onestepcheckout-shipping-method-section')[0];
    if (typeof shipping_method_section != 'undefined') {
        shipping_method_section.update('<div class="ajax-loader1"></div>');
    }

    var payment_method_section = $$('div.onestepcheckout-payment-methods')[0];
    payment_method_section.update('<div class="ajax-loader2">&nbsp;</div>');

    var review = $('checkout-review-load');
    review.update('<div class="ajax-loader3"></div>');

    count_loading = count_loading + 1;
    $('onestepcheckout-button-place-order').disabled = true;
    $('onestepcheckout-button-place-order').removeClassName('btn-checkout');
    $('onestepcheckout-button-place-order').addClassName('place-order-loader');
    var request = new Ajax.Request(save_address_url, {
        parameters: parameters,
        onSuccess: function(transport) {
            if (transport.status == 200) {
                var response = getResponseText(transport);
                count_loading = count_loading - 1;
                if (count_loading == 0) {
                    if (typeof shipping_method_section != 'undefined') {
                        shipping_method_section.update(response.shipping_method);
                    }
                    payment_method_section.update(response.payment_method);
                    // show payment form if available
                    if ($RF(form, 'payment[method]') != null) {
                        try {
                            var payment_method = $RF(form, 'payment[method]');
                            $('container_payment_method_' + payment_method).show();
                            $('payment_form_' + payment_method).show();
                        } catch (err) {

                        }
                    }
					if(check%2 == 0){
						save_address_information(save_address_url);
					}else{
						review.update(response.review);
						save_shipping_method(shipping_method_url);
						$('onestepcheckout-button-place-order').disabled = false;
						$('onestepcheckout-button-place-order').addClassName('btn-checkout');
						$('onestepcheckout-button-place-order').removeClassName('place-order-loader');
					}check++;
                    
                }
            }
        },
        onFailure: ''
    });
}

function save_shipping_method(shipping_method_url, enable_update_payment) {
    if (typeof enable_update_payment == 'undefined') {
        var enable_update_payment = false;
    }

    var form = $('one-step-checkout-form');
    var shipping_method = $RF(form, 'shipping_method');
    var payment_method = $RF(form, 'payment[method]');

    //reload payment only if this feature is enabled in admin - show image loading
    var payment_method_section = $$('div.onestepcheckout-payment-methods')[0];
    if (enable_update_payment) {
        payment_method_section.update('<div class="ajax-loader2">&nbsp;</div>');
    }

    //show image loading for review total
    var review = $('checkout-review-load');
    review.update('<div class="ajax-loader3"></div>');

    var parameters = {
        shipping_method: shipping_method,
        payment_method: payment_method
    };

    //Find payment parameters and include 
    var items = $$('input[name^=payment]', 'select[name^=payment]');
    var names = items.pluck('name');
    var values = items.pluck('value');

    for (var x = 0; x < names.length; x++) {
        if (names[x] != 'payment[method]') {
            parameters[names[x]] = values[x];
        }
    }
    $('onestepcheckout-button-place-order').disabled = true;
    $('onestepcheckout-button-place-order').removeClassName('btn-checkout');
    $('onestepcheckout-button-place-order').addClassName('place-order-loader');
    var request = new Ajax.Request(shipping_method_url, {
        method: 'post',
        parameters: parameters,
        onFailure: '',
        onSuccess: function(transport) {
            if (transport.status == 200) {
                var response = getResponseText(transport);
                if (enable_update_payment) {
                    payment_method_section.update(response.payment);
                    // show payment form if available
                    if ($RF(form, 'payment[method]') != null) {
                        try {
                            var payment_method = $RF(form, 'payment[method]');
                            $('container_payment_method_' + payment_method).show();
                            $('payment_form_' + payment_method).show();
                        } catch (err) {

                        }
                    }
                }
                review.update(response.review);
                $('onestepcheckout-button-place-order').disabled = false;
                $('onestepcheckout-button-place-order').addClassName('btn-checkout');
                $('onestepcheckout-button-place-order').removeClassName('place-order-loader');
                if (onestepcheckoutinadmin)
                    recollectTotal();
                var swiper = $('ccsave-swiper-data');
                if (swiper)
                    swiper.focus();
            }
        }
    });

}

function updateSection(transport) {
    var response = getResponseText(transport);
    if (response.shipping_method) {
        var shipping_method_section = $$('div.onestepcheckout-shipping-method-section')[0];
        if (typeof shipping_method_section != 'undefined') {

        }
    }
    if (response.payment_method) {

    }
}

function add_coupon_code(add_coupon_url) {
    var review = $('checkout-review-load');
    review.update('<div class="ajax-loader3"></div>');
    var coupon_code = $('coupon_code_onestepcheckout').value;
    var parameters = {coupon_code: coupon_code};
    var request = new Ajax.Request(add_coupon_url, {
        method: 'post',
        onFailure: '',
        parameters: parameters,
        onSuccess: function(transport) {
            var response = getResponseText(transport);
            if (response.error) {
                alert(response.message);
                review.update(response.review_html);
                $('remove_coupon_code_button').hide();
                recollectTotal();
            }
            else {
                // alert(response.message);
                review.update(response.review_html);
                $('remove_coupon_code_button').show();
                recollectTotal();
            }
        }
    });

}

function remove_coupon_code(add_coupon_url) {
    var review = $('checkout-review-load');
    var remove = 1;
    review.update('<div class="ajax-loader3"></div>');
    var coupon_code = $('coupon_code_onestepcheckout').value;
    var parameters = {coupon_code: coupon_code, remove: remove};
    var request = new Ajax.Request(add_coupon_url, {
        method: 'post',
        onFailure: '',
        parameters: parameters,
        onSuccess: function(transport) {
            var response = getResponseText(transport);
            if (response.error) {
                alert(response.message);
                review.update(response.review_html);
//                recollectTotal();
            }
            else {
                review.update(response.review_html);
                $('coupon_code_onestepcheckout').value = '';
                $('remove_coupon_code_button').hide();
                recollectTotal();
            }
        }
    });
}

function setNewAddress(isNew, type, save_address_url) {
    if (isNew) {
        resetSelectedAddress(type);
        $(type + '-new-address-form').show();
    }
    else {
        $(type + '-new-address-form').hide();
    }
    save_address_information(save_address_url);
}

function resetSelectedAddress(type) {
    var selectElement = $(type + '-address-select')
    if (selectElement) {
        selectElement.value = '';
    }
}

function showLogin(url) {
    TINY.box.show(url, 1, 400, 250, 150);
    return false;
}

function showpwdbox(url) {
    TINY.box.show(url, 1, 400, 250, 150);
    return false;
}

function showTermsAndCondition() {
    TINY.box.show(show_term_condition_url, 1, term_popup_width, term_popup_height, 150);
    return false;
}

function loginProcess(transport) {
    var response = getResponseText(transport);
    if (response.error && response.error != '') {
        $('onestepcheckout-login-error-message').update(response.error);
        $('onestepcheckout-login-error-message').show();
        disableLoginLoading();
    }
    else {
        $('onestepcheckout-login-error-message').hide();
        window.location = window.location;
    }
}

function passwordProcess(transport) {
    var response = getResponseText(transport);
    if (response.success) {
        $('onestepcheckout-password-error-message').hide();
        $('onestepcheckout-password-loading').hide();
        $('onestepcheckout-password-success-message').show();
    }
    else {
        if (response.error && response.error != '') {
            $('onestepcheckout-password-error-message').update(response.error);
            $('onestepcheckout-password-error-message').show();
            disablePassLoading();
        }
    }
}

function showPassLoading() {
    $('onestepcheckout-password-error-message').hide();
    $('osc-forgotpassword-form').hide();
    $('onestepcheckout-password-loading').show();
}

function disablePassLoading() {
    $('osc-forgotpassword-form').show();
    $('onestepcheckout-password-loading').hide();
}


function showLoginLoading() {
    $('onestepcheckout-login-error-message').hide();
    $('onestepcheckout-login-form').hide();
    $('onestepcheckout-login-loading').show();
}

function disableLoginLoading() {
    $('onestepcheckout-login-form').show();
    $('onestepcheckout-login-loading').hide();
}

function change_class_name(element, oldStep, newStep) {
    if (element) {
        element.removeClassName('step_' + oldStep);
        element.addClassName('step_' + newStep);
    }
}

function $RF(el, radioGroup) {
    if ($(el).type && $(el).type.toLowerCase() == 'radio') {
        var radioGroup = $(el).name;
        var el = $(el).form;
    } else if ($(el).tagName.toLowerCase() != 'form') {
        return false;
    }

    var checked = $(el).getInputs('radio', radioGroup).find(
            function(re) {
                return re.checked;
            }
    );
    return (checked) ? $F(checked) : null;
}

function initWhatIsCvvListeners() {
    $$('.cvv-what-is-this').each(function(element) {
        Event.observe(element, 'click', toggleToolTip);
    });
}

function checkPaymentMethod() {
    var options = document.getElementsByName('payment[method]');
    var pay = true;
    for (var i = 0; i < options.length; i++) {
        if ($(options[i].id).checked) {
            pay = false;
            break;
        }
    }
    return pay;
}

function addGiftwrap(url) {
    var parameters = {};
    if (!$('onestepcheckout_giftwrap_checkbox').checked) {
        parameters['remove'] = 1;
    } else {
        var options = document.getElementsByName('payment[method]');
        if (checkPaymentMethod()) {
            if ($(options[0].id))
                $(options[0].id).checked = true;
        }
    }
    var summary = $('checkout-review-load');
    summary.update('<div class="ajax-loader3">&nbsp;</div>');

    new Ajax.Request(url, {
        method: 'post',
        parameters: parameters,
        onFailure: '',
        onSuccess: function(transport) {
            if (transport.status == 200) {
                summary.update(transport.responseText);
                save_shipping_method(shipping_method_url, enable_update_payment);
            }
        }
    });
}

/**
 * FORM LOGIN
 **/
var OneStepCheckoutLoginPopup = Class.create({
    initialize: function(options) {
        this.options = options;
        this.popup_container = $('onestepcheckout-login-popup');
        this.popup_link = $('onestepcheckout-login-link');
        this.popup = null;
        this.createPopup();
        this.mode = 'login';

        this.forgot_password_link = $('onestepcheckout-forgot-password-link');
        this.forgot_password_container = $('onestepcheckout-login-popup-contents-forgot');
        this.forgot_password_loading = $('onestepcheckout-forgot-loading');
        this.forgot_password_error = $('onestepcheckout-forgot-error');
        this.forgot_password_success = $('onestepcheckout-forgot-success');
        this.forgot_password_button = $('onestepcheckout-forgot-button');
        this.forgot_password_table = $('onestepcheckout-forgot-table');

        this.login_link = $('onestepcheckout-return-login-link');
        this.login_container = $('onestepcheckout-login-popup-contents-login');
        this.login_table = $('onestepcheckout-login-table');
        this.login_error = $('onestepcheckout-login-error');
        this.login_loading = $('onestepcheckout-login-loading');
        this.login_button = $('onestepcheckout-login-button');
        this.login_form = $('onestepcheckout-login-form');
        this.login_username = $('id_onestepcheckout_username');

        /* Bindings for the enter button */
        var login_validator = new Validation('onestepcheckout-login-form');
        this.keypress_handler = function(e) {
            if (e.keyCode == Event.KEY_RETURN) {
                if (login_validator.validate()) {
                    e.preventDefault();

                    if (this.mode == 'login') {
                        this.login_handler();
                    } else if (this.mode == 'forgot') {
                        this.forgot_password_handler();
                    }
                }
            }
        }.bind(this);

        this.login_handler = function(e) {
            if (login_validator.validate()) {
                var parameters = this.login_form.serialize(true);
                var url = this.options.login_url;
                this.showLoginLoading();

                new Ajax.Request(url, {
                    method: 'post',
                    parameters: parameters,
                    onSuccess: function(transport) {
                        var result = transport.responseText.evalJSON();
                        if (result.success) {
                            window.location = window.location;
                        } else {
                            this.showLoginError(result.error);
                        }
                    }.bind(this)
                });
            }
        };

        var fogot_validator = new Validation('onestepcheckout-forgot-form');
        this.forgot_password_handler = function(e) {
            var email = $('id_onestepcheckout_email').getValue();

            /*  if(email == '') {
             alert(this.options.translations.invalid_email);
             return;
             } */
            if (fogot_validator.validate()) {
                this.showForgotPasswordLoading();

                /* Prepare AJAX call */
                var url = this.options.forgot_password_url;

                new Ajax.Request(url, {
                    method: 'post',
                    parameters: {email: email},
                    onSuccess: function(transport) {
                        var result = transport.responseText.evalJSON();

                        if (result.success) {
                            /* Show success message */
                            this.showForgotPasswordSuccess();

                            /* Pre-set username to simplify login */
                            this.login_username.setValue(email);
                        } else {
                            /* Show error message */
                            this.showForgotPasswordError();
                        }

                    }.bind(this)
                });
            }
        };

        this.bindEventHandlers();
    },
    bindEventHandlers: function() {
        /* First bind the link for opening the popup */
        if (this.popup_link) {
            this.popup_link.observe('click', function(e) {
                e.preventDefault();
                this.popup.open();
            }.bind(this));
        }

        /* Link for closing the popup */
        if (this.popup_container) {
            this.popup_container.select('p.close a').invoke(
                    'observe', 'click', function(e) {
                this.popup.close();
            }.bind(this));
        }

        /* Link to switch between states */
        if (this.login_link) {
            this.login_link.observe('click', function(e) {
                e.preventDefault();
                this.forgot_password_container.hide();
                this.login_container.show();
                this.mode = 'login';
            }.bind(this));
        }

        /* Link to switch between states */
        if (this.forgot_password_link) {
            this.forgot_password_link.observe('click', function(e) {
                e.preventDefault();
                this.login_container.hide();
                this.forgot_password_container.show();
                this.mode = 'forgot';
            }.bind(this));
        }

        /* Now bind the submit button for logging in */
        if (this.login_button) {
            this.login_button.observe(
                    'click', this.login_handler.bind(this));
        }

        /* Now bind the submit button for forgotten password */
        if (this.forgot_password_button) {
            this.forgot_password_button.observe('click',
                    this.forgot_password_handler.bind(this));
        }

        /* Handle return keypress when open */
        if (this.popup) {
            this.popup.observe('afterOpen', function(e) {
                document.observe('keypress', this.keypress_handler);
            }.bind(this));

            this.popup.observe('afterClose', function(e) {
                this.resetPopup();
                document.stopObserving('keypress', this.keypress_handler);
            }.bind(this));
        }

    },
    resetPopup: function() {
        this.login_table.show();
        this.forgot_password_table.show();

        this.login_loading.hide();
        this.forgot_password_loading.hide();

        this.login_error.hide();
        this.forgot_password_error.hide();

        this.login_container.show();
        this.forgot_password_container.hide();
    },
    showLoginError: function(error) {
        this.login_table.show();
        this.login_error.show();
        this.login_loading.hide();

        if (error) {
            this.login_error.update(error);
        }
    },
    showLoginLoading: function() {
        this.login_table.hide();
        this.login_loading.show();
        this.login_error.hide();
    },
    showForgotPasswordSuccess: function() {
        this.forgot_password_error.hide();
        this.forgot_password_loading.hide();
        this.forgot_password_table.hide();
        this.forgot_password_success.show();
    },
    showForgotPasswordError: function() {
        this.forgot_password_error.show();
        this.forgot_password_error.update(
                this.options.translations.email_not_found),
                this.forgot_password_table.show();
        this.forgot_password_loading.hide();
    },
    showForgotPasswordLoading: function() {
        this.forgot_password_loading.show();
        this.forgot_password_error.hide();
        this.forgot_password_table.hide();
    },
    show: function() {
        this.popup.open();
    },
    createPopup: function() {
        this.popup = new Control.Modal(this.popup_container, {
            overlayOpacity: 0.65,
            fade: true,
            fadeDuration: 0.3
        });
    }
});

//Validate Radio
function $RF(el, radioGroup) {
    if ($(el).type && $(el).type.toLowerCase() == 'radio') {
        var radioGroup = $(el).name;
        var el = $(el).form;
    } else if ($(el).tagName.toLowerCase() != 'form') {
        return false;
    }

    var checked = $(el).getInputs('radio', radioGroup).find(
            function(re) {
                return re.checked;
            }
    );
    return (checked) ? $F(checked) : null;
}

function $RFF(el, radioGroup) {
    if ($(el).type && $(el).type.toLowerCase() == 'radio') {
        var radioGroup = $(el).name;
        var el = $(el).form;
    } else if ($(el).tagName.toLowerCase() != 'form') {
        return false;
    }
    return $(el).getInputs('radio', radioGroup).first();
}

function get_separate_save_methods_function(url, update_payments)
{
    if (typeof update_payments == 'undefined') {
        var update_payments = false;
    }

    return function(e) {
        if (typeof e != 'undefined') {
            var element = e.element();

            if (element.name != 'shipping_method') {
                update_payments = false;
            }
        }

        var form = $('one-step-checkout-form');
        var shipping_method = $RF(form, 'shipping_method');
        var payment_method = $RF(form, 'payment[method]');
        var totals = get_totals_element();

        var freeMethod = $('p_method_free');
        if (freeMethod) {
            payment.reloadcallback = true;
            payment.countreload = 1;
        }

        totals.update('<div class="loading-ajax">&nbsp;</div>');

        if (update_payments) {
            var payment_methods = $$('div.payment-methods')[0];
            payment_methods.update('<div class="loading-ajax">&nbsp;</div>');
        }

        var parameters = {
            shipping_method: shipping_method,
            payment_method: payment_method
        }

        /* Find payment parameters and include */
        var items = $$('input[name^=payment]').concat($$('select[name^=payment]'));
        var names = items.pluck('name');
        var values = items.pluck('value');

        for (var x = 0; x < names.length; x++) {
            if (names[x] != 'payment[method]') {
                parameters[names[x]] = values[x];
            }
        }

        new Ajax.Request(url, {
            method: 'post',
            onSuccess: function(transport) {
                if (transport.status == 200) {
                    var data = transport.responseText.evalJSON();
                    var form = $('onestepcheckout-form');

                    totals.update(data.summary);

                    if (update_payments) {

                        payment_methods.replace(data.payment_method);

                        $$('div.payment-methods input[name^=payment\[method\]]').invoke('observe', 'click', get_separate_save_methods_function(url));
                        $$('div.payment-methods input[name^=payment\[method\]]').invoke('observe', 'click', function() {
                            $$('div.onestepcheckout-payment-method-error').each(function(item) {
                                new Effect.Fade(item);
                            });
                        });

                        if ($RF($('one-step-checkout-form'), 'payment[method]') != null) {
                            try {
                                var payment_method = $RF(form, 'payment[method]');
                                $('container_payment_method_' + payment_method).show();
                                $('payment_form_' + payment_method).show();
                            } catch (err) {

                            }
                        }
                    }
                }
            },
            parameters: parameters
        });
    }
}

function updateRegion(countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl)
{
    if (regions[countryEl.value]) {
        var i, option, region, def;

        def = regionSelectEl.getAttribute('defaultValue');
        if (regionTextEl) {
            if (!def) {
                def = regionTextEl.value.toLowerCase();
            }
            regionTextEl.value = '';
        }

        regionSelectEl.options.length = 1;
        for (regionId in regions[countryEl.value]) {
            region = regions[countryEl.value][regionId];

            option = document.createElement('OPTION');
            option.value = regionId;
            option.text = region.name.stripTags();
            option.title = region.name;

            if (regionSelectEl.options.add) {
                regionSelectEl.options.add(option);
            } else {
                regionSelectEl.appendChild(option);
            }

            if (regionId == def || (region.name && region.name.toLowerCase() == def) ||
                    (region.name && region.code.toLowerCase() == def)
                    ) {
                regionSelectEl.value = regionId;
            }
        }

        if (disableAction == 'hide') {
            if (regionTextEl) {
                regionTextEl.style.display = 'none';
            }

            regionSelectEl.style.display = '';
        } else if (disableAction == 'disable') {
            if (regionTextEl) {
                regionTextEl.disabled = true;
            }
            regionSelectEl.disabled = false;
        }
        setMarkDisplay(regionSelectEl, true);
    } else {
        if (disableAction == 'hide') {
            if (regionTextEl) {
                regionTextEl.style.display = '';
            }
            regionSelectEl.style.display = 'none';
            Validation.reset(regionSelectEl);
        } else if (disableAction == 'disable') {
            if (regionTextEl) {
                regionTextEl.disabled = false;
            }
            regionSelectEl.disabled = true;
        } else if (disableAction == 'nullify') {
            regionSelectEl.options.length = 1;
            regionSelectEl.value = '';
            regionSelectEl.selectedIndex = 0;
            lastCountryId = '';
        }
        setMarkDisplay(regionSelectEl, false);
    }

    _checkRegionRequired(countryEl, regionTextEl, regionSelectEl, regions['config']);
    // Make Zip and its label required/optional
    var zipUpdater = new ZipUpdater(countryEl.value, zipEl);
    zipUpdater.update();
}

function setMarkDisplay(elem, display)
{
    elem = $(elem);
    var labelElement = elem.up(0).down('label > span.required') ||
            elem.up(1).down('label > span.required') ||
            elem.up(0).down('label.required > em') ||
            elem.up(1).down('label.required > em');
    if (labelElement) {
        inputElement = labelElement.up().next('input');
        if (display) {
            labelElement.show();
            if (inputElement) {
                inputElement.addClassName('required-entry');
            }
        } else {
            labelElement.hide();
            if (inputElement) {
                inputElement.removeClassName('required-entry');
            }
        }
    }
}

function _checkRegionRequired(countryEl, regionTextEl, regionSelectEl, config)
{
    var label, wildCard;
    var elements = [regionTextEl, regionSelectEl];
    var that = this;
    if (typeof config == 'undefined') {
        return;
    }
    var regionRequired = config.regions_required.indexOf(countryEl.value) >= 0;

    elements.each(function(currentElement) {
        Validation.reset(currentElement);
        label = $$('label[for="' + currentElement.id + '"]')[0];
        if (label) {
            wildCard = label.down('em') || label.down('span.required');
            if (!that.config.show_all_regions) {
                if (regionRequired) {
                    label.up().show();
                } else {
                    label.up().hide();
                }
            }
        }

        if (label && wildCard) {
            if (!regionRequired) {
                wildCard.hide();
                if (label.hasClassName('required')) {
                    label.removeClassName('required');
                }
            } else if (regionRequired) {
                wildCard.show();
                if (!label.hasClassName('required')) {
                    label.addClassName('required')
                }
            }
        }

        if (!regionRequired) {
            if (currentElement.hasClassName('required-entry')) {
                currentElement.removeClassName('required-entry');
            }
            if ('select' == currentElement.tagName.toLowerCase() &&
                    currentElement.hasClassName('validate-select')) {
                currentElement.removeClassName('validate-select');
            }
        } else {
            if (!currentElement.hasClassName('required-entry')) {
                currentElement.addClassName('required-entry');
            }
            if ('select' == currentElement.tagName.toLowerCase() &&
                    !currentElement.hasClassName('validate-select')) {
                currentElement.addClassName('validate-select');
            }
        }
    });
}

function _loadCustomer(url, email, carUrl, handle) {
    new Ajax.Request(url, {
        parameters: {email_customer: email},
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result.success) {
                var checkcartUrl = carUrl;
                new Ajax.Request(checkcartUrl, {
                    method: 'post',
                    parameters: '',
                    onFailure: '',
                    onSuccess: function(transport) {
                        if (transport.status == 200) {
                            var stringre = transport.responseText;
                            if (stringre.trim() == 'noItem') {
                            } else {
                                javascript:ajaxcart.addToCartHandle(handle, '');
                            }
                        }
                    }
                });
            }
        }
    });
}	