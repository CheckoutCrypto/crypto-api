<?php

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
