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
require('cgResponse.php');

class ccTradeAddress {
	
	function getTradeAddress($masterCntrl, $action, $apikey, $l, $v, $a){
		$thread = "trade";
		$respDisp = new cgResponse();
		$tmpl = array();
		if($masterCntrl['disable_getnewaddress'] == 0){
			$coin = $v->getAndValidateCoin();
			$tradecoin = $v->getAndValidateTradeCoin();
			$amount = $v->getAndValidateAmount();
			$amtType = $v->getAndValidateAmtType();
			$rec_address = $v->getAndValidateAddress($tradecoin);
			$ignore = $v->getAndValidateIgnoreAmt();						
			$origKey = $apikey; 
			if(isset($coin) && isset($tradecoin) && isset($amount) && isset($rec_address) && isset($ignore) && isset($amtType)) { 
				$apikey = $a->getExchangeWallet();
				if(isset($apikey)){
					$uid = $a->getApiUserID($apikey);
					$gid = $a->getGrpID($uid);
					$action = "getnewaddress";
					$args['coin'] = $coin;
					$args['gid'] = $gid;
					$db = $this->dbConnect();
					$MinCoinTrade = $v->getMinCoinAmt($tradecoin);
					///  based on amount type (USD, BTC, anycoin), calculate amount to deposit with fee return amount in deposit coin
					$conversion = $this->ConvertAmount($db, $gid, $amount, $amtType, $tradecoin, $coin, $MinCoinTrade);
					$hotWalletBal = $db->getHotBalance($apikey, $tradecoin);
					if(floatval($conversion['amountRec']) > floatval($MinCoinTrade)){
						if(floatval($hotWalletBal) >= floatval($conversion['amountRec'])){
							$result = $a->addWorkOrderQuery($apikey, $action, $args, true, $thread);
							if($result){
								$db->createTrade($origKey, $coin, $tradecoin, $conversion['amountDep'], $conversion['amountRec'], $rec_address, $ignore, $result);
								sleep(1);
			 					$address = $a->getGeneratedWalletQuery($result, $apikey, $coin);
								if($address != null){
									$addressID = $db->addTradeAddress($origKey, $address, $result);
									/// retrieve deposit amt/coin
									 $respDisp->display("address", "newtrade", 
									array('status' => 'success', 
									'deposit_coin' => $coin,
									'deposit_amt' => $conversion['amountDep'],
									'receive_coin' => $tradecoin, 
									'receive_amt' => $conversion['amountRec'],
									'address' => $address));
								}else{
				                    			$respDisp->display("address", "pending", array());
								}
							}else{
								$l->ccLog('getnewaddress: api temporarily offline');
				                    		$respDisp->display("address", "error_api", array());
							}
						}else{
								$l->ccLog('getnewaddress: Insufficient funds to complete this transaction');
				               	$respDisp->display("address", "error_insuf", array());
						}
					}else{
						$l->ccLog('getnewaddress: No apikey or not enough funds');
				               	$respDisp->display("address", "error_min", array());
					}
				}else{
					$l->ccLog('getnewaddress: No apikey or not enough funds');
                           		$respDisp->display("address", "disabled", array());
			     }
                        } else {
                            $l->ccLog('getnewaddress: coin could not be validated');
                            $respDisp->display("address", "validation", array());
                        }
                    } else {
                        $l->ccLog('getnewaddress: getnewaddress is disabled');
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


    function dbConnect() {
        if(!(isset($db))) {
            include_once('ccDBtrade.php');
            $db = new ccTrade();
        }
        return $db;
    }


}
