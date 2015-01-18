<?php
/*
Copyright 2014-2015 Grant Hutchinson

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

One exception, this software is not allowed to be used by Simon Choucroun or any assosciate or affiliate individual/company */
class ccStatus {

	function getStatus($masterCntrl, $action, $apikey, $l, $v, $a){
				$tmpl = array();
				$queueid = $v->getAndValidateQueueID();
				    if(isset($queueid)) {
				        $response = $a->workOrderStatusQuery($apikey, $queueid); //checkif order exists
				        if($response['status'] == 'success') {
				            switch($response['action']) {
				                case 'getnewaddress':
				                    $result = $a->getGeneratedWalletQuery($queueid, $apikey);
				                    $tmpl['response']['status'] = $response['status'];
				                    $tmpl['response']['address'] = $result;
				                    break;
				                case 'getreceivedbyaddress':
                                    $address = $a->getGeneratedWalletQuery($queueid, $apikey);
                                    $result = $a->getAddressBalanceQuery($queueid, $apikey, $address);
				                    $tmpl['response']['status'] = $response['status'];
				                    $tmpl['response']['balance'] = $result['balance'];
				                    break;
				                case 'sendfrom':
				                    $address = $a->getGeneratedWalletQuery($queueid, $apikey);
				                    $tmpl['response']['status'] = $response['status'];
				                    break;
				                default:
				                    $tmpl['response']['status'] =  $response['status'];
				            }
				        } elseif ($response['status'] == 'pending') {
				            $tmpl['response']['status'] = 'pending';
				        } else {
				         $tmpl['response']['status'] = 'failure';
                         $tmpl['response']['message'] = 'No order found with specified queue id';
                         $l->ccLog('getstatus: Order not found');
				        }
                    } else {
                        $l->ccLog('getstatus: queueid could not be validated');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
	}

	function getTradeStatus($masterCntrl, $action, $apikey, $l, $v, $a){
				$tmpl = array();
				$origKey = $apikey;
				$queueid = $v->getAndValidateQueueID();
				    if(isset($queueid)) {
						$apikey = $a->getExchangeWallet();
				        $response = $a->workOrderStatusQuery($apikey, $queueid); //checkif order exists
				        if($response['status'] == 'success') {
				            switch($response['action']) {
				                case 'getnewaddress':
				                    $result = $a->getGeneratedWalletQuery($queueid, $apikey);
					                $db = $this->dbConnect();
									$addressID = $db->addTradeAddress($origKey, $result, $queueid);
				                    $tmpl['response']['status'] = $response['status'];
				                    $tmpl['response']['address'] = $result;
				                    break;
				                case 'getreceivedbyaddress':

                                    $address = $a->getGeneratedWalletQuery($queueid, $apikey);
                                    $result = $a->getAddressBalanceQuery($queueid, $apikey, $address);

									$db = $this->dbConnect();
									$db->updateReceived($origKey, $result['balance'], $queueid, '0');
									// get the trade - confirm amounts and flag for ignoring amounts									
									$trade = $db->getTrade($origKey, $address);

									/// amount traded IS what expected, flag expects full amount
									if($trade['amount_to'] == $trade['amount_from'] || $trade['ignore_amt'] == "true"){

										$walletConfirmed = $a->checkReceivedByAddress($address, $trade['coin_from']);  // what did it last confirm at? we don't want to double confirm it
		    							$balance = $a->getWalletBalance($address, $trade['coin_from']);
										$maxConfirm = $a->getCoinMaxConfirm($trade['coin_from']);
										if($walletConfirmed == $maxConfirm && $walletConfirmed != "false"){
											$tmpl['response']['description'] = 'balance has reached expected amount, confirm is max, funds transfer begins';
											$this->sendTrade($a, $origKey, $apikey, $balance, $trade);
											//$result = $a->addWorkOrderQuery($apikey, 'getreceived', $args, false);
										}else{
											$tmpl['response']['description'] = 'balance has reached expected amount, funds transfer at maxconfirm';
										}								
										$tmpl['response']['status'] = $response['status'];

									/// amount traded is LESS than we expected, flag expects full amount, insufficient fund exception
									}else if($trade['amount_to'] < $trade['amount_from'] && $trade['ignore_amt'] == "false"){
										$tmpl['response']['description'] = 'balance has NOT reached expected amount, please deposit before maxconfirm';
										$tmpl['response']['status'] = 'incomplete';
									/// amount traded is MORE than we expected, flag expects full amount, remittance exception
									}else if($trade['amount_to'] > $trade['amount_from'] && $trade['ignore_amt'] == "false"){
										$tmpl['response']['description'] = 'balance is OVER reached expected amount, please contact support for remittance';
										$tmpl['response']['status'] = 'remittance';
									}else{
										$tmpl['response']['status'] = $response['status'];
									}
				                    $tmpl['response']['balance'] = $result['balance'];
									$tmpl['response']['pending'] = $balance['pending'];
									$tmpl['response']['fee'] = $balance['fee'];
				                    break;
				                case 'sendfrom':
				                    $address = $a->getGeneratedWalletQuery($queueid, $apikey);
									//$db = $this->dbConnect();
									//$db->transComplete();
				                    $tmpl['response']['status'] = $response['status'];
				                    break;
				                default:
				                    $tmpl['response']['status'] =  $response['status'];
				            }
				        } elseif ($response['status'] == 'pending') {
				            $tmpl['response']['status'] = 'pending';
				        } else {
				         $tmpl['response']['status'] = 'failure';
                         $tmpl['response']['message'] = 'No order found with specified queue id';
                         $l->ccLog('getstatus: Order not found');
				        }
                    } else {
                        $l->ccLog('getstatus: queueid could not be validated');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
	}

		function sendTrade($a, $origKey, $apikey, $balance, $trade){
			$db = $this->dbConnect();
			/// calc the amount of coin to exchange, subtract our fee.

			$total_exchange = $this->calcExchangeBTC($db, $trade['uid'], $trade['gid'], $balance['balance'], $trade['coin_to'], $trade['coin_from']);

			$args = array();
			$args['account'] = $a->getUserWalletByID($apikey);
			$args['amount'] = $total_exchange;  // set to exchange calc amount
			$args['coin'] = $trade['coin_to'];
			$args['recip'] = $trade['address_out'];
			$args['gid'] = $trade['gid'];


				if($trade['status'] != 1){
					var_dump('the hot wallet sent the following coin: '.$args['coin'] . ' and the amount: '. $args['amount']);										
					$exchange = $a->addWorkOrderQuery($apikey, 'sendto', $args, true); //checkif order exists
					var_dump('final '.$exchange);	
					/// if success, get txid,
					// $db->transComplete($origKey, $txid, $trade['amount_to'], $trade['address_out'], $queueid, $status);
					/// else display error, remittance
					
			}

		}


		function calcExchangeBTC($db, $uid, $gid, $amount, $coin_to, $coin_from){
			if($coin_to == "BTC"){
				$rate_coin_to = $db->getUSDRate($coin_to);
			}else{
				$rate_coin_to = $db->getBtcRate($coin_to);
			}
			$rate_coin_from = $db->getBtcRate($coin_from);

			$deposit_total_btc = $amount * $rate_coin_from;
			$grp_fee = $db->getTradeFeeForGroup($gid);
			$fee = ( $grp_fee / 100) * $deposit_total_btc;  /// reminder to add trade fee to group
			
			$total = $deposit_total_btc - $fee;

			return $total;
		}

    function dbConnect() {
        if(!(isset($db))) {
            include_once('ccDBtrade.php');
            $db = new ccTrade();
        }
        return $db;
    }
}

?>
