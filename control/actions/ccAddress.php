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
class ccAddress {

		function getAddressInfo($masterCntrl, $action, $apikey, $l, $v, $a){
					$tmpl = array();
					$coin = $v->getAndValidateCoin();
					$address = $v->getAndValidateAddress($coin);

					if(isset($coin) && isset($address)){
						$maxConfirm = $a->getCoinMaxConfirm($coin);
						$confirms = $v->getAndValidateConfirms($maxConfirm);
						if(isset($confirms)){
							$receivedby = $a->getAddressInfo($address, $coin, $confirms);
						 	$tmpl['response']['status'] = 'success';
							$tmpl['response']['walletaddress'] = $receivedby['walletaddress'];
							$tmpl['response']['coins_enabled']= $receivedby['coins_enabled'];
							$tmpl['response']['balance_total']= $receivedby['balance_total'];
							$tmpl['response']['pending_total'] = $receivedby['pending_total'];
							$tmpl['response']['fee_total'] = $receivedby['fee_total'];
							$tmpl['response']['orderid'] = $receivedby['orderid'];
							$tmpl['response']['confirm'] = $receivedby['confirm'];
							$tmpl['response']['timestamp'] = $receivedby['timestamp'];
						}
              } else {
                   $l->ccLog('getaddressinfo: Arguments coin, address or confirms could not be validated');
                   $tmpl['response']['status'] = 'failure';
                   $tmpl['response']['message'] = 'Arguments coin, address or confirms could not be validated';
             }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
		}

}
