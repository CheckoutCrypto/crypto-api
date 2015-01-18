<?php

Class ratesDb {

    function connectDb() {
        include_once('./config/dbconfig.php');
        $r = new ccDbConfig();
        $rDbConfig = $r->config();
        $rDb = new PDO($rDbConfig['driver'].":host=".$rDbConfig['host'].";dbname=".$rDbConfig['database'], $rDbConfig['username'], $rDbConfig['password']);
        return $rDb;
    }

	function getBtcRate(){
		$coins = array();
		$coin = array();
		try{
		    $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_code, market_sell_depth, market_buy_depth, exchange_id, exchange_spec FROM ccdev_coin WHERE coin_code = :coin LIMIT 1" );
            $stmt->bindValue(':coin', 'BTC', PDO::PARAM_STR);
            $stmt->execute();
             $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows) AND count($rows) == 1) {
                $row = $rows[0];
                if(isset($row['coin_code'])) {
					$coin['coin_code'] = $row['coin_code'];
					$coin['exchange_id'] = $row['exchange_id'];
					$coin['exchange_spec'] = $row['exchange_spec'];
					$coin['sell_depth'] = $row['market_sell_depth'];
					$coin['buy_depth'] = $row['market_buy_depth'];
					return $coin;
                }
            }
		}catch (exception $e){
			echo $e;
		}		
		return false;

	}

	function getAllCoinExchanges(){
		$coins = array();
		$coin = array();
		try{
		    $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_code, market_sell_depth, market_buy_depth, exchange_id, exchange_spec FROM ccdev_coin WHERE coin_code != :coin" );
            $stmt->bindValue(':coin', 'BTC', PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
              foreach($rows as $row){
                if(isset($row['coin_code'])) {
					$coin['coin_code'] = $row['coin_code'];
					$coin['exchange_id'] = $row['exchange_id'];
					$coin['exchange_spec'] = $row['exchange_spec'];
					$coin['sell_depth'] = $row['market_sell_depth'];
					$coin['buy_depth'] = $row['market_buy_depth'];
					array_push($coins, $coin);
                }
			  }
				return $coins;
            }
		}catch (exception $e){
			echo $e;
		}		
		return false;
	}

	function getAllFiatExchanges(){
		$coins = array();
		$coin = array();
		try{
		    $ccDb = $this->connectDb();
            $stmt = $ccDb->prepare("SELECT coin_code, exchange_id FROM ccdev_fiat" );
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(is_array($rows)) {
              foreach($rows as $row){
                if(isset($row['coin_code'])) {
					$coin['coin_code'] = $row['coin_code'];
					$coin['exchange_id'] = $row['exchange_id'];
					array_push($coins, $coin);
                }
			  }
				return $coins;
            }
		}catch (exception $e){
			echo $e;
		}		
		return false;
	}
    
    function setRates($coin_code,$coin_result) {
			if($coin_code == "BTC"){
				$coin_result['usd'] = $coin_result['avg'];
				$coin_result['avg'] = '1';
				$coin_result['sell'] = $coin_result['high'];
				$coin_result['buy'] = $coin_result['low'];
			}
        try {
            $rDb = $this->connectDb();
            $stmt = $rDb->prepare("UPDATE ccdev_coin SET coin_rate = :coin_rate, coin_rate_btc = :btc, coin_rate_sell = :sell, coin_rate_buy = :buy WHERE coin_code = :coin_code LIMIT 1" );
            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->bindValue(':coin_rate', $coin_result['usd'], PDO::PARAM_STR);
            $stmt->bindValue(':btc', $coin_result['avg'], PDO::PARAM_STR);
            $stmt->bindValue(':sell', $coin_result['sell'], PDO::PARAM_STR);
            $stmt->bindValue(':buy', $coin_result['buy'], PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            echo $e;
        }
        return false;
    }

    function setFiatRates($fiat,$rate) {
        try {
            $rDb = $this->connectDb();
            $stmt = $rDb->prepare("UPDATE ccdev_fiat SET coin_rate_usd = :coin_rate WHERE coin_code = :coin_code LIMIT 1" );

            $stmt->bindValue(':coin_code', $fiat, PDO::PARAM_STR);
            $stmt->bindValue(':coin_rate', $rate, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            echo $e;
        }
        return false;
    }

}


?>
