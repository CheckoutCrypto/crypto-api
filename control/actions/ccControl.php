<?php

class ccControl {

	function getRefreshWorker($masterCntrl, $action, $apikey, $l, $v, $a){
					$tmpl = array();
					if($masterCntrl['disable_worker'] == 0){
						$result = $a->checkServerApiKey($apikey);
						if($result == true){
							$type = $v->getAndValidateType();
							if($type == "coins"){
								$a->notifyWorkerCache("coins");
	       						 $tmpl['response']['status'] = 'success';
							}else if ($type == "groups"){
								$a->notifyWorkerCache("groups");
	       					 	$tmpl['response']['status'] = 'success';
							}else {
	       						 $tmpl['response']['status'] = 'missing type';
                        		$l->ccLog('refreshworker: refreshworker is disabled');
                   			 }

						}
                    } else {
	       				 $tmpl['response']['status'] = 'disabled';
                        $l->ccLog('refreshworker: refreshworker is disabled');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
	}


	function getServiceCharge($masterCntrl, $action, $apikey, $l, $v, $a){
					$tmpl = array();
					if($masterCntrl['disable_withdraw'] == 0){
						$result = $a->checkServerApiKey($apikey);
						if($result == true){
							$userid = $v->getAndValidateUserID();  // uid
							$group = $v->getAndValidateGroup();  // gid
							$coin = $v->getAndValidateCoin();  	// coin
							if(isset($userid) && isset($group) && isset($coin)){
							$account = $a->getAccountInfo($userid);
							$rate = $a->getCoinRate($coin);  // amount
						 	$args['uid'] = $userid;
							$args['gid'] = $group;
						   	$args['coin_code'] = $coin;
							$args['account'] = $account['walletname'];
							$args['rate'] = $rate;

							$result = $a->addWorkOrderQuery($apikey, 'service_charge', $args, true);
							if(isset($result)){
								$tmpl['response']['status'] = 'success';
							}else{
								$tmpl['response']['status'] = 'failure';
							}
						}else{
								$tmpl['response']['status'] = 'missing param';
						}
						}
                    } else {
	       				 $tmpl['response']['status'] = 'disabled';
                        $l->ccLog('service_charge: service_charge is disabled');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
	}

}
