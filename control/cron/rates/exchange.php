<?php

function getExchange($type, $index){
	/// Exchange Url + Coin Key
	$exchange = array();	
	$exchange[1]['url'] = "http://pubapi.cryptsy.com/api.php?method=singlemarketdata&marketid=";
	$exchange[1]['name'] = "cryptsy";
	$exchange[2]['url'] = "http://pubapi2.cryptsy.com/api.php?method=singlemarketdata&marketid=";
	$exchange[2]['name'] = "cryptsy";  /* 'POT', 'marketid' => '173', 'DOGE','marketid' => '132', 'SYS','marketid' => '278', 'XC','marketid' => '210', 'CLOAK', 'marketid' => '227', */
	$exchange[3]['url'] = "https://www.bitstamp.net/api/ticker/";
	$exchange[3]['name'] = "bitstamp";  /// BTC
	$exchange[4]['url'] = "https://bittrex.com/api/v1.1/public/getmarketsummary?market=btc-";
	$exchange[4]['name'] = "bittrex";   /// NEOS, SDC, FIBRE, LXC, MAX
	$exchange[5]['url'] = "https://btc-e.com/api/2/ltc_btc/ticker";
	$exchange[5]['name'] = "btc-e";  // LTC

	/////FIAT
	$fiat[1]['url'] = "http://www.freecurrencyconverterapi.com/api/v2/convert?q=USD_GBP&compact=y";   /// USD to GBP
	$fiat[1]['name'] = "free_currency";
	$fiat[2]['url'] = "http://www.freecurrencyconverterapi.com/api/v2/convert?q=USD_CAD&compact=y";   /// USD to CAD
	$fiat[2]['name'] = "free_currency";
	///////////////////////////////


	$url = array();
	$count = 1; // exchange counter
	
	if($type == "crypto"){
		foreach($exchange as $ex){
				if($count == $index){
					$url['url'] = $exchange[$count]['url'];
					$url['name'] = $exchange[$count]['name'];
					return $url; 
				}
			$count++;
		}
	}else{
		foreach($fiat as $ft){
			if($count == $index){
					$url['url'] = $fiat[$count]['url'];
					$url['name'] = $fiat[$count]['name'];
					return $url; 
				}
			$count++;
		}
	}
}

?>
