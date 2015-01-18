<?php
require('cgResponse.php');

class ccTradeReceive {


	function getReceived($masterCntrl, $action, $apikey, $l, $v, $a){
		$thread = "trade";
		$tmpl = array();
		$coin = $v->getAndValidateCoin();
		$address = $v->getAndValidateAddress($coin);
		$origKey = $apikey;
		$respDisp = new cgResponse();

		if(isset($coin) && isset($address)){
			$maxConfirm = $a->getCoinMaxConfirm($coin);
			$confirms = $v->getAndValidateConfirms($maxConfirm);
			if(isset($confirms)){
			/// check if wallet has been confirmed already (we don't want that added twice) 
			/// reminder make sure cron doesn't confirm an address that hasn't reached the exact amount if we're doing a spend trade
			$walletConfirmed = $a->checkReceivedByAddress($address, $coin);
			$db = $this->dbConnect();
			$tradeStatus = $db->getTradeStatus($address);
				if(isset($walletConfirmed)){

					if($walletConfirmed == false || $tradeStatus < 2){

						$apikey = $a->getExchangeWallet();
						if(isset($apikey)){

							$uid = $a->getApiUserID($apikey);
							$gid = $a->getGrpID($uid);
							$amount = 0;
							$orderid = $a->getReceivedByAddress($uid, $gid, $address, $coin, $amount, $confirms, $thread);
							// update trade cache table

							/// $db->updateStatus($origKey, $address, $orderid);
							//// REMIND Check if trade was completed previously first.
							sleep(1);
				         $result = $a->getAddressBalanceQuery($orderid, $apikey, $address);
							
							/// make sure theres a result at all first, we just called getreceived to worker!

							if(isset($result)){
							// get the trade - confirm amounts and flag for ignoring amounts							
								$trade = $db->getTrade($origKey, $address);
								$walletConfirmed = $a->checkReceivedByAddress($address, $coin);  // what did it last confirm at?

								if(floatval($result['balance']) >= floatval($trade['amount_from']) && $result['balance'] != "0"){
									$db->updateReceived($origKey, $result['balance'], $address, 1);
								}
								/// amount traded IS what expected, flag expects full amount
								if(floatval($result['balance']) >= floatval($trade['amount_from'])  || $trade['ignore_amt'] == "true"){
									$walletConfirmed = $a->checkReceivedByAddress($address, $trade['coin_from']);  // what did it last confirm at?
									$balance = $a->getWalletBalance($address, $trade['coin_from']);
									$maxConfirm = $a->getCoinMaxConfirm($trade['coin_from']);

									if($walletConfirmed >= $maxConfirm && $walletConfirmed != "false" && $balance['balance'] > 0){
										$MinCoinTrade = $v->getMinCoinAmt($trade['coin_to']);
										$txid = $this->sendTrade($a, $origKey, $apikey, $balance, $trade, $MinCoinTrade);

										if($txid != NULL){
											$respDisp->display("receive", "trade_send", array('address' =>$trade['address_out'], 
												'txid' => $txid,
												'balance' =>floatval($balance['balance']),
												'pending' => floatval($balance['pending']),
												'receive_amt' => floatval($trade['amount_to']),
												'receive_coin' => $trade['coin_to'],
												'deposit_amt' => floatval($trade['amount_from']),
												'deposit_coin' => $trade['coin_from']));
										}else{
											$respDisp->display("receive", "error_api", array());
										}
										if(floatval($result['balance']) > floatval($trade['amount_from'])  && $trade['ignore_amt'] == "false"){
											$respDisp->display("receive", "error_max", 
												array('receive_amt'=> floatval($trade['amount_from']),
												'receive_coin' => $trade['coin_from'],
												'balance' => floatval($result['balance'])));
										}
								//////  IF CALL CONFIRM IS LESS THAN MAX CONFIRM, BUT STILL CONTAINS A BALANCE
									}else if($walletConfirmed < $maxConfirm && $balance['pending'] > 0){   //// confirms is less than max
									/// IF WE"RE REQUIRING THE EXACT AMOUNT FOR THE DEPOSIT AND THE TRADE IS GREATER THAN THE AMOUNT NEEDED
										if(floatval($result['balance']) > floatval($trade['amount_to'])  && $trade['ignore_amt'] == "false"){
											$respDisp->display("receive", "error_max", array('receive_amt'=> floatval($trade['amount_from']),
												'receive_coin' => $trade['coin_from'], 'balance' => $result['balance'],
												'receive_amt' => floatval($trade['amount_to']),
												'receive_coin' => $trade['coin_to'],
												'deposit_amt' => floatval($trade['amount_from']),
												'deposit_coin' => $trade['coin_from']));
										/// IF THE AMOUNT REQUIRED DOES NOT MATTER
										}else{
                           		$respDisp->display("receive", "trade_confirm", array());
										}
									}else if($walletConfirmed > $maxConfirm) {   //// confirms is less than max {
											$respDisp->display("receive", "trade_complete", array());
									}else if($walletConfirmed <= $maxConfirm && floatval($result['balance']) > 0){
										$respDisp->display("receive", "trade_complete", array());
									}else{
										$respDisp->display("receive", "trade_pending", array());
									}
									/// amount traded is LESS than we expected, flag expects full amount, insufficient fund exception
							 	}else if(floatval($result['balance']) < floatval($trade['amount_from'])  && $trade['ignore_amt'] == "false"){
									$respDisp->display("receive", "error_min", 
										array('receive_amt'=> floatval($trade['amount_from']),
										'receive_coin' => $trade['coin_from'], 
										'balance' => floatval($result['balance'])));
								/// amount traded is MORE than we expected, flag expects full amount, remittance exception
								}else{
									$tmpl['response']['status'] = $response['status'];
									$tmpl['response']['balance'] = floatval($balance['balance']);
									$tmpl['response']['pending'] = floatval($balance['pending']);
									echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
								}
							}
						}else{
							$l->ccLog('gettradereceived: disabled');
                           				$respDisp->display("receive", "disabled", array());
						}
					}else{
                           				$respDisp->display("receive", "disabled", array());
				 	} 
				}
		    	 }
                 } else {
                   $l->ccLog('getreceivedbyaddress: Arguments coin, address, amount or confirms could not be validated');
                   $respDisp->display("receive", "validation", array());
                }
	}

