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
Class ccTrade {
    function connectDb() {
        include_once('./config/dbconfig.php');
        $c = new ccDbConfig();
        $ccDbConfig = $c->config();
        $ccDb = new PDO($ccDbConfig['driver'].":host=".$ccDbConfig['host'].";dbname=".$ccDbConfig['database'], $ccDbConfig['username'], $ccDbConfig['password']);
        return $ccDb;
    }

	function createTrade($origKey, $coin, $cointrade, $amountDep, $amountRec, $address, $ignore, $idgen){	
  		 $user_id = $this->getUserIdByApiKey($origKey);
		/// default param
		$gid = 1;
		$txid = '';
		$status = 0;	
		$addressin = '';
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("INSERT INTO ccdev_trades (basic_id, bundle_type, uid, gid, coin_to, amount_to, coin_from, amount_from, ignore_amt, address_gen, address_out, txid, id_gen, id_last, status, timestamp) VALUES (:basicid, :bundle, :user, :group, :cointo, :amount, :coinfrom, :amountfrom, :ignore, :addressgen, :addressout, :txid, :idgen, :idlast, :status, NOW())");
            $stmt->bindValue(':basicid', '0', PDO::PARAM_INT);
            $stmt->bindValue(':bundle', 'trade_bundle', PDO::PARAM_STR);
            $stmt->bindValue(':user', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':group', $gid, PDO::PARAM_INT);
            $stmt->bindValue(':cointo', $cointrade, PDO::PARAM_STR);
            $stmt->bindValue(':amount', $amountRec, PDO::PARAM_INT);
            $stmt->bindValue(':coinfrom', $coin, PDO::PARAM_STR);
            $stmt->bindValue(':amountfrom', $amountDep, PDO::PARAM_INT);
			$stmt->bindValue(':ignore', $ignore, PDO::PARAM_STR);
            $stmt->bindValue(':addressgen', $addressin, PDO::PARAM_STR);
            $stmt->bindValue(':addressout', $address, PDO::PARAM_STR);
            $stmt->bindValue(':txid', $txid, PDO::PARAM_STR);
            $stmt->bindValue(':idgen', $idgen, PDO::PARAM_INT);
            $stmt->bindValue(':idlast', $idgen, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status, PDO::PARAM_INT);
            $stmt->execute();
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            echo $e;
        }
        return false;
	
	}

	function addTradeAddress($origKey, $address, $last_id){
  		 $user_id = $this->getUserIdByApiKey($origKey);
		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("update ccdev_trades SET address_gen = :addressgen, id_gen = :idgen WHERE uid = :user AND id_last = :idlast");
            $stmt->bindValue(':addressgen', $address, PDO::PARAM_STR);
			$stmt->bindValue(':idlast', $last_id, PDO::PARAM_INT);
            $stmt->bindValue(':idgen', $last_id, PDO::PARAM_INT);
            $stmt->bindValue(':user', $user_id, PDO::PARAM_INT);
	  $stmt->execute();
					
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            echo $e;
        } 

	}

	function updateStatus($origKey, $address, $queueid){
  		 $user_id = $this->getUserIdByApiKey($origKey);
		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("update ccdev_trades SET id_last = :lastid WHERE uid = :user AND address_gen = :address");
            $stmt->bindValue(':lastid', $queueid, PDO::PARAM_STR);
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->bindValue(':user', $user_id, PDO::PARAM_INT);
            $stmt->execute();
					
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            echo $e;
        } 

	}


	function updateReceived($origKey, $amount, $address, $status){
  		 $user_id = $this->getUserIdByApiKey($origKey);
		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("update ccdev_trades SET status = :status, amount_from = :amount WHERE uid = :user AND address_gen = :address");
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status, PDO::PARAM_INT);
            $stmt->bindValue(':user', $user_id, PDO::PARAM_INT);
            $stmt->execute();
					
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            echo $e;
        } 

	}

	function transComplete($origKey, $txid, $amount, $address, $status){
  		 $user_id = $this->getUserIdByApiKey($origKey);
		try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("update ccdev_trades SET status = :status, amount_to = :amount, txid = :txid WHERE uid = :user AND address_gen = :address");
            $stmt->bindValue(':txid', $txid, PDO::PARAM_STR);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_INT);
            $stmt->bindValue(':user', $user_id, PDO::PARAM_INT);
            $stmt->execute();
					
            return $ccDb->lastInsertId();
        } catch (exception $e) {
            echo $e;
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
            echo $e;
        }
        return false;
    }

	function getTrade($apikey, $address){
	  	$user_id = $this->getUserIdByApiKey($apikey);

		$trades = array();
		 try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT * FROM ccdev_trades WHERE uid = :user_id AND address_gen = :address LIMIT 1" );
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['uid'])) {
                    $trades['uid'] = $row['uid'];
                    $trades['gid'] = $row['gid'];
                    $trades['coin_to'] = $row['coin_to'];
                    $trades['amount_to'] = $row['amount_to'];
                    $trades['coin_from'] = $row['coin_from'];  
                    $trades['amount_from'] = $row['amount_from'];  
                    $trades['ignore_amt'] = $row['ignore_amt'];  
					$trades['address_gen'] = $row['address_gen'];              
                    $trades['address_out'] = $row['address_out'];             	
                    $trades['id_gen'] = $row['id_gen'];              
                    $trades['id_last'] = $row['id_last'];      
                    $trades['status'] = $row['status'];  
                    $trades['timestamp'] = $row['timestamp'];
					return $trades;                     	           	                   	           	
	            }
            }
            return false;
        } catch (exception $e) {
            echo $e;
        }
        return false;

	}

	function getTradeStatus($address){

		$trades = array();
		 try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT status FROM ccdev_trades WHERE address_gen = :address LIMIT 1" );
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['status'])) {
                    $tradeStatus = $row['status'];
						return $tradeStatus; 	                   	           	
	            }
            }
            return false;
        } catch (exception $e) {
            echo $e;
        }
        return false;

	}

    function getSpecificCoinRate($coin_code) {
		$rates = array();
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_rate, coin_rate_btc, coin_rate_sell, coin_rate_buy FROM ccdev_coin WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_rate'])) {
                        $rates['coin_rate'] = $row['coin_rate'];
                        $rates['coin_rate_btc'] = $row['coin_rate_btc'];
						$rates['coin_rate_sell'] = $row['coin_rate_sell'];
						$rates['coin_rate_buy'] = $row['coin_rate_buy'];
				return $rates;
                    }
                }

        } catch (exception $e) {
            echo $e;
        }
        return false;
    }

	function getTradeFee($gid){
        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT grp_trade_fee FROM ccdev_groups WHERE basic_id = :group LIMIT 1" );
            $stmt->bindValue(':group', $gid, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['grp_trade_fee'])) {
                    return $row['grp_trade_fee'];
                }
            }
            return false;
        } catch (exception $e) {
            echo $e;
        }
        return false;			
	}

	function getHotBalance($apikey, $coin){
	  	$user_id = $this->getUserIdByApiKey($apikey);
	        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_balance FROM ccdev_balance WHERE uid = :user_id AND coin_code = :coin LIMIT 1" );
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
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
        } catch (exception $e) {
            echo $e;
        }
        return false;	

	}

	function getTransIDByReceiver($address, $amount){
	        try {
            $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT tranid FROM ccdev_transactions WHERE receiver = :address AND amount = :amount AND timestamp <= DATE_ADD(NOW(), INTERVAL 1 MICROSECOND) AND timestamp >= DATE_ADD(NOW(), INTERVAL -10 SECOND) LIMIT 1" );
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['tranid'])) {
                    return $row['tranid'];
                }
            }
            return false;
        } catch (exception $e) {
            echo $e;
        }
        return false;	

	}

}

?>
