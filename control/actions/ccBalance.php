<?php

class ccBalance {

		function getBalance($masterCntrl, $action, $apikey, $l, $v, $a){
					$thread = "cron";
					 $tmpl = array();
					$coin = $v->getAndValidateCoin();

					if(isset($coin) ){
						if($masterCntrl['disable_getbalance'] == 0){
							$uid = $a->getApiUserID($apikey);
							if(isset($uid)){
								$balance = $a->getBalance($uid, $coin);
								$tmpl['response']['status'] = 'success';
								$tmpl['response']['balance'] = $balance;
                            } else {
                                $l->ccLog('getbalance: User ID could not be determined');
                                $tmpl['response']['status'] = 'failure';
                                $tmpl['response']['message'] = 'Internal server error';
                            }
                        } else {
                            $l->ccLog('getbalance: getbalance is disabled');
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'getbalance is disabled';
                        }
                    } else {
                        $l->ccLog('getbalance: Coin could not be validated');
                        $tmpl['response']['status'] = 'failure';
                        $tmpl['response']['message'] = 'Invalid argument for coin';
                    }	
    			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
		}
}

?>
