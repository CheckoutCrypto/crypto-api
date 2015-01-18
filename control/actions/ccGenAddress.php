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
