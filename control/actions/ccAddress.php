<?php

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
