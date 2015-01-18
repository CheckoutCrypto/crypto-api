<?php
require('cgResponse.php');

class ccGenAddress {
	
	function getGenAddress($masterCntrl, $action, $apikey, $l, $v, $a){
		$thread = "work";
		$respDisp = new cgResponse();
		$tmpl = array();
		if($masterCntrl['disable_getnewaddress'] == 0){
			$coin = $v->getAndValidateCoin();
			if(isset($coin)) { 
				 $args['coin'] = $coin;
				 $args['gid'] = 0;
				 $result = $a->addWorkOrderQuery($apikey, $action, $args, true, $thread);
				 sleep(1);
				 $response = $a->workOrderStatusQuery($apikey, $result); //checkif order exists/completed
				 if($response['status'] == 'success') {
				        $address = $a->getGeneratedWalletQuery($result, $apikey, $coin);
					$respDisp->display("address", "newaddress", array('status' => $response['status'], 'address' => $address));
				}
                        } else {
                            $l->ccLog('getnewaddress: coin could not be validated');
			    $respDisp->display("address", "validation", array());
                        }
                } else {
                     $l->ccLog('getnewaddress: getnewaddress is disabled');
		   $respDisp->display("address", "error_api", array());
                 }
	}
}
