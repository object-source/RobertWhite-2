<?php

class Ebizmarts_SagePayReporting_Model_Cron {

	/**
	 * Retrieve fraud score (3rd man) for transactions that do not have score.
	 * @param  $cron Cron object
	 * @return Ebizmarts_SagePayReporting_Model_Cron
	 */
	public function getThirdmanScores($cron) {

		$fraudTblName = Mage::getSingleton('core/resource')->getTableName('sagepayreporting_fraud');
		$transactions = Mage::getResourceModel('sagepaysuite2/sagepaysuite_transaction_collection');
		$transactions->addFieldToSelect(array('order_id', 'vendor_tx_code', 'vps_tx_id'));

		$transactions
		->getSelect()
		->where("`main_table`.`order_id` IS NOT NULL AND (`main_table`.`order_id` NOT IN (SELECT `order_id` FROM ". $fraudTblName ."))")
		->order("main_table.created_at DESC")
		->limit(15);

		$now = strtotime("now");

		foreach($transactions as $_trn) {

			$update = $_trn->updateFromApi();

			if (!$update->getFraud()) {
				continue;
			}

			try {

				$rs             = $update->getFraud();
				$noresult       = ((string)$rs->getThirdmanAction() == 'NORESULT');
				$orderPusOneDay = strtotime("+1 day", strtotime($_trn->getCreatedAt()));

				if(!$noresult || ($now > $orderPusOneDay)) {

					/**
					 *  Automatic fulfill
					 */
					$canAuthorise = ($_trn->getTxType() == 'AUTHENTICATE' && !$_trn->getAuthorised());
					$canRelease   = ($_trn->getTxType() == 'DEFERRED' && !$_trn->getReleased());
					$rank         = ( $this->_getCanRank() && ($this->_getRank() <= (int)$rs->getThirdmanScore()) );

					if(($canAuthorise || $canRelease) && $rank){
						Mage::getModel('sagepaysuite/api_payment')->invoiceOrder($_trn->getOrderId(), Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
					}
					/**
					 *  Automatic fulfill
					 */

				}

			} catch (Exception $e) {
				Sage_Log::logException($e);
			}

		}

	}

	protected function _getCanRank()
	{
		return Mage::getStoreConfigFlag('payment/sagepaysuite/auto_fulfill_low_risk_trn');
	}

	protected function _getRank()
	{
		return (int)Mage::getStoreConfig('payment/sagepaysuite/auto_fulfill_low_risk_trn_value');
	}

}