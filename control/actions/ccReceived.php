<?php
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
