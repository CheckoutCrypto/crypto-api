<?php

function apiRequest($url, $exchangeKey, $coin, $maxSell, $maxBuy) {
	$tmp = array();
	if($url['name'] == "cryptsy"){
		$apiurl = $url['url'] . $exchangeKey;
	}else if($url['name'] == "bittrex"){
		$apiurl = $url['url'] . $coin;
	}else{
		$apiurl = $url['url'];
	}
	$scheme = parse_url($apiurl);
	$scheme = $scheme['scheme'];
	$delay = stream_context_create(array($scheme => array('timeout' => 5)));
	$response = file_get_contents($apiurl);
	// if cryptsy goes down
	if(empty($response)){
		var_dump('no response!');
	}
	$result = $response;
    $tmp = json_decode($response,true);
    $final = array();
	if($url['name'] == "free_currency"){
			$final['rate'] = number_format((float)$tmp['USD_'.$coin]['val'],8, '.', ''); 		
	}else{
		/*
		*  Bitstamp
		*/
		if($url['name'] == "bitstamp"){
			$final['high'] = $tmp['high'];
			$final['low'] = $tmp['low'];
			$final['last'] = $tmp['last'];
			$final['avg'] = $tmp['vwap'];
			$final['vol'] = $tmp['volume'];
			$final['bid'] = $tmp['bid'];
			$final['ask'] = $tmp['ask'];
		/*
		*  BTC-e
		*/
		}else if($url['name'] == "btc-e"){
			$final['sell'] = number_format((float)$tmp['ticker']['high'], 8, '.', '');
			$final['buy'] = number_format((float)$tmp['ticker']['low'], 8, '.', '');
			$final['last'] = number_format((float)$tmp['ticker']['last'], 8, '.', '');
			$final['avg'] = number_format((float)$tmp['ticker']['avg'], 8, '.', '');
		/*	$final['buy'] = number_format((float)$tmp['ticker']['buy'], 8, '.', '');
			$final['sell'] = number_format((float)$tmp['ticker']['sell'], 8, '.', '');
			$final['vol'] = number_format((float)$tmp['ticker']['vol'], 8, '.', '');
			$final['vol_cur'] = number_format((float)$tmp['ticker']['vol_cur'], 8, '.', ''); */
		/*
		*  Bittrex
		*/
		}else if($url['name'] == "bittrex"){  
			$final['sell'] = number_format((float)$tmp["result"][0]['High'], 8, '.', '');
			$final['buy'] = number_format((float)$tmp["result"][0]['Low'], 8, '.', '');
			$final['last'] = number_format((float)$tmp["result"][0]['Last'], 8, '.', '');
			$final['avg'] = number_format((float)$tmp["result"][0]['Ask'], 8, '.', '');
			$final['vol'] = number_format((float)$tmp["result"][0]['Volume'],8, '.', ''); 
		/*
		*  Cryptsy
		*/
		}else if($url['name'] == "cryptsy" ){
			echo "MAX SELL DEPTH = ". $maxSell;
			echo "MAX BUY DEPTH = ". $maxBuy;
			$count = 0;		
			$total = 0;	
			/// get all orders adding to the max sell depth
			foreach($tmp['return']['markets'][$coin]["sellorders"] as $market){	
				if($total < (float)$maxSell){ 			
					$total = $total + $market['total'];
					if($total >= (float)$maxSell){
						$final['sell'] = $market["price"];
					}
				}else{
					$count++;
				}
			}
			$count = 0;
			$total = 0;	
			/// get all orders adding to the max buy depth
			foreach($tmp['return']['markets'][$coin]["buyorders"] as $market){	
				if($total < (float)$maxBuy){ 			
					$total = $total + $market['total'];
					if($total >= (float)$maxBuy){
						$final['buy'] = $market["price"];
					}
				}else{
					$count++;
				}
			}
			$final['avg'] = $tmp['return']['markets'][$coin]['lasttradeprice'];
		}
	}
	$result = $final;
			
    if($result) {
         return $result;
    } 
        return false;
}

?>