	function sendTrade($a, $origKey, $apikey, $balance, $trade, $minAmt){
		$thread = "trade";
		$db = $this->dbConnect();
		/// calc the amount of coin to exchange, subtract our fee.
		$total_exchange = $this->ConvertAmount($db, $trade['gid'], $balance['balance'],  $trade['coin_from'], $trade['coin_to'], $trade['coin_from'], $minAmt);
		// ensure wallet balance is enough , double check balance > min_amount we can send!
		$hotWalletBal = $db->getHotBalance($apikey, $trade['coin_to']);
		$args = array();
		$args['account'] = $a->getUserWalletByID($apikey);
		$args['amount'] = round($total_exchange['amountRec'], 8, PHP_ROUND_HALF_UP);  // set to exchange calc amount
		$args['coin'] = $trade['coin_to'];
		$args['recip'] = $trade['address_out'];
		$args['gid'] = $trade['gid'];
		$txid = '';
		if($trade['status'] == 1 && $hotWalletBal > $args['amount'] && $args['amount'] > $minAmt){									
			$exchange = $a->addWorkOrderQuery($apikey, 'sendto', $args, true, $thread); //checkif order exists
			/// if success, get txid,
			if(isset($exchange)){
				sleep(1);
				$txid = $db->getTransIDByReceiver($trade['address_out'], $args['amount']);
				if(!empty($txid)){
					$db->transComplete($origKey, $txid, $args['amount'], $trade['address_gen'], 2);
					return $txid;
				}else{
						/// problem getting transaction id, trade possibly never sen
				}					
			} else{
					//// trade was never completed
			}
		}

	}

        function ConvertAmount($db, $gid, $amount, $amount_type, $coin_to, $coin_from, $tradetxfee){
                $conversion = array();
                $cnTO =  $db->getSpecificCoinRate($coin_to);
                $cnFROM =  $db->getSpecificCoinRate($coin_from);
                $tradeFee = $db->getTradeFee($gid);

                if(strtoupper($amount_type) == "USD"){
                        $cnBTC =  $db->getSpecificCoinRate('BTC');
                        // convert USD to coin - deposit
                        $amountDep = ($amount / $cnBTC['coin_rate_buy']) / $cnFROM['coin_rate_buy'];
                        /// convert amountRec $coin_to - received
                        if($coin_to == "BTC"){
                                $amountRec = ($amount / $cnBTC['coin_rate_sell']);  // without fee
                        }else{
                                $amountRec = ($amount / $cnBTC['coin_rate_sell']) / $cnTO['coin_rate_sell'];
                        }
                }else if(strtoupper($amount_type) == "BTC"){
                        /// convert btc to desired coin - deposit
                        $amountDep = $amount / $cnFROM['coin_rate_buy'];
                        /// convert amountRec $coin_to - received
                        if($coin_to == "BTC"){
                                $amountRec = $amount;
                        }else{
                                $amountRec = $amount / $cnTO['coin_rate_buy'];
                        }
                }else{
                        /// accept the amount as the natural amount - deposit
                        $amountDep = $amount;
                        /// convert amountRec $coin_to - received
                        if($coin_to == "BTC"){
                                $amountRec = $amount * $cnFROM['coin_rate_buy']; 
                        }else{
                                $amountRec = ($amount * $cnFROM['coin_rate_buy']) / $cnTO['coin_rate_buy'];
                        } 
                }

                        /// add fee
                        $fee = $amountDep * ($tradeFee/100);
                        $amountDep = $amountDep + $fee + $tradetxfee;

                $conversion['amountDep'] = round($amountDep, 8, PHP_ROUND_HALF_UP);
                $conversion['amountRec'] = round($amountRec, 8, PHP_ROUND_HALF_UP);
                // $conversion['amountFee'] = $fee;

                return $conversion;
        }

	function calcExchangeBTC($db, $uid, $gid, $amount, $coin_to, $coin_from){
		if($coin_to == "BTC"){
			$rate_to = $db->getSpecificCoinRate($coin_to);
			$rate_coin_to = $rate_to['coin_rate'];
		}else{
			$rate_to = $db->getSpecificCoinRate($coin_to);
			$rate_coin_to = $rate_to['coin_rate_btc'];
		}
		$rate_from = $db->getSpecificCoinRate($coin_from);
		$rate_coin_from = $rate_to['coin_rate_btc'];

		$deposit_total_btc = $amount * $rate_coin_from;
		$grp_fee = $db->getTradeFee($gid);
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
