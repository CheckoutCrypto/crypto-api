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
class ccRate {

		function getRate($masterCntrl, $action, $apikey, $l, $v, $a){
					$tmpl = array();
					if($masterCntrl['disable_rate'] == 0){
						$coin = $v->getAndValidateCoin();
						$reqrate = $v->getAndValidateRate();
						if(isset($reqrate) && isset($coin)) {
						    $rates = $a->getRates($coin);
						    if(isset($rates)) {
						        $tmpl['response']['status'] = 'success';
						        $tmpl['response']['rates'][strtoupper($reqrate).'_'.strtoupper($coin)] = $rates;
                            } else {
                                $l->ccLog('getrate: No rates were returned from server');
                            }
                        } else {
                            $l->ccLog('getrate:  Arguments coin or rate could not be validated');
                            $tmpl['response']['status'] = 'failure';
                            $tmpl['response']['message'] = 'Invalid argument for coin or rate';
                        }
                    } else {
                        $l->ccLog('getrate: getrate is disabled');
                    }
				echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);

		}
}

?>
