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
class ccTransaction {

	function getTransaction($masterCntrl, $action, $apikey, $l, $v, $a){
   				 $tmpl = array();
				if($masterCntrl['disable_transaction'] == 0){
						$coin = $v->getAndValidateCoin();
						$trans = $v->getAndValidateTrans();
						if(isset($coin) && isset($trans)){
							$transaction = $a->getTransaction($trans, $coin);
							    $tmpl['response']['status'] = 'success';
								$tmpl['response']['tranid'] = $transaction['tranid'];
								$tmpl['response']['coin_code']= $transaction['coin_code'];
								$tmpl['response']['sender'] = $transaction['sender'];
								$tmpl['response']['receiver'] = $transaction['receiver'];
								$tmpl['response']['amount'] = $transaction['amount'];
								$tmpl['response']['status'] = $transaction['status'];
								$tmpl['response']['timestamp'] = $transaction['timestamp'];
                        } else {
                            $l->ccLog('gettransaction: Arguments coin or trans could not be validated');
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Arguments coin or transaction could not be validated';
                        }
                    } else {
                        $l->ccLog('gettransaction: gettransaction is disabled');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
	}

}

?>
