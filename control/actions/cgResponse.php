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
class cgResponse{
	
	function display($callType, $callEvent, $params){
		$tmpl = array();
		switch($callType){
			case "address":  $tmpl = $this->addressResponse($callEvent, $params);
			break;
			case "receive":  $tmpl = $this->receiveResponse($callEvent, $params);
			break;
			case "balance":  $tmpl = $this->balanceResponse($callEvent, $params);
			break;
			case "transaction": $tmpl = $this->transResponse($callEvent, $params);
			break;
		}
		echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);		
	}

	function addressResponse($callEvent, $params){
		$tmpl = array();
		switch($callEvent){
			/// Success wallet address generated for client deposit
			case "newaddress":
				if(isset($params['address']) && isset($params['status'])){
				     $tmpl['response']['status'] = $params['status'];
				     $tmpl['response']['address'] = $params['address'];
				}
			break;
			/// Successful wallet address generated for trade
			case "newtrade":
				if(isset($params['status']) && isset($params['deposit_coin']) && isset($params['deposit_amt']) 
					&& isset($params['receive_coin']) && isset($params['receive_amt']) && isset($params['address'])){
					$tmpl['response']['status'] = 'success';
					$tmpl['response']['deposit_coin'] = $params['deposit_coin'];			
					$tmpl['response']['deposit_amt'] = $params['deposit_amt'];
					$tmpl['response']['receive_coin'] = $params['receive_coin'];
					$tmpl['response']['receive_amt'] = $params['receive_amt'];
					$tmpl['response']['address'] = $params['address'];
				}
			break;
			/// Hot wallet doesn't contain enough coin for trade
			case "error_insuf":
				$tmpl['response']['status'] = 'error_insuf';
				$tmpl['response']['message'] = 'Their is insufficient funds to complete this transaction';		
			break;
			/// Coin amount does not meet minimum or is larger than maximum
			case "error_min":
				$tmpl['response']['status'] = 'error_min';
				$tmpl['response']['message'] = 'Deposit amount too small';				
			break;
			/// Coin amount does not meat minimum or is larger than maximum
			case "error_max":
				$tmpl['response']['status'] = 'error_max';
				$tmpl['response']['message'] = 'Insufficient funds to complete this trade, check back later';				
			break;
			/// API is offine
			case "error_api":
			    $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Api is offline';
			break;
			/// API Call Validation error
			case "validation": 
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Argument coin could not be validated.';
			break;
			case "disabled": 
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Deposits for this coin have been disabled.';
			break;
			case "pending": 
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Address generation pending, api busy.';
			break;
		}
		return $tmpl;
	}

	function receiveResponse($callEvent,$params){
		$tmpl = array();
		switch($callEvent){
			//// Deposit Completed - once (first time)
			case "complete_once":
				if(isset($params['status']) && isset($params['balance'])){
					 $tmpl['response']['status'] = 'confirmed';
					 $tmpl['response']['pending'] = $params['balance'];
				}
			break;
			//// Trade Completed - already
			case "complete_alrdy":
				if(isset($params['pending']) && isset($params['balance']) && isset($params['fee'])){
					 $tmpl['response']['status'] = 'max_confirmed';
					 $tmpl['response']['balance'] = $params['balance'];
					 $tmpl['response']['pending'] = $params['pending'];
					 $tmpl['response']['fee'] = $params['fee'];
				}
			break;
			/// Getreceived by call pending - possibly an error
			case "pending":
				if(isset($params['status']) && isset($params['queue_id'])){
					$tmpl['response']['status'] = $params['status'];
					$tmpl['response']['queue_id'] = $params['queue_id'];
				}
			break;
			/// Successful wallet address generated for trade
			case "trade_confirm":
					$tmpl['response']['description'] = 'balance has reached expected amount, funds transfer at maxconfirm';
					$tmpl['response']['status'] = 'maxconfirm'; 
			break;
			case "trade_send":
				if(isset($params['address']) && isset($params['txid']) 
					&& isset($params['receive_amt']) && isset($params['receive_coin']) 
					&& isset($params['deposit_amt']) && isset($params['deposit_coin'])){
					$tmpl['response']['description'] = 'balance reached expected amount, confirm is max, funds transfer begins';		
					$tmpl['response']['status'] = 'complete'; 
					$tmpl['response']['address'] = $params['address'];
					$tmpl['response']['txid'] = $params['txid'];
					$tmpl['response']['receive_coin'] = $params['receive_coin'];
					$tmpl['response']['receive_amt'] = $params['receive_amt'];
					$tmpl['response']['deposit_coin'] = $params['deposit_coin'];
					$tmpl['response']['deposit_amt'] = $params['deposit_amt'];
				}
			break;
			case "trade_complete":
					$tmpl['response']['description'] = 'balance has reached MAX confirms already';
					$tmpl['response']['status'] = 'success';
			break;
			case "trade_pending":
				 	$tmpl['response']['status'] = 'pending'; 
					$tmpl['response']['description'] = 'pending deposit';  /// nothing received			
			break;
			/// Getreceived by call pending - possibly an error
			case "disabled":
					$tmpl['response']['status'] = 'disabled';
			break;
			/// Hot wallet doesn't contain enough coin for trade
			case "error_insuf":
			break;
			/// Coin amount does not meet minimum or is larger than maximum
			case "error_min":
				if(isset($params['balance']) && isset($params['receive_coin']) && isset($params['receive_amt'])){
					$tmpl['response']['description'] = 'deposit has NOT reached expected amount, please deposit remaining, you must maxconfirm afterwords';
					$tmpl['response']['status'] = 'incomplete';
					$tmpl['response']['balance'] = $params['balance'];
					$tmpl['response']['expected'] = $params['receive_amt'];
					$tmpl['response']['receive_coin'] = $params['receive_coin'];
				}
			break;
			case "error_max":
				if(isset($params['balance']) && isset($params['receive_coin']) && isset($params['receive_amt'])){
					$tmpl['response']['status'] = 'remittance';
					$tmpl['response']['balance'] = $params['balance'];
					$tmpl['response']['expected'] = $params['receive_amt'];
					$tmpl['response']['receive_coin'] = $params['receive_coin'];
					$tmpl['response']['description'] = 'deposit is OVER expected amount, please contact support for remittance';
				}			
			break;
			/// API is offine
			case "error_api":
			break;
			/// API Call Validation error
			case "validation": 
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = ' Arguments coin, address, amount or confirms could not be validated.';
			break;
		}
		return $tmpl;
	}

	function transResponse($callEvent, $params){
		$tmpl = array();
		switch($callEvent){
			case "newaddress":
			break;
			/// Successful wallet address generated for trade
			case "newtrade":
			break;
			/// Hot wallet doesn't contain enough coin for trade
			case "error_insuf":
			break;
			/// Coin amount does not meet minimum or is larger than maximum
			case "error_min_max":
			break;
			/// API is offine
			case "error_api":
			break;
		}
		return $tmpl;
	}

	function balanceResponse($callEvent, $params){
		$tmpl = array();
		switch($callEvent){
			case "newaddress":
			break;
			/// Successful wallet address generated for trade
			case "newtrade":
			break;
			/// Hot wallet doesn't contain enough coin for trade
			case "error_insuf":
			break;
			/// Coin amount does not meet minimum or is larger than maximum
			case "error_min_max":
			break;
			/// API is offine
			case "error_api":
			break;
		}
		return $tmpl;
	}

}

?>

