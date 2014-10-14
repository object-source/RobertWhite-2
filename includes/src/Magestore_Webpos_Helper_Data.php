<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Webpos Helper
 * 
 * @category    Magestore
 * @package     Magestore_Webpos
 * @author      Magestore Developer
 */
class Magestore_Webpos_Helper_Data extends Mage_Core_Helper_Abstract {

    public function __construct() {
        $this->settings = $this->getConfigData();
    }

    public function getOnePage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function isSignUpNewsletter() {
        if ($this->isCustomerLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if (isset($customer))
                $customerNewsletter = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if (isset($customerNewsletter) && $customerNewsletter->getId() != null && $customerNewsletter->getData('subscriber_status') == 1) {
                return true;
            }
        }
        return false;
    }

    public function getFieldValue() {
        return array(
            '0' => Mage::helper('webpos')->__('Null'),
            'firstname' => Mage::helper('webpos')->__('First Name'),
            'lastname' => Mage::helper('webpos')->__('Last Name'),
            'prefix' => Mage::helper('webpos')->__('Prefix Name'),
            'middlename' => Mage::helper('webpos')->__('Middle Name'),
            'suffix' => Mage::helper('webpos')->__('Suffix Name'),
            'email' => Mage::helper('webpos')->__('Email Address'),
            'company' => Mage::helper('webpos')->__('Company'),
            'street' => Mage::helper('webpos')->__('Address'),
            'country' => Mage::helper('webpos')->__('Country'),
            'region' => Mage::helper('webpos')->__('State/Province'),
            'city' => Mage::helper('webpos')->__('City'),
            'postcode' => Mage::helper('webpos')->__('Zip/Postal Code'),
            'telephone' => Mage::helper('webpos')->__('Telephone'),
            'fax' => Mage::helper('webpos')->__('Fax'),
            'birthday' => Mage::helper('webpos')->__('Date of Birth'),
            'gender' => Mage::helper('webpos')->__('Gender'),
            'taxvat' => Mage::helper('webpos')->__('Tax/VAT number'),
        );
    }

    public function getFieldEnable($number) {
        return Mage::getStoreConfig('webpos/field_position_management/row_' . $number);
    }

    public function getFieldRequire($field) {
        return Mage::getStoreConfig('webpos/field_require_management/' . $field);
    }

    public function getConfigData() {
        $configData = array();
        $configItems = array('general/active', 'general/checkout_title', 'general/checkout_description',
            'general/show_shipping_address', 'general/country_id',
            'general/default_payment', 'general/default_shipping',
            'general/postcode', 'general/region_id', 'general/city',
            'general/use_for_disabled_fields', 'general/hide_shipping_method',
            'general/page_layout',
            'field_management/show_city', 'field_management/show_zipcode',
            'field_management/show_company', 'field_management/show_fax',
            'field_management/show_telephone', 'field_management/show_region',
            'general/show_comment', 'general/show_newsletter',
            'general/show_discount', 'general/newsletter_default_checked',
            'field_management/enable_giftmessage',
            'checkout_mode/show_login_link', 'checkout_mode/enable_registration',
            'checkout_mode/allow_guest', 'checkout_mode/login_link_title',
            'ajax_update/enable_ajax', 'ajax_update/ajax_fields',
            'ajax_update/update_payment',
            'ajax_update/reload_payment',
            'terms_conditions/enable_terms', 'terms_conditions/term_html',
            'terms_conditions/term_width', 'terms_conditions/term_height',
            'order_notification/enable_notification', 'order_notification/notification_email');
        foreach ($configItems as $configItem) {
            $config = explode('/', $configItem);
            $value = $config[1];
            $configData[$value] = Mage::getStoreConfig('webpos/' . $configItem);
        }
        return $configData;
    }

