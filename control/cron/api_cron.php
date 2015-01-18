<?php
include('./control/actions/ccapi.php');
include('./control/cron/maintenance.php');
include('./log/api_log.php');
require('./config/CoinsAndActions.php');
$apikey = "";
$thread = "cron";
$maxconfirm = 20;
$l = new ccApiLog('cron');
if(isset($argv[1])){

    $a = new ccApi();
		$m = new cgMaintenance();
	switch($argv[1]){

        case 'pending_withdraw':
        $l->ccLog('Cron: Doing pending withdraw');
		$count = 0;
		$coins = $a->getAllCoins();
		foreach($coins as $cn){
			$maxconfirm = $a->getCoinMaxConfirm($cn['coin_code']);
			$transactions = $a->getPendingTrans($maxconfirm, $cn['coin_code']);
			foreach($transactions as $tran){
				$args['uid'] = $tran['uid'];
				$args['gid'] = $a->getGrpID($tran['uid']);
				$args['coin_code'] = $cn['coin_code'];
				$args['tranid'] = $tran['tranid'];
				$args['amount'] = $tran['amount'];
				$args['confirm'] = $maxconfirm;
				$count = $count + 1;
				$result = $a->addWorkOrderQuery($apikey, 'gettransaction', $args, false, $thread);
		    }
		}
		if($count > 0){
			$a->notifyWorker("any", $thread);
		}

		break;
        case 'pending_balance':

        $l->ccLog('Cron: Doing pending balance');
		$count = 0;
		$coins = $a->getAllCoins();
		foreach($coins as $cn){
			$maxconfirm = $a->getCoinMaxConfirm($cn['coin_code']);
			$wallets = $a->getPendingTotal($maxconfirm, $cn['coin_code']);
			foreach($wallets as $wall){
				$args['uid'] = $wall['uid'];
				$args['gid'] = $a->getGrpID($wall['uid']);
				$args['amount'] = $wall['pending_total'];
				$args['address'] = $wall['walletaddress'];
				$args['coin'] =  $cn['coin_code'];
				$args['confirm'] = $maxconfirm;
				$count = $count + 1;
				$result = $a->addWorkOrderQuery($apikey, 'getreceived', $args, false, $thread);
			}
		}
		if($count > 0){
			$a->notifyWorker("any", $thread);
		}
		break;
		case 'autopayment':

        $l->ccLog('Cron: Doing autopayment');
		$count = 0;
		///  look for balances with autopay_amount > 0
		$balances = $a->getAutoPay();
		foreach($balances as $balance){
				
				$args['uid'] = $balance['uid'];
				$args['gid'] = $a->getGrpID($balance['uid']);
				$args['coin'] = $balance['coin_code'];
				$fees = $a->getCoinFees($args['coin']);
				
				$args['autopay'] = $balance['coin_autopay'];
				$args['recip'] = $balance['coin_autoaddress'];

				$TotalFee = (($args['autopay'] * $fees['coin_fee'])/100) + ($fees['coin_txfee'] * 2);
				$subTotal = $TotalFee + $args['autopay'];
				if($balance['coin_balance']  >= $subTotal){
					$args['amount'] = $balance['coin_autopay'];
					$args['account'] = $a->getUserWallet($args['uid']);
					$result = $a->addWorkOrderQuery($apikey, 'autopay', $args, false, $thread);
					$count = $count + 1;
				}
		}
				if($count > 0){
					$a->notifyWorker("any", $thread);
		}
		////  look for coin_balance >= (autopay_amount*ourfee)/100)  + autopay_amount + txfee	
		//// do work order sendfrom	
		break;
		case 'cleanwork':
       	///  look for work orders longer than 30 minutes and delete them
        $l->ccLog('Cron: Doing cleanwork');
			$m->cleanOrders();
		break;
		case 'cleanwallets':
       	///  look for unused wallets older than 60 minutes and delete them
			$l->ccLog('Cron: Doing cleanwallets');
			$m->cleanWallets();
		break;
		case 'cleantrade':
       	///  look for undeposited trades older than 24 hours and delete them
			$l->ccLog('Cron: Doing cleantrade');
			$m->cleanTrades();
		break;
		case 'cleancron':
       	///  look for undeposited trades older than 24 hours and delete them
			$l->ccLog('Cron: Doing cleantrade');
			$m->cleanCron();
		break;
		case 'service_charge':
       	///  look for any accounts who are at their billing cycle and have an available balance
   //     $l->ccLog('Cron: Doing service charges for the day');
		$count = 0;
		$groups = $a->getAllGroups();
		$coins = $a->getAllCoins();
		$charges = $a->getAllBilled();
		foreach($charges as $chrg){
			$acc = $a->getAccountInfo($chrg['uid']);
			$balance = $a->getCoinBalance($chrg['uid'], $acc['default_coin']);
			$args['coin_code'] = $acc['default_coin'];
			foreach($coins as $cn){
					if($cn['coin_code'] == $acc['default_coin']){
						$args['rate'] = $cn['coin_rate'];
					}
			}
			foreach($groups as $grp){
				if($grp['grpid'] == $chrg['grpid']){
					if($balance > ($grp['cost'] / $args['rate'])){
						$count = $count +1;
						$args['gid'] = $chrg['grpid'];
						$args['uid'] = $chrg['uid'];
						$args['account'] = $acc['walletname'];
						$a->addWorkOrderQuery($apikey, 'service_charge', $args, false, $thread);
					}
				}
			}
		}
		if($count > 0){
				$a->notifyWorker("any", $thread);
		}	 	
		break;
		case 'checkbalance':
			$count = 0;
            $l->ccLog('Cron: Doing checkbalance');
			///  update all wallet balances to make sure they're exact
			$wallets = $a->getAllWallets();
			foreach($wallets as $wall){
				$args['address'] = $wall['walletname'];
				$args['uid'] = $wall['user_id'];
				/// get coins by user
				$coins = $a->getCoinsByUser($args['uid']);
				foreach($coins as $cn){
					$args['coin'] = $cn['coin_code'];
					/// check for pending wallet
					$pending = $a->checkPendingWallets($args);
					$maxConfirm = $a->getCoinMaxConfirm($args['coin']);
					$pendingTrans = $a->checkPendingTrans($args, $maxConfirm);
					if($pending == false && $pendingTrans == false){
						$result = $a->addWorkOrderQuery($apikey, 'getbalance', $args, false, $thread);
						$count = $count + 1;
					}
				} 

			}
			if($count > 0){
				$a->notifyWorker("any", $thread);
		}	 
		break;
	}

}

?>
