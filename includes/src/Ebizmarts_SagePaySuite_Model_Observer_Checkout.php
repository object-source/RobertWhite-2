<?php

/**
 * Checkout events observer
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_SagePaySuite
 * @author     Ebizmarts <info@ebizmarts.com>
 */
class Ebizmarts_SagePaySuite_Model_Observer_Checkout extends Ebizmarts_SagePaySuite_Model_Observer {

    protected function _getLastOrderId() {
        return (int) (Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId());
    }

    /**
     * Save Magemaven Order Comments
     * @param  $observer
     * @return Ebizmarts_SagePaySuite_Model_Observer_Checkout
     */
    public function saveMagemavenOrderComment($observer) {

        //Magemaven_OrderComment
        $comment = $this->getSession()->getOrderComments(true);
        if($comment) {

            $order = $observer->getEvent()->getOrder();

            if(is_object($order)) {
                $order->setCustomerComment($comment);
                $order->setCustomerNoteNotify(true);
                $order->setCustomerNote($comment);
            }
        }
        //Magemaven_OrderComment

        return $this;
    }

    /**
     * Clear SagePaySuite session when loading onepage checkout
     */
    public function controllerOnePageClear($o) {

        /**
         * Delete register and guest cards when loading checkout
         */
        try {
            $sessionCards = Mage::helper('sagepaysuite/token')->getSessionTokens();
            if ($sessionCards->getSize() > 0) {
                foreach ($sessionCards as $_c) {
                    if ($_c->getCustomerId() == 0) {

                        $delete = Mage::getModel('sagepaysuite/sagePayToken')
                                ->removeCard($_c->getToken(), $_c->getProtocol());
                        if ($delete['Status'] == 'OK') {
                            $_c->delete();
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
        /**
         * Delete register and guest cards when loading checkout
         */
        Mage::getSingleton('core/session')->unsetData('surchargeamount');
        Mage::getSingleton('core/session')->unsetData('surchargeamounttax');

        $this->getSession()->clear();
    }

    public function controllerMultishippingClear($o) {
        Mage::getSingleton('core/session')->unsetData('surchargeamount');
        Mage::getSingleton('core/session')->unsetData('surchargeamounttax');
        $this->getSession()->clear();
    }

    /**
     * Capture DEFERRED or AUTHENTICATE transactions
     */
    public function deferredCapture($o) {
        $canCapture = (bool) Mage::getStoreConfig('payment/sagepaysuite/deferred_capture');
        if ($canCapture) {

            $orderId = $this->_getLastOrderId();

            $trn = $this->_getTransactionsModel()
                    ->loadByParent($orderId);
            if ($trn->getId()) {
                if ($trn->getTxType() == 'DEFERRED' || $trn->getTxType() == 'AUTHENTICATE') {
                    Mage::getModel('sagepaysuite/api_payment')->invoiceOrder($orderId, Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                }
            }
        }
    }

    public function controllerOnePageSuccess($o) {

        //Capture data from Sage Pay API
        $orderId = $this->_getLastOrderId();

        $this->_getTransactionsModel()->addApiDetails($orderId);

        /**
         * Delete session tokencards if any
         */
        $vdata = Mage::getSingleton('core/session')->getVisitorData();

        $sessionCards = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->getCollection()
                ->addFieldToFilter('visitor_session_id', (string) $vdata['session_id']);

        if ($sessionCards->getSize() > 0) {
            foreach ($sessionCards as $_c) {
                if ($_c->getCustomerId() == 0) {
                    $_c->delete();
                }
            }
        }

        //Associate Customer ID for DIRECT transactions without 3D and REGISTER checkout
        $tokenId = $this->getSession()->getLastSavedTokenccid(true);
        if((int)$tokenId) {
            $token = Mage::getModel('sagepaysuite2/sagepaysuite_tokencard')->load($tokenId);
            if($token->getId() && ($token->getId() == $tokenId) && !$token->getCustomerId()) {

                $customerId = Mage::getModel('sales/order')->load($orderId)->getCustomerId();

                $token->setCustomerId($customerId)
                        ->save();
            }
        }

        /**
         * Delete session tokencards if any
         */
        $this->getSession()->clear();
    }

    public function sendPaymentFailedEmail($observer) {
        //Check if enabled in config.
        if(0 === (int)Mage::getStoreConfig('payment/sagepaysuite/send_payment_failed_emails')) {
            return $this;
        }

        $quote   = $observer->getEvent()->getQuote();
        $message = $observer->getEvent()->getMessage();

        try {

            Mage::helper('sagepaysuite/checkout')->sendPaymentFailedEmail($quote, $message);

        } catch(Exception $ex) {
            Sage_Log::logException($ex);
        }

        return $this;
    }
}