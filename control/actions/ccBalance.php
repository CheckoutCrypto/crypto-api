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
