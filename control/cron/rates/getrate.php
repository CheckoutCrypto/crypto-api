<?php

include 'rates.php';
include 'exchange.php';
include 'ratedb.php';

/// connect DB
$rDb = new ratesDb();

/// Get BTC Avg USD first
$coin = $rDb->getBtcRate();
$exchange = getExchange("crypto", $coin['exchange_id']);
$btc = apiRequest($exchange, '', $coin['coin_code'], '', $coin['sell_depth'], $coin['buy_depth']);
$rDb->setRates($coin['coin_code'], $btc); 

/// Get All Coins and exchanges
$coins =  $rDb->getAllCoinExchanges();
foreach($coins as $coin){
	$exchange = getExchange("crypto", $coin['exchange_id']);
	$result = apiRequest($exchange,  $coin['exchange_spec'], $coin['coin_code'], $coin['sell_depth'], $coin['buy_depth']);
	$result['usd'] = number_format((float)($result['avg'] * $btc['avg']), 8, '.', '');
	echo "COIN = ". $coin['coin_code'];	
	$rDb->setRates($coin['coin_code'], $result); 
} 

/// Get All Fiats and exchanges
$fiats =  $rDb->getAllFiatExchanges();
foreach($fiats as $ft){
	$exchange = getExchange("fiat", $ft['exchange_id']);
	$result = apiRequest($exchange, '', $ft['coin_code'], '', '', '');
	$rDb->setFiatRates($ft['coin_code'], $result['rate']);
}  
?>
