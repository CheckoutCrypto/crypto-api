<?php

class ccRefreshCoins {
	
	function getRefreshCoins($masterCntrl, $action, $apikey, $l, $v, $a){
					$tmpl = array();
					if($masterCntrl['disable_all_coins'] == 0){
						$userid = $a->getApiUserID($apikey);
						$coins = $a->getEnabledCoins($userid);
						if(isset($coins)){
							$rate = array();
 							$tmpl['response']['status'] = 'success';
							$count = 0;
							foreach($coins as $cn){

								$code = $cn['coin_code'];
								$rate = $a->getRates($code);
								$image = $a->getCoinImage($code);
	 							$tmpl['response']['coins']['coin_'.$count]['coin_name'] = $cn['coin_name'];
	 							$tmpl['response']['coins']['coin_'.$count]['coin_code'] = $cn['coin_code'];
	 							$tmpl['response']['coins']['coin_'.$count]['rate'] = $rate;
	 							$tmpl['response']['coins']['coin_'.$count]['coin_image'] = $image;
								$count++;
							} 
                        } else {
                            $l->ccLog('refreshcoins: coin could not be validated');
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Argument coin could not be validated.';
                        }
                    } else {
                        $l->ccLog('refreshcoins: refreshcoins is disabled');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);

	}

}
