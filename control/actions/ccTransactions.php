<?php

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
