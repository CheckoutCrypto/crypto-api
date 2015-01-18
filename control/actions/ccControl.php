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
