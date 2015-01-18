<?php
require('./control/2fa/GoogleAuthenticator/PHPGangsta/GoogleAuthenticator.php');
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
Class ccApi {
    function dbConnect() {
        if(!(isset($db))) {
            include_once('database.php');
            $db = new ccDb();
        }
        return $db;
    }

    function getRates($coin) {
        if(isset($coin)) {
            $db = $this->dbConnect();
            $rates = $db->getRates($coin);
            return $rates;
        }
    }

    function workOrderStatusQuery($queueid, $apikey) {
        $db = $this->dbConnect();
        $order = $db->getWorkOrder($queueid, $apikey);
        $result = $order;

        if($order['status'] == 1) { //set status to human readable value
            $result['status'] = 'success';
        } elseif ($order['status'] == 0) {
            $result['status'] = 'pending';
        }
        if(isset($result)) {
            return $result;
        }
        return false; //order not found
    }

    function getGeneratedWalletQuery($queueid, $apikey, $coin) {
        $db = $this->dbConnect();
        $result = $db->getGeneratedWallet($queueid, $apikey, $coin);
        if(isset($result)) {
            //do some checking if address has correct format maybe
            return $result;
        }
        return false;
    }

    function getAddressBalanceQuery($queueid, $apikey,$address) {
        $db = $this->dbConnect();
        $result = $db->getAddressBalance($queueid, $apikey, $address);
        if(isset($result)) {
            return $result;
        }
        return false;
    }

    function checkWorkerStatus() {
        $db = $this->dbConnect();
        $status = $db->getWorkerStatus();

        if(intval($status) === 1) { //mysql returns as string, convert to int
            $result = true;
        } else {
            $result = false;
        }

        if(isset($result)) {
            return $result;
        }
        return false; //general error TODO
    }

      function notifyWorker($orderNumber, $thread) {
        $prod = TRUE;
        if(isset($orderNumber) AND ($prod === TRUE)) {
            include_once('./config/ccapiconfig.php');
            $ccApiConf = new ccApiConfig();

            $orderNumber = intval($orderNumber);
            try {
                $host= $ccApiConf->ccApiWorkerServer();
				$port = $ccApiConf->ccApiWorkerPort();
                $orderNumber = $ccApiConf->ccApiWorkerKey() . '-'.$thread;
                $bytelength = strlen($orderNumber);

                $fp = fsockopen($host, $port, $bytelength);
                if($fp) {
                    socket_set_blocking( $fp, false );
                    fwrite($fp, $orderNumber);
                }
            } catch (exception $e) {
               // echo $e;
            }
        }
        return false; //general error or orderNumber not set
    }

	function notifyWorkerCache($msg = NULL) {
		     $prod = TRUE;

             if(isset($msg) AND ($prod === true)) {
                 include_once('./config/ccapiconfig.php');
                 $ccApiConf = new ccApiConfig();
		         try {
		             $host= $ccApiConf->ccApiWorkerServer();
		             $port = $ccApiConf->ccApiWorkerPort();
		             $apiKey = $ccApiConf->ccApiWorkerKey();
		             $msg = $apiKey."-".$msg;
		             $bytelength = strlen($msg);
		             $fp = fsockopen($host, $port, $bytelength);
		             if($fp) {
		                 socket_set_blocking( $fp, false );
		                 fwrite($fp, $msg);
		             }
		         } catch (exception $e) {
		         //    echo $e;
		         }
		         return false;
		     }
		     return false; //general error or msg not set
	}

    function addWorkOrderQuery($apikey, $action, $args, $notify, $thread) {

       switch ($action) {
         case 'getnewaddress':  /// work
            $db = $this->dbConnect();
            $coin = $args['coin'];
			$gid = $args['gid'];
            $result = $db->addWorkOrder($apikey, $coin, $gid, $thread);
            break;
         case 'getbalance':  /// cron
            $db = $this->dbConnect();
            $address = $args['address'];
            $userid = $args['uid'];
            $coin = $args['coin'];
            $result = $db->addWorkOrderBalance($userid, $address, $coin, $thread);
            break;
       case 'getreceived':  /// work
            $db = $this->dbConnect();
            $userid = $args['uid'];
            $gid = $args['gid'];
            $address = $args['address'];
            $amount = $args['amount'];
            $coin = $args['coin'];
      		 $confirm = $args['confirm'];
            $result = $db->addWorkOrderReceived($userid, $gid, $address, $amount, $coin, $confirm, $thread);
            break;
         case 'sendto':  /// work
            $db = $this->dbConnect();
            $account = $args['account'];
            $amount = $args['amount'];
            $coin = $args['coin'];
            $sendto = $args['recip'];
            $gid = $args['gid'];
            $result = $db->addWorkOrderSend($gid, $apikey, $account, $amount, $coin, $sendto, $thread);
            break;
        case 'autopay': /// cron
            $db = $this->dbConnect();
			$userid = $args['uid'];
            $account = $args['account'];
            $amount = $args['amount'];
            $coin = $args['coin'];
            $sendto = $args['recip'];
            $gid = $args['gid'];
            $result = $db->addWorkOrderAutoPay($gid, $userid, $account, $amount, $coin, $sendto, $thread);
            break;
        case 'gettransaction':    /// cron
            $db = $this->dbConnect();
            $userid = $args['uid'];
            $tranid = $args['tranid'];
           	$coin = $args['coin_code'];
            $amount = $args['amount'];
            $confirm = $args['confirm'];
            $gid = $args['gid'];
            $result = $db->addWorkOrderTrans($userid, $gid, $tranid, $amount, $coin, $confirm, $thread);
            break;
        case 'service_charge':   /// cron
            $db = $this->dbConnect();
            $userid = $args['uid'];
           	$coin = $args['coin_code'];
            $gid = $args['gid'];
            $account = $args['account'];
            $rate = $args['rate'];
            $result = $db->addWorkOrderServCharge($userid, $gid, $account, $coin, $rate, $thread);
            break;
       }

		if($notify == true){  // if worker/cron called
		   $active = $this->checkWorkerStatus();
		   if ($active == false ) {
		       try {
		           $this->notifyWorker($result, $thread);
		       } catch (exception $e) {
		           echo $e;
		       }
		   }
		}
        if(isset($result)) {
            return $result;
        }
        return false;
    } 


/*
	CC OTP EMAIL
*/
	function getOTPConfirm($userid, $coin){
 		$db = $this->dbConnect();
        $otpConfirm = $db->getOTPConfirm($userid, $coin);
		return $otpConfirm;
	}

	function ccOTP_otp_generate() {
		  $gen = array();

		  //Generate a random secret key for this instance
		  $secret = $this->random_password(32);
		  $gen['secret'] = base64_encode($secret);

		  //Generate string for url request
		  $data = $this->random_password(16);
		  $gen['data'] = base64_encode($data); //store the data to verify later

		  $message = hash_hmac('sha256', $data, $secret);
		  $gen['signature'] = urlencode(base64_encode($message));

		  return $gen;
	}
	function random_password($length) {
	  $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
	  $password = substr( str_shuffle( $chars ), 0, $length );
	  return $password;
	}

	function ccOTP_otp_get_auth_url($signature) {
		$base_url = "coingateway.net/drupal/";
		$url = $base_url.'?q=ccOTP/auth/'.$signature;
		return $url;
	}

	function ccOTP_otp_insert($dbArgs){
		$db = $this->dbConnect();
        $otpSet = $db->setOTPConfirm($dbArgs);
		return $otpSet;
	}

    function ccOTP_otp_remove($dbArgs){
        $db = $this->dbConnect();
        $otpSet = $db->setOTPRemove($dbArgs);
        return $otpSet;
    }

/*
	CC OTP TWOFACT
*/	
	function validateTwoFact($userid, $twofa){
       $db = $this->dbConnect();
        $secret = $db->getUserTwoFactor($userid);
		$ga = new PHPGangsta_GoogleAuthenticator();
		$checkResult = $ga->verifyCode($secret, $twofa, 2);  
		if ($checkResult) {
				return true;
		}
        return false;
	}

	function getExchangeWallet(){
		    include_once('./config/ccapiconfig.php');
            $ccApiConf = new ccApiConfig();
			/// do getbalance query for the hot wallet, on this coin
			$hotID =  $ccApiConf->getAdminID();
			$db = $this->dbConnect();
			$hotkey = $db->getApiKeyByUserId($hotID);

		 return $hotkey;
	}

    function validateApiRequest($apikey) {
        $db = $this->dbConnect();
        $valid = $db->validateApiKey($apikey);
        if($valid === true) {
            return true;
        }
        return false;
    }

    function getApiUserID($apikey) {
        $db = $this->dbConnect();
        $userid = $db->getUserIdByApiKey($apikey);
        return $userid;
    }

  	 function getOTPpref($userid) {
        $db = $this->dbConnect();
        $user = $db->getOTP_pref($userid);
        return $user;
    }


    function getUserWalletByID($apikey) {
        $db = $this->dbConnect();
        $userid = $db->getUserIdByApiKey($apikey);
        $walletname = $db->getWalletNameByUserId($userid);
        return $walletname;
    }
	
	function getWalletBalance($wallet, $coin){
		 $db = $this->dbConnect();
		$balance = $db->getWalletBalance($wallet, $coin);
		return $balance;
	}

	function getCoinName($coinName){
		 $db = $this->dbConnect();
        $coin_name = $db->getCoinName($coinName);
        return $coin_name;
	}

	function getUserWallet($userid){
        $db = $this->dbConnect();
        $walletname = $db->getWalletNameByUserId($userid);
        return $walletname;
	}

	function getUserInfo($userid){
		$db = $this->dbConnect();
        $userinfo = $db->getUserInfo($userid);
        return $userinfo;
	}


	function getBalance($id, $coin){
        $db = $this->dbConnect();
        $balance = $db->getBalanceByIDandCoin($id, $coin);
        return $balance;
	}

	function getTransaction($trans, $coin){
        $db = $this->dbConnect();
        $trans = $db->getTransactionByID($trans, $coin);
        return $trans;
	}

	function getReceivedByAddress($uid, $gid, $address, $coin, $amount, $confirms, $thread){
        $db = $this->dbConnect();
        $orderid = $db->addWorkOrderReceived($uid, $gid, $address, $amount, $coin, $confirms, $thread);
       $active = $this->checkWorkerStatus();
       if ($active == false) {
           try {
               $this->notifyWorker($orderid, $thread);
           } catch (exception $e) {
               echo $e;
           }
       }
        return $orderid;
	}

	function checkReceivedByAddress($address, $coin){
		$db = $this->dbConnect();
		$walletConfirm = $db->checkWalletConfirms($address, $coin);
		return $walletConfirm;
	}

	function getAddressInfo($address, $coin, $confirms){
        $db = $this->dbConnect();
        $receivedby = $db->getReceivedFromAddress($address, $coin, $confirms);
        return $receivedby;
	}

	function checkServerApiKey($apikey){
        $db = $this->dbConnect();
        $result = $db->checkServerKey($apikey);
        return $result;
	}

	function checkMinMaxCoin($coin){
		$db = $this->dbConnect();
		$coinRange = $db->getCoinRange($coin);
		return $coinRange;
	}


	function getMasterCntrl(){
        $db = $this->dbConnect();
        $admin = $db->getMasterCntrl();
        return $admin;
	}

	function getEnabledCoins($userid){
        $db = $this->dbConnect();
        $coins = $db->getEnabledCoins($userid);
        return $coins;

	}

	function getCoinImage($coin_code){
      $db = $this->dbConnect();
        $coins = $db->getCoinImage($coin_code);
        return $coins;
	}

	function getCoinMaxConfirm($coin_code){
      $db = $this->dbConnect();
        $MXconfirm = $db->getCoinMaxConfirm($coin_code);
        return $MXconfirm;
	}

	function getPendingTrans($maxconfirm, $coin_code){
        $db = $this->dbConnect();
        $trans = $db->getPendingTrans($maxconfirm, $coin_code);
        return $trans;
	}

	function getPendingTotal($maxconfirm, $coin_code){
        $db = $this->dbConnect();
        $balances = $db->getPendingBalances($maxconfirm, $coin_code);
        return $balances;
	}

	function getAutoPay(){
        $db = $this->dbConnect();
        $accounts = $db->getAutoPay();
        return $accounts;

	}
	function getCoinFees($coin){
        $db = $this->dbConnect();
        $accounts = $db->getCoinFees($coin);
        return $accounts;
    }

	function getCoinsByUser($userid){
		$db = $this->dbConnect();
		$coins = $db->getEnabledCoins($userid);
		return $coins;
	}

	function getAllWallets(){
		$db = $this->dbConnect();
		$wallets = $db->getAllWallets();
		return $wallets;
	}
	
	function checkPendingWallets($args){
		$db = $this->dbConnect();
		$pending = $db->getPendingWallets($args['uid'], $args['coin']);
		return $pending;
	}

	function checkPendingTrans($args, $maxconfirm){
		$db = $this->dbConnect();
		$pending = $db->checkPendingTrans($args['uid'], $args['coin'], $maxconfirm);
		return $pending;
	}

	function validateBalance($sub, $balance, $coin){
			$total = array();		
			$coinFees = $this->getCoinFees($coin);
			if(isset($coinFees)){
				$txfee = $coinFees['coin_txfee'];
				$fee = $coinFees['coin_fee'];
				$totalfee = ($sub * $fee)/100;
				$subtotal = $sub +$totalfee + ($txfee * 2);
				if($balance >= $subtotal){
					$total['subtotal'] = $subtotal;
					$total['balance_remaining'] = $balance-$subtotal;
					$total['sent_total'] = $sub;
					$total['txfee'] = $txfee;
					$total['ccfee'] = $fee;
					return $total;

				}
			}
	}

	/*  Service Charges */

	// get user's pricing package
	 function getGrpID($userid){
		$db = $this->dbConnect();
        $grpid = $db->getGroupID($userid);
        return $grpid;
	} 

	// get billing info (grpid, max trans)
	 function getBillingInfo($userid){
		$db = $this->dbConnect();
        $grp = $db->getBillingInfo($userid);
        return $grp;
	} 

	// get all people to be billed
	function getAllBilled(){
			$db = $this->dbConnect();
			$billed = $db->getAllBilled();
			return $billed;
	}

	// get all pricing groups
	function getAllGroups(){
			$db = $this->dbConnect();
			$groups = $db->getAllGroups();
			return $groups;
	}

	// get specific user's account info
	function getAccountInfo($userid){
			$db = $this->dbConnect();
			$account = $db->getAccountInfo($userid);
			return $account;
	}

	// get the balance for a single user's, specific coin
	function getCoinBalance($uid, $coin){
		$db = $this->dbConnect();
		$walBal = $db->getCoinBalance($uid, $coin);
		return $walBal;
	} 

	// get all coins and their rates
	function getAllCoins(){
		$db = $this->dbConnect();
		$coins = $db->getAllCoins();
		return $coins;

	}

	/// get the USD dollar rate for a specific coin
	function getCoinRate($coin){
		$db = $this->dbConnect();
		$rate = $db->getCoinRate($coin);
		return $rate;
	}
	
	///check max transactions in billing cycle, reset to default package
/*	function checkMaxTrans($userid){
		$db = $this->dbConnect();
		$summary = getBillingInfo($userid);
		$grpid = $summary['grp_id'];
		$totalTrans = $summary['total_trans'];
		$MaxTrans = $db->getGroupInfo($grpid);

		if($totalTrans >= $MaxTrans){
			/// reset to default package price
			$db->setDefaultGroup($userid, 1);
		}
	}  */
}

?>
