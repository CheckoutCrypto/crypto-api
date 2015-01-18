<?php
require('config/CoinsAndActions.php');
require('validateaddress.inc');

class ccValidate {
	function getAndValidateAction(){
      if(!empty($_GET['action'])){
			if(preg_match("/^[A-Z]+$/i", $_GET['action'])) {
		        $action = $_GET['action'];
				$a = new ccApi_validate();
				$actions = $a->Config(2);

				foreach($actions as $act){
					if($act == $action){
						return $action;
					}
				}
			}
        }
	}

	function getAndValidateCoin(){
		if(!empty($_GET['coin'])){  //e.g. POT
			if(preg_match("/^[A-Z]+$/i", $_GET['coin'])) {
		      $coin = strtoupper($_GET['coin']); //Always use uppercase
				$a = new ccApi_validate();
				$Coins = $a->getCoin($_GET['coin']);

				if(isset($Coins['coin_code'])){
						return $Coins['coin_code'];
				}
			}
		}
	}

	function getAndValidateTradeCoin(){
		if(!empty($_GET['coin_trade'])){  //e.g. POT
			if(preg_match("/^[A-Z]+$/i", $_GET['coin_trade'])) {
		      $coin = strtoupper($_GET['coin_trade']); //Always use uppercase
				$a = new ccApi_validate();
				$Coins = $a->getCoin($_GET['coin_trade']);

				if(isset($Coins['coin_code'])){
						return $Coins['coin_code'];
				}
			}
		}
	}

	function getAndValidateTwoFactor(){
		if(!empty($_GET['twofa'])){
		 	$twofa = $_GET['twofa'];
		     	if(preg_match("/^[0-9]{1,10}+$/", $twofa)) {
					return $twofa;
				}
		}
	}


	function getAndValidateApi(){
	 	if(!empty($_GET['apikey'])) {
		     	$apikey = $_GET['apikey'];
				if(preg_match("/^[A-Z0-9]+$/i", $apikey)) {
					return $apikey;
				}
		}
	}

	function getAndValidateUserID(){
	 	if(!empty($_GET['uid'])) {
		     	$uid = $_GET['uid'];
		     	if(preg_match("/^[0-9]{1,10}+$/", $uid)) {
					return $uid;
				}
		}
	}

	function getAndValidateGroup(){
	 	if(!empty($_GET['gid'])) {
		     	$gid = $_GET['gid'];
		     	if(preg_match("/^[0-9]{1,10}+$/", $gid)) {
					return $gid;
				}
		}
	}

	function getAndValidateRate(){
      if(!empty($_GET['rate'])) { 
			if(preg_match("/^[A-Z]+$/i", $_GET['rate'])) {
		        if(strtolower($_GET['rate']) == 'usd') {
		            $getrate = 'usd';
					return $getrate;
		        }
			}
      }
	}


	function getAndValidateAddress($coin){
	  	if(!empty($_GET['address'])) {
			$address = $_GET['address'];
			if(preg_match("/^[A-Z0-9]+$/i", $address)) {
					$a = new ccApi_validate();
					$Coins = $a->getCoin($coin);
					if(isset($Coins['coin_valid'])){
						$v = new validateAddress($Coins['coin_valid']);
						$result = $v->checkAddress($address, $Coins['coin_valid']);
						if($result == true){
							return $address;
						}
					}
			 }
		}
	}


	function getAndValidateQueueID(){
        if(!empty($_GET['queueid'])) { 
		    $queueid = $_GET['queueid'];
		    if(preg_match("/^[0-9]{0,}+$/", $queueid)) {
				return $queueid;
		    }
		}
	}

	function getAndValidateIgnoreAmt(){
        if(!empty($_GET['ignore_amt'])) { 
		    $queueid = $_GET['ignore_amt'];
		    if($queueid == "true" || $queueid == "false") {
				return $queueid;
		    }
		}
	}

	function getAndValidateConfirms($maxConfirm){

          if(isset($_GET['confirms'])) {
		    $confirms = $_GET['confirms'];
		    if(preg_match("/^[0-9]{0,}+$/", $confirms) && $confirms <= $maxConfirm) {
				return $confirms;
		    }
		}
	}

	function getAndValidateAmount(){
        if(!empty($_GET['amount'])) {
            $amount = $_GET['amount'];
        	if(preg_match("/^[0-9]{0,}\.[0-9]{1,8}|[0-9]{1,}+$/", $amount)) {
				return $amount;
			}
        }
	}

	function getAndValidateMinMaxAmount($min, $max, $amount){
		if($amount > $min && $amount < $max){
			return true;
		} else{
			return false;
		}
	
	}

	function getAndValidateTrans(){
        if(!empty($_GET['tranid'])) {
            $trans = $_GET['tranid'];
			if(preg_match("/^[A-Z0-9]+$/i", $trans)) {
				return $trans;
			}
        }
	} 

	function getAndValidateType(){

        if(!empty($_GET['type'])) { 
			if(preg_match("/^[A-Z]+$/i", $_GET['type'])) {
		        if(strtolower($_GET['type']) == 'coins' || strtolower($_GET['type']) == 'groups') {
					return $_GET['type'];
		        }
			}
        }
	}

	function getAndValidateAmtType(){

        if(!empty($_GET['amt_type'])) { 
			if(preg_match("/^[A-Z]+$/i", $_GET['amt_type'])) {
		        if(strtolower($_GET['amt_type']) == 'usd' || strtolower($_GET['amt_type']) == 'btc') {
					$amtType = $_GET['amt_type'];

		        }else{
					$a = new ccApi_validate();
					$Coins = $a->getCoin($_GET['amt_type']);
					if(isset($Coins['coin_code'])){
							$amtType = $_GET['amt_type'];
					}
				}
					return $amtType;
        }
		}
	}

	function getMinCoinAmt($coin_trade){
		$min_amount = 0;
		$a = new ccApi_validate();
		$Coins = $a->getCoin($coin_trade);
		if(isset($Coins['coin_code'])){
				$min_amount = $Coins['coin_txfee'];
		}
       return $min_amount;
	}

}

?>
