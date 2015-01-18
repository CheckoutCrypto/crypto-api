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
require('cgResponse.php');

class ccReceive {

	function getReceived($masterCntrl, $action, $apikey, $l, $v, $a){
		$thread = "work";
		$respDisp = new cgResponse();
		$tmpl = array();
		$coin = $v->getAndValidateCoin();
		$address = $v->getAndValidateAddress($coin);

		if(isset($coin) && isset($address)){
			$maxConfirm = $a->getCoinMaxConfirm($coin);
			$confirms = $v->getAndValidateConfirms($maxConfirm);
			if(isset($confirms)){  /// check if wallet has been confirmed already (we don't want that added twice)
				$walletConfirmed = $a->checkReceivedByAddress($address, $coin);
				if(isset($walletConfirmed)){
					if($walletConfirmed == false || $walletConfirmed < $confirms ){
						$uid = $a->getApiUserID($apikey);
						$gid = $a->getGrpID($uid);
						$amount = 0;
						$orderid = $a->getReceivedByAddress($uid, $gid, $address, $coin, $amount, $confirms, $thread);
						sleep(1);
	        				$response = $a->workOrderStatusQuery($apikey, $orderid); //checkif order exists
				        	if($response['status'] == 'success') {
					           	$address = $a->getGeneratedWalletQuery($orderid, $apikey);
						        $result = $a->getAddressBalanceQuery($orderid, $apikey, $address);
							$respDisp->display("receive", "complete_once", array('status' => $response['status'], 'balance' => $result['balance']));
						}else{
							$respDisp->display("receive", "pending", array('status' => $response['status'], 'queue_id' => $orderid));
						}
					}else{
		                                $balance = $a->getWalletBalance($address, $coin);
						$respDisp->display("receive", "complete_alrdy", array('status' => 'confirmed', $balance['balance'], 'pending' => $balance['pending'], 'fee' => $balance['fee']));
					}
				}
			}
                    } else {
                        $l->ccLog('getreceivedbyaddress: Arguments coin, address, amount or confirms could not be validated');
                        $respDisp->display("receive", "validation", array());
                    }
	}
}

?>