    public function isHideShippingMethod() {
        $_isHide = $this->settings['hide_shipping_method'];
        if ($_isHide) {
            $_quote = $this->getOnepage()->getQuote();
            $rates = $_quote->getShippingAddress()->getShippingRatesCollection();
            $rateCodes = array();
            foreach ($rates as $rate) {
                if (!in_array($rate->getCode(), $rateCodes)) {
                    $rateCodes[] = $rate->getCode();
                }
            }
            if (count($rateCodes) > 1) {
                $_isHide = false;
            }
        }

        return $_isHide;
    }

    public function saveShippingMethod($shippingMethod) {
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $rate = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        $this->getOnepage()->getQuote()->collectTotals()->save();
        return array();
    }

    public function savePaymentMethod($data) {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $onepage = Mage::getSingleton('checkout/session')->getQuote();
        if ($onepage->isVirtual()) {
            $onepage->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $onepage->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }
        $payment = $onepage->getPayment();
        $payment->importData($data);

        $onepage->save();

        return array();
    }

    public function enableTermsAndConditions() {
        return $this->settings['enable_terms'];
    }

    public function getTermsConditionsHtml() {
        return $this->settings['term_html'];
    }

    public function isShowShippingAddress() {
        if ($this->getOnepage()->getQuote()->isVirtual()) {
            return false;
        }
        if ($this->settings['show_shipping_address']) {
            return true;
        }
        return false;
    }

    public function getTermPopupWidth() {
        return $this->settings['term_width'];
    }

    public function getTermPopupHeight() {
        return $this->settings['term_height'];
    }

    public function enableCustomSize() {
        return Mage::getStoreConfig('webpos/terms_conditions/enable_custom_size', $this->getStoreId());
    }

    public function getTermTitle() {
        return Mage::getStoreConfig('webpos/terms_conditions/term_title', $this->getStoreId());
    }

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function showDiscount() {
        return $this->settings['show_discount'];
    }

    public function enableGiftMessage() {
        //return $this->settings['enable_giftmessage'];
//		return Mage::getStoreConfig('sales/gift_options/allow_order');
        $giftMessage = Mage::getStoreConfig('webpos/giftmessage/enable_giftmessage', $this->getStoreId());
        if ($giftMessage) {
            Mage::getConfig()->saveConfig('sales/gift_options/allow_order', 1);
            Mage::getConfig()->saveConfig('sales/gift_options/allow_items', 1);
            return true;
        } else {
            Mage::getConfig()->saveConfig('sales/gift_options/allow_order', 0);
            Mage::getConfig()->saveConfig('sales/gift_options/allow_items', 0);
            return false;
        }
    }

    public function enableOrderComment() {
        return $this->settings['show_comment'];
    }

    public function isShowNewsletter() {
        if ($this->settings['show_newsletter'] && !$this->isSignUpNewsletter())
            return true;
        else
            return false;
    }

    public function isSubscribeByDefault() {
        return $this->settings['newsletter_default_checked'];
    }

    public function enableGiftWrap() {
        return Mage::getStoreConfig('webpos/giftwrap/enable_giftwrap', $this->getStoreId());
    }

    public function enableGiftwrapModule() {
        $moduleGiftwrap = Mage::getConfig()->getModuleConfig('Magestore_Giftwrap')->is('active', 'true');
        return $moduleGiftwrap;
    }

    public function getGiftwrapAmount() {
        return Mage::getStoreConfig('webpos/giftwrap/giftwrap_amount', $this->getStoreId());
    }

    public function getGiftwrapType() {
        return Mage::getStoreConfig('webpos/giftwrap/giftwrap_type', $this->getStoreId());
    }

