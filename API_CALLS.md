---
title: API Reference

language_tabs:
  - shell

toc_footers:
  - <a href='#'>Sign Up for a Developer Key</a>
  - <a href='http://github.com/tripit/slate'>Documentation Powered by Slate</a>

includes:
  - errors

search: true
---

# Introduction

Welcome to the CheckoutCrypto API! You can use our API to access CheckoutCrypto API endpoints, which can get information on various wallets, balances, transactions, rates, in our database.

We have language bindings in Shell! You can view code examples in the dark area to the right, and you can switch the programming language of the examples with the tabs in the top right.

# Authentication

> To authorize, use this code:

```shell
# With shell, you can just pass the correct param with each request
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY"
```

> Make sure to replace `YOUR_KEY` with your API key.

CheckoutCrypto uses API keys to allow access to the API. You can register a new CheckoutCrypto API key at our [developer portal](https://checkoutcrypto.com/user/register).

CheckoutCrypto expects for the API key to be included in all API requests to the server in a header that looks like the following:

`https://api.checkoutcrypto.com/?apikey=YOUR_KEY`

<aside class="notice">
You must replace `YOUR_KEY` with your personal API key.
</aside>
# General
## Get Balance

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getbalance&coin=YOUR_COIN"
```
> Make sure to replace `YOUR_KEY` with your API key and `YOUR_COIN` with your coin code for your account.

> The above command returns JSON structured like this:

```json
{
	"response":
	{
		"status":"success",
		"balance":"0.00000000"
	}
}

```

This endpoint retrieves a user's balance, for any coin, using this call.

### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getbalance`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The name of the coin we want to generate from or utilize.

## Get Transaction

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=gettransaction&coin=YOUR_COIN&tranid=YOUR_TRANID"
```
> Make sure to replace `YOUR_KEY` with your API key and `YOUR_COIN` with your coin code for your account, aswell as `YOUR_TRANID` with your txid.

> The above command returns JSON structured like this:

```json
{
	"response":
	{
		"status":"success",
		"balance":"0.00000000"
	}
}

```

This endpoint retrieves a user's balance, for any coin, using this call.

### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=gettransaction`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The name of the coin we want to generate from or utilize.
txid | n/a | The txid generated from a withdrawal.

## Get Rate

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getrate&coin=YOUR_COIN&rate=USD"
```
> Make sure to replace `YOUR_KEY` with your API key and `YOUR_COIN` with your coin code for your account.

> The above command returns JSON structured like this:

```json
{
	"response":
	{
		"status":"success",
		"rates":
		{
			"USD_BTC":"353.99000000"
		}
	}
}

```

This endpoint retrieves a specific coin rate for a user's enabled coins.

### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getrate`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The name of the coin we want to generate from or utilize.
rate | n/a | The name of the currency we want to return e.g. USD

## Refresh Coins

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=refreshcoins"
```
> Make sure to replace `YOUR_KEY` with your API key.

> The above command returns JSON structured like this:

```json
{
	"response":{
		"status":"success",
		"coins":{
			"coin_0":{
				"coin_name":"potcoin",
				"coin_code":"POT",
				"rate":"0.00246801",
				"coin_image":"http://downloads.checkoutcrypto.com/images/coins/potcoin.png"
			},
			"coin_1":{
				"coin_name":"Bitcoin",
				"coin_code":"BTC",
				"rate":"328.63000000",
				"coin_image":"http://downloads.checkoutcrypto.com/images/coins/bitcoin.png"
			}
		}
	}
}

```

This endpoint retrieves all available coins, rates, thumbnails.

### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=refreshcoins`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.

# Deposits

## Get NewWalletAddress

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getnewaddress&coin=YOUR_COIN"
```

> Make sure to replace `YOUR_KEY` with your API key and `YOUR_COIN` with your preferred coin to generate a wallet from.

> The above command returns JSON structured like this:

```json
{
	"response":{
		"status":"success",
		"address":"1AKBivy121nuAe2Dg1PoYcFimXvaK8KdCR"
	}
}
```

This endpoint retrieves a queueid for the wallet address, you must follow retrieve the result via a getstatus call.

### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getnewaddress`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The name of the coin we want to generate from or utilize.


## Get ReceivedByAddress

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getreceivedbyaddress&coin=YOUR_COIN&address=GEN_ADDRESS&confirms=CONFIRM_AMT"
```
> Make sure to replace `YOUR_KEY` with your API key and `YOUR_COIN` with your preferred coin to generate a wallet from. Also don't forget to replace `GEN_ADDRESS` with the address generated from getnewaddress and `CONFIRM_AMT` with a confirm number less than max confirm for that coin (see Min/Max Confirmation overview for more information).

> The above command returns JSON structured like this:

```json
{   == if pending ==
	"response":{
		"status":"success",
		"queue_id":"142"
	}
  == if confirmed once ==
	"response":{
		"status":"success",
		"pending":"0.01"
	}
  == if confirmed max ==
	"response":{
		"status":"success",
		"pending": "0.0000000"
		"balance":"0.01"
		"fee":"0.001"
	}
}
```

This endpoint retrieves the queued order for a getnewaddress call.


### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=getreceivedbyaddress`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The name of the coin we want to generate from or utilize.
address | n/a | The wallet address of the coin we want to received deposit from or utilize.
confirms | n/a | The number of confirmations required, less than max confirm for that coin. (see min/max confirm overview for more information).

# Trading
## Get NewTradeAddress
```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=gettradeaddress&coin=YOUR_COIN&amount=YOUR_AMT&coin_trade=YOUR_TRADECOIN&address=YOUR_RECEIVEADDRESS&ignore_amt=TRUE&amt_type=YOUR_AMOUNTTYPE"
```

> Make sure to replace `YOUR_KEY` with your API key, `YOUR_COIN` with your preferred coin to generate a wallet from,`YOUR_AMOUNTTYPE` with your preferred amount input, to generate a wallet.

> The above command returns JSON structured like this:

```json
 {
	"response":{
		"status":"success",
		"deposit_coin":"POT",
		"deposit_amt":101,
		"receive_coin":"BTC",
		"receive_amt":0.000985,
		"address":"PD8KrKnxNZmJfB3F8kEWa5tDnKHzrLPBoo"
	}
}
```

This endpoint retrieves a queueid for the wallet address, you must follow it by a gettradestatus call.

### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=gettradeaddress`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The acronym of the coin we want to deposit with.
coin_trade | n/a | The acronym of the coin we expect to receive.
amount | n/a | The amount you want to deposit from a single coin
address | n/a | The valid coin address you expect to receive from
ignore_amt | n/a | ignore the actual deposit amount versus the expected amount, exchange regardless (set false to force amount depost is equal to expected amount)
amt_type | n/a | The type of amount you entered e.g. USD/BTC/LTC

## Get TradeReceived

```shell
curl "https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=gettradereceived&coin=YOUR_COIN&address=GEN_ADDRESS&confirms=CONFIRM_AMT"
```
> Make sure to replace `YOUR_KEY` with your API key and `YOUR_COIN` with your preferred coin to generate a wallet from. Also don't forget to replace `GEN_ADDRESS` with the address generated from getnewaddress and `CONFIRM_AMT` with a confirm number less than max confirm for that coin (see Min/Max Confirmation overview for more information).

> The above command returns JSON structured like this:

```json 
PENDING_-=no_deposit_received(hasn't_been_confirmed)
{
	"response":{
		"status":"pending",
		"description":"pending deposit",
		"balance":0,
		"pending":0
	}
}


DEPOSIT-RECEIVED_-deposit_received(waiting_for_MAX_confirm)
{
	"response":{
		"description":"balance has reached expected amount, funds transfer at maxconfirm",
		"status":"maxconfirm",
		"balance":0,
		"pending":101.0001
	}
}
```
```json
 MAXCONFIRM
{
	"response":{
		"description":"balance has reached expected amount, confirm is max, funds transfer begins",
		"status":"complete",
		"address":"1GetbDHnPWV7Efs5PAetHGZ7o2rpuCpm7A",
		"txid":"ce82460ef9d702b0d7f0bff44115a20653ed876742ea597dd7be43b67b9c30e8",
		"balance":101.0001,
		"pending":0
	}
}
```

This endpoint retrieves the queued order for a gettradereceived call.


### HTTP Request

`GET https://api.checkoutcrypto.com/?apikey=YOUR_KEY&action=gettradereceived`

### Query Parameters

Parameter | Default | Description
--------- | ------- | -----------
action | n/a | This is the name of the API call definer, basically which api call we're making.
coin | n/a | The name of the coin we want to generate from or utilize.
address | n/a | The wallet address of the coin we want to received deposit from or utilize.
confirms | n/a | The number of confirmations required, less than max confirm for that coin. (see min/max confirm overview for more information).
