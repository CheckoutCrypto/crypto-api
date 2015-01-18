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