    public function getOrderGiftwrapAmount() {
        $amount = $this->getGiftwrapAmount();
        $giftwrapAmount = 0;
        // $freeBoxes = 0;
        $items = Mage::getSingleton('checkout/cart')->getItems();
        if ($this->getGiftwrapType() == 1) {
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $giftwrapAmount += $amount * ($item->getQty());
            }
        } elseif (count($items) > 0) {
            $giftwrapAmount = $amount;
        }
        return $giftwrapAmount;
    }

    public function checkGiftwrapSession() {
        $session = Mage::getSingleton('checkout/session');
        return $session->getData('webpos_giftwrap');
    }

    public function getSurveyQuestion() {
        return Mage::getStoreConfig('webpos/survey/survey_question', $this->getStoreId());
    }

    public function getSurveyValues() {
        return Mage::getStoreConfig('webpos/survey/survey_values', $this->getStoreId());
    }

    public function enableFreeText() {
        return Mage::getStoreConfig('webpos/survey/enable_survey_freetext', $this->getStoreId());
    }

    public function enableSurvey() {
        return Mage::getStoreConfig('webpos/survey/enable_survey', $this->getStoreId());
    }

    public function saveOrderComment($observer) {
        $billing = $this->_getRequest()->getPost('billing');
        if ($this->enableOrderComment()) {
            $comment = $billing['onestepcheckout_comment'];
            $comment = trim($comment);
            if ($comment != '') {
                $order = $observer->getEvent()->getOrder();
                try {
                    $order->setWebposOrderComment($comment);
                } catch (Exception $e) {
                    
                }
            }
        }
        if ($this->enableSurvey()) {
            $surveyQuestion = $this->getSurveyQuestion();
            $surveyValues = unserialize($this->getSurveyValues());
            $surveyValue = $billing['onestepcheckout-survey'];
            $surveyFreeText = $billing['onestepcheckout-survey-freetext'];
            if (!empty($surveyValue)) {
                if ($surveyValue != 'freetext') {
                    $surveyAnswer = $surveyValues[$surveyValue]['value'];
                } else {
                    $surveyAnswer = $surveyFreeText;
                }
            }

            $order = $observer->getEvent()->getOrder();
            $session = Mage::getSingleton('checkout/session');
            if ($surveyQuestion)
                $session->setData('survey_question', $surveyQuestion);
            if ($surveyAnswer)
                $session->setData('survey_answer', $surveyAnswer);
        }
    }

    /**
     * Hai.Ta 28.5.2013
     * */
    public function getUrlSetCustomerToQuote() {
        return $this->_getUrl('webpos/index/loadCustomerToQuote', array('_secure' => true, 'auth' => 1));
    }

    /**
     * Hai.Ta 28.5.2013
     * */
    public function getConfigCheckEmail() {
        return (int) Mage::getStoreConfig('webpos/ajax_update/check_email', $this->getStoreId());
    }

    /**
     * Hai.Ta 28.5.2013
     * */
    public function getConfigShowNotice() {
        return (int) Mage::getStoreConfig('webpos/ajax_update/show_popup', $this->getStoreId());
    }

    /**
     * Intergrated RewardPoints 
     */
    public function getActiveRewardPoints() {
        if (Mage::getConfig()->getModuleConfig('Magestore_RewardPoints')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/general/enable') && Mage::getStoreConfig('webpos/rewardpoints/enable_rewardpoints')) {
            return true;
        } else
            return false;
    }

    public function getActiveRewardPointsRule() {
        if ($this->getActiveRewardPoints() && Mage::getConfig()->getModuleConfig('Magestore_RewardPointsRule')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/rewardpointsrule/enable')) {
            return true;
        } else
            return false;
    }

    public function showEarningPointsCart() {
        if (Mage::getConfig()->getModuleConfig('Magestore_RewardPoints')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/general/enable') && Mage::getConfig()->getModuleConfig('Magestore_RewardPointsRule')->is('active', 'true') && Mage::getStoreConfig('rewardpoints/rewardpointsrule/enable')) {
            return true;
        } else
            return false;
    }
    
    public function getBackgroundColor($style){
        if($style == 'orange')
            return '#EE9600';
        if($style == 'green')
            return '#539222';
        if($style == 'black')
            return '#363636';
        if($style == 'blue')
            return '#417290';
        if($style == 'darkblue')
            return '#094171';
        if($style == 'pink')
            return '#EA4A72';
        if($style == 'red')
            return '#BA200F';
        if($style == 'violet')
            return '#C246AE';
        
        return '#EE9600'; 
    }

}
