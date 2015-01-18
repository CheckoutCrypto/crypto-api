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
class ccSendFunds {

	function getSendFunds($masterCntrl, $action, $apikey, $l, $v, $a){
		if($masterCntrl['disable_withdraw'] == 0){
						$tmpl = array();
						 $coin = $v->getAndValidateCoin();
						 $recip = $v->getAndValidateAddress($coin);
						 $amount = $v->getAndValidateAmount();
   						$tmpl['response']['status'] = 'failure';
							if(isset($recip) && isset($amount) && isset($coin)) {
								$args['recip'] = $recip;
								$args['coin'] = $coin;
								$uid = $a->getApiUserID($apikey);

								$rangeCoin = $a->checkMinMaxCoin($coin);
								$validRange = $v->getAndValidateMinMaxAmount($rangeCoin['min_amount'], $rangeCoin['max_amount'], $amount);
								 $args['uid'] = $uid;
								$balance = $a->getBalance($uid, $coin);
								$account = $a->getUserWalletByID($apikey);
									
								/// get account name by api key $address 
								if(isset($account)) {
									$args['account'] = $account;
									$args['gid'] = $a->getGrpID($uid);
									$validBalance = $a->validateBalance($amount, $balance, $coin);
									
						            if(isset($validBalance)){
											$args['amount'] = $amount;
											$result = $a->addWorkOrderQuery($apikey, 'sendto', $args, true);
						                        
											if($result) {
													$tmpl['response']['status'] = 'success';
													$tmpl['response']['queue_id'] = $result;
													$tmpl['response']['sent_total'] = $validBalance['sent_total'];
													$tmpl['response']['subtotal'] = $validBalance['subtotal'];
													$tmpl['response']['txfee'] = $validBalance['txfee'];
													$tmpl['response']['ccfee'] = $validBalance['ccfee'];
													$tmpl['response']['balance_remaining'] = $validBalance['balance_remaining'];
						                    } else {
						                          $l->ccLog('send: Invalid result from addWorkOrderQuery');
   													$tmpl['response']['status'] = 'failure';
						                    }
						             } else {
						                  $l->ccLog('send: Invalid balance. Insufficient balance to complete request.');
						                  $tmpl['response']['status'] = 'failure';
						                  $tmpl['response']['message'] = 'Insufficient balance to complete request.';
						         	}	
								}	
							} 
						echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
					}		
	}

}
