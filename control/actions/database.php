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
error_reporting(~0); ini_set('display_errors', 0);

Class ccDb {
    function connectDb() {
        include_once('./config/dbconfig.php');
        $c = new ccDbConfig();
        $ccDbConfig = $c->config();
        $ccDb = new PDO($ccDbConfig['driver'].":host=".$ccDbConfig['host'].";dbname=".$ccDbConfig['database'], $ccDbConfig['username'], $ccDbConfig['password']);
        return $ccDb;
    }

	function getTableByThread($thread){
		$api[0]['thread'] = "trade";
		$api[0]['table'] = "ccdev_trade_orders";
		$api[1]['thread'] = "work";
		$api[1]['table'] = "ccdev_work_orders";
		$api[2]['thread'] = "cron";
		$api[2]['table'] = "ccdev_cron_orders";

		foreach($api as $a){
			if($a['thread'] == $thread){
				return $a['table'];
			}
		}
	}

    function addWorkOrder($identifier, $coin, $gid, $thread) {
        $user_id = $this->getUserIdByApiKey($identifier);
        $walletname = $this->getWalletNameByUserId($user_id);
		$table = $this->getTableByThread($thread);
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,coin_name,uid,gid,amount,sender,action) VALUES (NOW(),:coin_name,:user_id,:gid,:amount,:sender,:action)");
            $stmt->bindValue(':coin_name', $coin, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':gid', $gid, PDO::PARAM_INT);
            $stmt->bindValue(':amount', 0, PDO::PARAM_INT);
            $stmt->bindValue(':sender', $walletname, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'getnewaddress', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function addWorkOrderBalance($userid, $address, $coin, $thread) {
		$recip = "";
		$amount = "0.0000";
		$table = $this->getTableByThread($thread);
		echo $table;
        try { 
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,uid,sender,recipient,amount,coin_name,action) VALUES (NOW(),:user_id,:sender,:recipient,:amount,:coin_name,:action)");
            $stmt->bindValue(':user_id', intval($userid), PDO::PARAM_INT);
            $stmt->bindValue(':sender', (string)$address, PDO::PARAM_STR);
         $stmt->bindValue(':recipient', (string)$recip, PDO::PARAM_STR);
         $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindValue(':coin_name', (string)$coin, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'getbalance', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }   
        return false;
    }  

    function addWorkOrderTrans($user_id, $gid, $tranid, $amount, $coin, $confirm, $thread) {
		$table = $this->getTableByThread($thread);
        try { 
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,uid,gid,sender, recipient, amount,coin_name,action) VALUES (NOW(),:user_id,:gid,:sender,:recipient,:amount,:coin_name,:action)");
            $stmt->bindValue(':user_id', intval($user_id), PDO::PARAM_INT);
            $stmt->bindValue(':gid', intval($gid), PDO::PARAM_INT);
            $stmt->bindValue(':sender', (string)$tranid, PDO::PARAM_STR);
            $stmt->bindValue(':coin_name', (string)$coin, PDO::PARAM_STR);
            $stmt->bindValue(':amount', (string)$amount, PDO::PARAM_STR);
 			 $stmt->bindValue(':recipient', (string)$confirm, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'gettransaction', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }   
        return false;
    }  

    function addWorkOrderReceived($user_id, $gid, $address, $amount, $coin, $confirm, $thread) {
		$table = $this->getTableByThread($thread);
        try { 
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,uid,gid,sender, recipient, amount,coin_name,action) VALUES (NOW(),:user_id,:gid,:sender,:recipient,:amount,:coin_name,:action)");
            $stmt->bindValue(':user_id', intval($user_id), PDO::PARAM_INT);
            $stmt->bindValue(':gid', intval($gid), PDO::PARAM_INT);
            $stmt->bindValue(':sender', (string)$address, PDO::PARAM_STR);
            $stmt->bindValue(':coin_name', (string)$coin, PDO::PARAM_STR);
            $stmt->bindValue(':amount', (string)$amount, PDO::PARAM_STR);
 			 $stmt->bindValue(':recipient', (string)$confirm, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'getreceivedbyaddress', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }   
        return false;
    }  
    function addWorkOrderSend($gid, $identifier, $address, $amount, $coin, $recipient, $thread) {
        $user_id = $this->getUserIdByApiKey($identifier);
		$table = $this->getTableByThread($thread);
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,uid,gid, sender,recipient,amount,coin_name,action) VALUES (NOW(),:user_id,:gid, :sender,:recipient,:amount,:coin_name,:action)");
            $stmt->bindValue(':user_id', intval($user_id), PDO::PARAM_INT);
            $stmt->bindValue(':gid', intval($gid), PDO::PARAM_INT);
            $stmt->bindValue(':sender', (string)$address, PDO::PARAM_STR);
            $stmt->bindValue(':recipient', (string)$recipient, PDO::PARAM_STR);
            $stmt->bindValue(':coin_name', (string)$coin, PDO::PARAM_STR);
            $stmt->bindValue(':amount', (string)$amount, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'sendfrom', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }
    function addWorkOrderAutoPay($gid, $userid, $address, $amount, $coin, $recipient, $thread) {
		$table = $this->getTableByThread($thread);        
		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,uid,gid,sender,recipient,amount,coin_name,action) VALUES (NOW(),:user_id,:gid,:sender,:recipient,:amount,:coin_name,:action)");
            $stmt->bindValue(':user_id', intval($userid), PDO::PARAM_INT);
            $stmt->bindValue(':gid', intval($gid), PDO::PARAM_INT);
            $stmt->bindValue(':sender', (string)$address, PDO::PARAM_STR);
            $stmt->bindValue(':recipient', (string)$recipient, PDO::PARAM_STR);
            $stmt->bindValue(':coin_name', (string)$coin, PDO::PARAM_STR);
            $stmt->bindValue(':amount', (string)$amount, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'sendfrom', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function addWorkOrderServCharge($userid, $gid,  $account, $coin, $rate, $thread) {
		$table = $this->getTableByThread($thread);
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ". $table ." (timestamp,uid,gid,amount,sender,coin_name,action) VALUES (NOW(),:user_id,:gid,:amount,:sender,:coin_name,:action)");
            $stmt->bindValue(':user_id', intval($userid), PDO::PARAM_INT);
            $stmt->bindValue(':gid', $gid, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $rate, PDO::PARAM_STR);
            $stmt->bindValue(':sender', (string)$account, PDO::PARAM_STR);
            $stmt->bindValue(':coin_name', (string)$coin, PDO::PARAM_STR);
            $stmt->bindValue(':action', 'service_charge', PDO::PARAM_STR);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getWorkOrder($identifier, $queueid) {
        $status = false;
        $result = false;
        $row = false;

        $user_id = $this->getUserIdByApiKey($identifier);
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT result,action FROM ccdev_work_orders WHERE basic_id = :basic_id AND uid = :uid LIMIT 1");
            $stmt->bindValue(':basic_id', intval($queueid), PDO::PARAM_INT);
            $stmt->bindValue(':uid', intval($user_id), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouln't happen
        }
        if(isset($row['result'])) {
            $result['status'] = intval($row['result']);
            $result['action'] = strtolower($row['action']);
        }
        if(isset($result)) {
            return $result;
        }
        return false; //order not found
    }

    function getWorkerStatus() {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT worker_status FROM ccdev_admin LIMIT 1");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouldn't happen
        }
        if(isset($row['worker_status'])) {
			if( $row['worker_status'] == 1){
            	$active = $row['worker_status'];
			}
        }
        if(isset($active)) {
            return $active;
        }
        return false; //general error
    }

    function getAddressBalance($orderid, $identifier, $address) {
        $user_id = $this->getUserIdByApiKey($identifier);
			$result = array();
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT pending_total, balance_total FROM ccdev_wallets WHERE walletaddress = :walletaddress AND uid = :uid AND orderid = :orderid OR walletaddress = :walletaddress AND uid = :uid AND last_processed_id = :orderid LIMIT 1");
            $stmt->bindValue(':walletaddress', (string)$address, PDO::PARAM_STR);
            $stmt->bindValue(':orderid', intval($orderid), PDO::PARAM_INT);
            $stmt->bindValue(':uid', intval($user_id), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouldn't happen
        }
        if(isset($row['pending_total'])) {
            $result['balance'] = $row['pending_total'];
            $result['balance_total'] = $row['balance_total'];
        }
        if(isset($result)) {
            return $result;
        }
        return false;
    }

	/// same as function above without orderid and $apikey
    function getWalletBalance($address, $coin) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT pending_total, balance_total, fee_total FROM ccdev_wallets WHERE walletaddress = :walletaddress AND coins_enabled = :coin LIMIT 1");
            $stmt->bindValue(':walletaddress', (string)$address, PDO::PARAM_STR);
            $stmt->bindValue(':coin', $coin, (string)PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouldn't happen
        }
        if(isset($row['pending_total'])) {
 			$result['balance'] = $row['balance_total'];
            $result['pending'] = $row['pending_total'];
 			$result['fee'] = $row['fee_total'];
        }
        if(isset($result)) {
            return $result;
        }
        return false;
    }

   function getWalletCount($address) {
       try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT count FROM ccdev_wallets WHERE walletaddress = :walletaddress LIMIT 1");
            $stmt->bindValue(':walletaddress', $address, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouldn't happen
        }
        if(isset($row['count'])) {
            $result = $row['count'];
        }
        if(isset($result)) {
            return $result;
        }
        return false; 
    }

   function checkWalletConfirms($address, $coin) {
       try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT confirm FROM ccdev_wallets WHERE walletaddress = :walletaddress AND coins_enabled = :coin LIMIT 1");
            $stmt->bindValue(':walletaddress', $address, PDO::PARAM_STR);
            $stmt->bindValue(':coin', $coin, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouldn't happen
        }
        if(isset($row['confirm'])) {
            $result = $row['confirm'];
        }
        if(isset($result)) {
            return $result;
        }
        return false; 
    }

	function setWalletCount($wallet, $count){

		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("update count = :count FROM ccdev_wallets WHERE walletaddress = :wallet");
            $stmt->bindValue(':count', intval($count), PDO::PARAM_INT);
            $stmt->bindValue(':wallet', $wallet, PDO::PARAM_STR);
            $stmt->execute();
					
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            // echo $e;
        } 
	}

    function getGeneratedWallet($orderid, $identifier, $coin) {
        $user_id = $this->getUserIdByApiKey($identifier);
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT walletaddress FROM ccdev_wallets WHERE orderid = :orderid AND uid = :uid AND coins_enabled =:coincode AND timestamp <= DATE_ADD(NOW(), INTERVAL +10 MINUTE) OR last_processed_id = :orderid AND uid = :uid AND coins_enabled = :coincode AND timestamp <= DATE_ADD(NOW(), INTERVAL +10 MINUTE) LIMIT 1");
            $stmt->bindValue(':coincode', $coin, PDO::PARAM_STR);
            $stmt->bindValue(':orderid', intval($orderid), PDO::PARAM_INT);
            $stmt->bindValue(':uid', intval($user_id), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            // echo $e;
        }
        if(is_array($rows) AND count($rows) == 1) {
            $row = $rows[0];
        } else {
            return false; //this shouldn't happen
        }
        if(isset($row['walletaddress'])) {
            $result = $row['walletaddress'];
        }
        if(isset($result)) {
            return $result;
        }
        return false;
    }

    function getApiKeyByUserId($user_id) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT api_key FROM ccdev_accounts WHERE user_id = :user_id LIMIT 1" );
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['api_key'])) {
                    return $row['api_key'];
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getAllWallets() {
        try {
 			$wallets = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT walletname, user_id FROM ccdev_accounts" );
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
                foreach($rows as $row) {
                  	$wall['walletname'] = $row['walletname'];
					$wall['user_id'] = $row['user_id'];
					array_push($wallets, $wall); 
                }
            }
			
            return $wallets;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }


    function getBalanceByIDandCoin($user_id, $coin) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_balance FROM ccdev_balance WHERE uid = :user_id AND coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':coin_code', $coin, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_balance'])) {
                    return $row['coin_balance'];
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getWalletNameByUserId($user_id) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT walletname FROM ccdev_accounts WHERE user_id = :user_id LIMIT 1" );
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['walletname'])) {
                    return $row['walletname'];
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }
	
    function setOTPConfirm($args){

        $existing = $this->getOTPConfirm($args['uid'], $args['coin_code']);

        if(!($existing)) {
            try {
                $ccDb = $this->connectDb();
                $stmt = $ccDb->prepare("INSERT INTO ccdev_otp (uid, coin_name, coin_code, coin_amount, coin_address, callback_action, secret, data, sent, valid, created) VALUES (:uid, :coin_name, :coin_code, :coin_amount, :coin_address, :action, :secret, :data, :sent, :valid, :created)");
                $stmt->bindValue(':created', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $stmt->bindValue(':uid', (int)$args['uid'], PDO::PARAM_INT);
                $stmt->bindValue(':coin_name', $args['coin_name'], PDO::PARAM_STR);
                $stmt->bindValue(':coin_code', $args['coin_code'], PDO::PARAM_STR);
                $stmt->bindValue(':coin_amount',  $args['coin_amount'], PDO::PARAM_STR);
                $stmt->bindValue(':coin_address', $args['coin_address'], PDO::PARAM_STR);
                $stmt->bindValue(':action', $args['action'], PDO::PARAM_STR);
                $stmt->bindValue(':secret', $args['secret'], PDO::PARAM_STR);
                $stmt->bindValue(':data', $args['data'], PDO::PARAM_STR);
                $stmt->bindValue(':sent', 1, PDO::PARAM_INT);
                $stmt->bindValue(':valid', 0, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->rowCount();
                return $rows;
            } catch (exception $e) {
                //echo($e);
            }
        } else {
            try {
                $ccDb = $this->connectDb();
                $stmt = $ccDb->prepare("UPDATE ccdev_otp SET created = :created, coin_name = :coin_name, coin_code = :coin_code, uid = :uid, coin_amount = :coin_amount, coin_address = :coin_address, callback_action = :action ,data = :data, secret = :secret, sent = sent +1, valid = :valid WHERE coin_code = :coin_code AND uid = :uid");
                $stmt->bindValue(':created', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $stmt->bindValue(':uid', (int)$args['uid'], PDO::PARAM_INT);
                $stmt->bindValue(':coin_name', $args['coin_name'], PDO::PARAM_STR);
                $stmt->bindValue(':coin_code', $args['coin_code'], PDO::PARAM_STR);
                $stmt->bindValue(':coin_amount',  $args['coin_amount'], PDO::PARAM_STR);
                $stmt->bindValue(':coin_address', $args['coin_address'], PDO::PARAM_STR);
                $stmt->bindValue(':action', $args['action'], PDO::PARAM_STR);
                $stmt->bindValue(':secret', $args['secret'], PDO::PARAM_STR);
                $stmt->bindValue(':data', $args['data'], PDO::PARAM_STR);
                $stmt->bindValue(':valid', 0, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->rowCount();
                return $rows;
            } catch (exception $e) {
                //echo($e);
            }
        }
        return false;

	}

    function setOTPRemove($args){
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("DELETE FROM ccdev_otp WHERE coin_name = :coin_name AND coin_code = :coin_code AND uid = :uid AND coin_address = :coin_address");
            $stmt->bindValue(':uid', (int)$args['uid'], PDO::PARAM_INT);
            $stmt->bindValue(':coin_name', $args['coin_name'], PDO::PARAM_STR);
            $stmt->bindValue(':coin_code', $args['coin_code'], PDO::PARAM_STR);
            $stmt->bindValue(':coin_address', $args['coin_address'], PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->rowCount();
            if($rows === 1) {
                return TRUE;
            }
        } catch (exception $e) {
            //var_dump($e);
        }
        return false;
    }

    function getOTPConfirm($userid, $coin){
        try {
			$otpDetails = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT uid, coin_name, coin_code, coin_amount, coin_address, valid, sent FROM ccdev_otp WHERE uid = :uid AND coin_code = :coin LIMIT 1" );
            $stmt->bindValue(':uid', (int)$userid, PDO::PARAM_INT);
            $stmt->bindValue(':coin', $coin, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['uid'])) {
                     $otpDetails['uid'] = $row['uid'];
                     $otpDetails['coin_name'] = $row['coin_name'];
                     $otpDetails['coin_code'] = $row['coin_code'];
                     $otpDetails['coin_address'] = $row['coin_address'];
                     $otpDetails['coin_amount'] = $row['coin_amount'];
                     $otpDetails['valid'] = $row['valid'];
                     $otpDetails['sent'] = $row['sent'];
					return $otpDetails;
                }
            }
        } catch (exception $e) {
            //// echo $e;
        }
	}



    function getUserIdByApiKey($apikey) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT user_id FROM ccdev_accounts WHERE api_key = :apikey LIMIT 1" );
            $stmt->bindValue(':apikey', $apikey, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['user_id'])) {
                    return $row['user_id'];
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getOTP_Pref($userid) {
        try {
			$user = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT user_id, pref_otp, pending_otp FROM ccdev_auth WHERE user_id = :userid LIMIT 1" );
            $stmt->bindValue(':userid', $userid, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['user_id'])) {
                    $user['uid'] = $row['user_id'];
                    $user['pref_otp'] = $row['pref_otp'];
                    $user['pending_otp'] = $row['pending_otp'];
					return $user;
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function validateApiKey($apikey) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT api_key FROM ccdev_accounts WHERE api_key = :apikey LIMIT 1" );
            $stmt->bindValue(':apikey', $apikey, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['api_key'])) {
                    $apikey_db = $row['api_key'];
                    if($apikey_db === $apikey) {
                        return true;
                    }
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function checkServerKey($apikey) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT api_key FROM ccdev_accounts WHERE api_key = :apikey AND isMaintenance = :isMaintenance LIMIT 1" );
            $stmt->bindValue(':apikey', $apikey, PDO::PARAM_STR);
            $stmt->bindValue(':isMaintenance', 1, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['api_key'])) {
                    $apikey_db = $row['api_key'];
                    if($apikey_db === $apikey) {
                        return true;
                    }
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getRates($coin_code) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_rate FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_rate'])) {
                        return $row['coin_rate'];
                    }
                }
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getCoinImage($coin_code) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_image FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_image'])) {
                        return $row['coin_image'];
                    }
                }
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getCoinRate($coin_code) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_rate FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_rate'])) {
                        return $row['coin_rate'];
                    }
                }
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }


   function getCoinRange($coin_code) {
        try {
			$coinRange = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT min_amount, max_amount FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['min_amount'])) {
                       $coinRange['min_amount'] = $row['min_amount'];
						$coinRange['max_amount'] = $row['max_amount'];                    
					}
                }
 			return $coinRange; 
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

   function getCoinName($coin_code) {
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_name FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_name'])) {
                        return $row['coin_name'];
                    }
                }
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

   function getCoinMaxConfirm($coin_code) {
        try {
			$fees = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_MxConf FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_MxConf'])) {
                        return $row['coin_MxConf'];
                    }
                }
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

    function getCoinFees($coin_code) {
        try {
			$fees = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_fee, coin_txfee FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_fee'])) {
						$fees['coin_txfee'] = $row['coin_txfee'];
						$fees['coin_fee'] = $row['coin_fee'];
                        return $fees;
                    }
                }
        } catch (exception $e) {
            // echo $e;
        }
        return false;
    }

	function getMasterCntrl(){
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT disable_all_coins, disable_worker, disable_withdraw, disable_getnewaddress, disable_getbalance, disable_rate FROM ccdev_admin LIMIT 1" );
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
				    $admin['disable_all_coins'] = $row['disable_all_coins'];
                    $admin['disable_worker'] = $row['disable_worker'];
                    $admin['disable_withdraw'] = $row['disable_withdraw'];
                    $admin['disable_getnewaddress'] = $row['disable_getnewaddress'];
                    $admin['disable_getbalance'] = $row['disable_getbalance'];
                    $admin['disable_rate'] = $row['disable_rate'];
					return $admin;
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;


	}

	function getTransactionByID($trans, $coin){
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_code, tranid, sender, receiver, amount, status, timestamp FROM ccdev_transactions WHERE tranid = :tranid AND coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':tranid', $trans, PDO::PARAM_STR);
            $stmt->bindValue(':coin_code', $coin, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['tranid'])) {
				    $transaction['coin_code'] = $row['coin_code'];
                    $transaction['tranid'] = $row['tranid'];
                    $transaction['sender'] = $row['sender'];
                    $transaction['receiver'] = $row['receiver'];
                    $transaction['amount'] = $row['amount'];
                    $transaction['status'] = $row['status'];
                    $transaction['timestamp'] = $row['timestamp'];
					return $transaction;
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;


	}

	function getReceivedFromAddress($address, $coin, $confirms){
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT walletaddress, coins_enabled, balance_total, pending_total, fee_total, confirm, orderid, timestamp FROM ccdev_wallets WHERE walletaddress = :walletaddress AND coins_enabled = :coins_enabled AND confirm >= :confirm LIMIT 1" );
            $stmt->bindValue(':walletaddress', $address, PDO::PARAM_STR);
            $stmt->bindValue(':coins_enabled', $coin, PDO::PARAM_STR);
            $stmt->bindValue(':confirm', $confirms, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['walletaddress'])) {
				    $receivedby['walletaddress'] = $row['walletaddress'];
				    $receivedby['coins_enabled'] = $row['coins_enabled'];
                    $receivedby['balance_total'] = $row['balance_total'];
                    $receivedby['pending_total'] = $row['pending_total'];
                    $receivedby['fee_total'] = $row['fee_total'];
                    $receivedby['confirm'] = $row['confirm'];
                    $receivedby['orderid'] = $row['orderid'];
                    $receivedby['timestamp'] = $row['timestamp'];
					return $receivedby;
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;


	}
	function getEnabledCoins($userid){
        try {
			$coins = array();
			$count = 0;
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_name, coin_code FROM ccdev_balance WHERE uid = :uid" );
            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
					foreach($rows as $row){
						$coin['coin_name'] = $row['coin_name'];
						$coin['coin_code'] = $row['coin_code'];
						array_push($coins, $coin);
					}
            }
            return $coins;
        } catch (exception $e) {
            // echo $e;
        }
        return false;


	}
	function getPendingTrans($maxconfirm, $coin_code){
        try {
			$transactions = array();
			$count = 0;
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT uid, tranid, amount, status FROM ccdev_transactions WHERE status < :maxconfirm AND status >= 0 AND coin_code = :coin_code");
            $stmt->bindValue(':maxconfirm', intval($maxconfirm), PDO::PARAM_INT);
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
					foreach($rows as $row){
						$trans['uid'] = $row['uid'];
						$trans['tranid'] = $row['tranid'];
						$trans['amount'] = $row['amount'];
						$trans['status'] = $row['status'];
						array_push($transactions, $trans);
					}
            }
            return $transactions;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

	}
	function getAutoPay(){
        try {
			$autopayments = array();
			$count = 0;
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT uid, coin_autopay, coin_autoaddress, coin_balance, coin_code FROM ccdev_balance WHERE coin_balance >= coin_autopay AND coin_autopay > 0");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
					foreach($rows as $row){
						$autopay['uid'] = $row['uid'];
						$autopay['coin_autopay'] = $row['coin_autopay'];
						$autopay['coin_autoaddress'] = $row['coin_autoaddress'];
						$autopay['coin_balance'] = $row['coin_balance'];
						$autopay['coin_code'] = $row['coin_code'];
						array_push($autopayments, $autopay);
					}
            }
            return $autopayments;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

	}

	function getUserTwoFactor($userid){
  		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT twofa_key FROM ccdev_auth WHERE user_id = :user_id LIMIT 1" );
            $stmt->bindValue(':user_id', $userid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['twofa_key'])) {
                    return $row['twofa_key'];
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

	}

	function getUserInfo($userid){
  		try {
			$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT name, mail FROM users WHERE uid = :uid LIMIT 1" );
            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['mail'])) {
					$userinfo['username'] = $row['name'];
					$userinfo['email'] = $row['mail'];
					return $userinfo;
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

    }

	function getGroupID($userid){
  		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT grp_id FROM ccdev_billing_summary WHERE user_id = :uid LIMIT 1" );
            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['grp_id'])) {
					$groupid = $row['grp_id'];
					return $groupid;
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

    }

	function getBillingInfo($userid){
  		try {
			$group = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT grp_id, total_trans FROM ccdev_billing_summary WHERE user_id = :uid LIMIT 1" );
            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['grp_id'])) {
					$group['grp_id'] = $row['grp_id'];
					$group['total_trans'] = $row['total_trans'];
					return $group;
                }
            }
            return false;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

    }

	function getPendingBalances($maxconfirm, $coin_code){
        try {
			$wallets = array();
			$count = 0;
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT uid, pending_total, walletaddress, confirm FROM ccdev_wallets WHERE pending_total > 0 AND confirm < :maxconfirm AND coins_enabled = :coins_enabled");
            $stmt->bindValue(':maxconfirm', $maxconfirm, PDO::PARAM_INT);
            $stmt->bindValue(':coins_enabled', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
					foreach($rows as $row){
						$wallet['uid'] = $row['uid'];
						$wallet['pending_total'] = $row['pending_total'];
						$wallet['walletaddress'] = $row['walletaddress'];
						$wallet['confirm'] = $row['confirm'];
						array_push($wallets, $wallet);
					}
            }
            return $wallets;
        } catch (exception $e) {
            // echo $e;
        }
        return false;

	}

	function getPendingWallets($userid, $coin){
			$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT uid FROM ccdev_wallets WHERE uid = :uid AND coins_enabled = :coin AND pending_total > :pending LIMIT 1" );  //  AND confirm < :confimMax AND confirm >= :confirmMin 
            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->bindValue(':coin', $coin, PDO::PARAM_STR);
            $stmt->bindValue(':pending', 0, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['uid'])) {
					return true;
                }
            }
			return false;

	}

	function checkPendingTrans($userid, $coin, $maxconfirm){
			$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT uid FROM ccdev_transactions WHERE uid = :uid AND coin_code = :coin AND status < :stat LIMIT 1" );  //  AND confirm < :confimMax AND confirm >= :confirmMin 

            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->bindValue(':coin', $coin, PDO::PARAM_STR);
            $stmt->bindValue(':stat', intval($maxconfirm), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['uid'])) {
					return true;
                }
            }
			return false;

	}

	/* Service Charges */

	function getCoinBalance($uid, $coin){
			$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_balance FROM ccdev_balance WHERE uid = :uid AND coin_code = :coin LIMIT 1" );  //  AND confirm < :confimMax AND confirm >= :confirmMin 
            $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
            $stmt->bindValue(':coin', $coin, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_balance'])) {
					return $row['coin_balance'];
                }
            }
			return false;

	}

	function getAccountInfo($userid){
			$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT walletname, default_coin FROM ccdev_accounts WHERE user_id = :uid LIMIT 1" );  //  AND
            $stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['walletname'])) {
					$userinfo['walletname'] = $row['walletname'];
					$userinfo['default_coin'] = $row['default_coin'];
					return $userinfo;
                }
            }
			return false;

	}

	function getGroupInfo($gid){
			$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT grp_max_transactions FROM ccdev_groups WHERE basic_id = :gid LIMIT 1" );  //  AND
            $stmt->bindValue(':gid', $gid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['grp_max_transactions'])) {
					return $row['grp_max_transactions'];
                }
            }
			return false;

	}

	function getAllBilled(){
        	$billed = array();
        	$userinfo = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT user_id, grp_id FROM ccdev_billing_summary WHERE billing_cycle <= DATE_ADD(NOW(), INTERVAL -30 DAY) " );  
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
		      	 foreach($rows as $row){
		            if(isset($row['user_id'])) {
						$userinfo['uid'] = $row['user_id'];
						$userinfo['grpid'] = $row['grp_id'];
						array_push($billed, $userinfo);
		            }
				}
				return $billed;
            }
			return false;

	}

	function getAllGroups(){
        	$groups = array();
        	$grp = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT basic_id, grp_withdraw_fee, grp_payment_length FROM ccdev_groups" );  
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
		
		      	 foreach($rows as $row){
		            if(isset($row['grp_cost'])) {
						$grp['grpid'] = $row['basic_id'];
						$grp['cost'] = $row['grp_withdraw_fee'];
						$grp['payment_length'] = $row['grp_payment_length'];
						array_push($groups, $grp);
		            }
				}
				return $groups;
            }
			return false;
	}

	function getAllCoins(){
        	$coins = array();
        	$cn = array();
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_rate, coin_code FROM ccdev_coin" );  
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
		
		      	 foreach($rows as $row){
		            if(isset($row['coin_rate'])) {
						$cn['coin_code'] = $row['coin_code'];
						$cn['coin_rate'] = $row['coin_rate'];
						array_push($coins, $cn);
		            }
				}
				return $coins;
            }
			return false;
	}

	
}
