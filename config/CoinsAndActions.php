<?php


Class ccApi_validate{
   function connectDb() {
        include_once('./config/dbconfig.php');
        $c = new ccDbConfig();
        $ccDbConfig = $c->config();
        $ccDb = new PDO($ccDbConfig['driver'].":host=".$ccDbConfig['host'].";dbname=".$ccDbConfig['database'], $ccDbConfig['username'], $ccDbConfig['password']);
        return $ccDb;
    }

	function getCoin($coin){
		$Coins = array();
      $ccDb = $this->connectDb();
      $stmt = $ccDb->prepare("SELECT coin_code, coin_name, coin_txfee, min_amount, max_amount, coin_validate, coin_MxConf from ccdev_coin WHERE coin_code = :coin");
      $stmt->bindValue(':coin', $coin, PDO::PARAM_STR);
      $stmt->execute();
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if(is_array($rows) AND count($rows) == 1) {
       $row = $rows[0];
           if(isset($row['coin_code'])) {
					$Coins['coin_code'] = $row['coin_code'];
					$Coins['coin_name'] = $row['coin_name'];
					$Coins['coin_txfee'] = $row['coin_txfee'];
					$Coins['coin_min'] = $row['min_amount'];
					$Coins['coin_max'] = $row['max_amount'];
					$Coins['coin_valid'] = $row['coin_validate'];
					$Coins['coin_mxConf'] = $row['coin_MxConf'];
				}
		}
					return $Coins;
	}

	function Config($type){

		switch($type){
		case 1:	
		break;
		case 2:
			$Actions = array(
			'getaddressinfo',
			'getbalance',
			'gettransaction',
			'getreceivedbyaddress',
			'getnewaddress',
			'send',
			'getstatus',
			'getbalance',
			'refreshworker',
			'refreshcoins',
			'getrate',
			'servicecharge',
			'sendfunds',
			'gettradeaddress',
			'gettradereceived',
			'gettradestatus',
			);
			return $Actions;
		break;
		}
	}
}

?>
