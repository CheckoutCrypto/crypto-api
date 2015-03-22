CheckoutCrypto API
==================

The source code for the public + private API allows the site, merchants, developers, to connect to the worker backend.  This is the middleman between the backend management(worker), and the public(site, developers, etc).
 

###Dependencies
You must have the CheckoutCrypto drupal site database installed. Which means you must install drupal with these modules: https://github.com/CheckoutCrypto/site enabled and installed.

required:  ccAccount, ccAdmin, ccBalance ccCoins, ccGroups, ccOTP, ccService, ccWallets, ccTransactions, ccWorker, cgTrading,

All the above are preinstalled in the CheckoutCrypto site docker image

specific api requirements:
apache, php5, php5-mysql, php5-curl, curl, git 

## Docker
### Required Containers
Run MySQL daemon container with mysql-server (exposed port 3306)

```
docker run --name mysql -e MYSQL_ROOT_PASSWORD=somepass -d mysql
```

Run PHPMyAdmin daemon container with mysql connection(exposed port 80 mapped to 81)

```
docker run -d --link mysql:mysql -e MYSL_USERNAME=root --name phpmyadmin -p 81:80 corbinu/docker-phpmyadmin
```

###API Usage Server

```
 docker run -d -it -p 83:80 --name api --link mysql:mysql checkoutcrypto/crypto-api
```

###API Single usage

```
 docker run -it -p 83:80 --name api --link mysql:mysql checkoutcrypto/crypto-api
```

###API Container Access

```
 docker exec -it api /bin/bash
```

###Post-installation

API is preset to boot with environment variables supplied by the mysql container above. You must link a mysql container such as the one above, with accompanying variables in order to set the database correctly.

The last api setup steps are below. First you need a worker api key, supplied by a worker [qt](https://registry.hub.docker.com/u/checkoutcrypto/worker) [ dart](https://registry.hub.docker.com/u/checkoutcrypto/worker-dart)

- [CheckoutCrypto Drupal Site and Database](https://registry.hub.docker.com/u/checkoutcrypto/site/) Installed and configured separately.
- [CheckoutCrypto Worker](https://registry.hub.docker.com/u/checkoutcrypto/worker)
- [Bitcoin daemon](https://bitcoin.org/en/download)

[Read the repository for more API information](https://github.com/CheckoutCrypto/crypto-api/) 
[For specific api calls](https://github.com/CheckoutCrypto/crypto-api/blob/master/API_CALLS.md)


API
===
1) Fill in Worker API key, port, and site hot wallet(drupal account), in ./config/ccapiconfig.php (create API key, within worker menu). Note: Worker key is generated within the worker itself, run the worker without the -server option, hit option 3 (scroll debug output).

2) Fill in site coins, validation codes, txfees, min/max, in ccdev_coins database table. Add each coin to worker's cache. To do this run worker without -server option, hit option 2, follow instructions(hitting enter after each input).

3) Fill in api database login in ./config/dbconfig.php

4) Add your SMTP info, for all email validations, to ./control/actions/ccEmail.php ln 59 and 62.  This is necessary to validate any user who has an email OTP preference.

Coins + Rates
======

coin_name = coin literal string name

coin_code = coin acronym

coin_rate   = rate in USD

coin_rate_btc = rate in BTC

coin_rate_sell = rate at which host will sell in BTC

coin_rate_buy= rate at which host wil buy in BTC

Market_buy_depth = The depth of the market(in BTC), at which you would like to calculate the buy_rate

Market_sell_depth = The depth of the market(in BTC), at which you would like to calculate the sell_rate

coin_MxConf = The max confirmation the worker/api will accept before allowing the confirmation of the transaction.  

exchange_id = the id of the market exchange we wish to use for any given coin's default rate. To see these exact prewritten IDs, look in ./control/cron/rates/exchange.php

exchange_spec = a unique identifier for the coin market (used in conjunction with crypsy's API, could be used for others, when a market has an identifier per coin), to establish current rates/orders on the market.

coin_fee = default percentage of a fee for this coin, used for customizing coin pricing

coin_txfee = default coin amount for txfee

coin_enabled = disable a coin

min/max = allows limitations for the amount of withdraw



Hot Wallet Instructions
========================

1) you need to ensure account signups by anon users is enabled (site-> configuration), login to site as admin

2) register a new account, (use a lengthy user, pass), write it down, http://10.0.1.10/site/user/register

3) verify account, by email

4) Create a password, set basic user settings.  Leave email as 2fa option

5) Enable all coins, http://10.0.1.10/site/Coin , go coin by coin and hit "manage" a popup comes up, hit "enabled".

6) visit Account Dashboard http://10.0.1.10/site/Account copy your new user's API key

7) Place your apikey in here, instead of the current API key http://10.0.1.11/api/api.php?apikey=404df6fe955060d799b0782d879c783c5909e3f1&action=getnewaddress&coin=BTC  Change the coin for any coin you wish to deposit e.g. BTC, LTC, POT, etc

8)  ./bitcoind youraccount depositwalletaddress

9) a) ssh into API server. api@10.0.1.11
   b) cd /var/www/api/config
	c) nano ccapiconfig.php

	change:  $adminid = '1';

	The number should be the drupal user id you just created.  How do you know which userid is for who?
10) go to phpymyadmin http://10.0.1.12/phpmyadmin, login as root, and browse site database.

11) look for and go to, 'users' table.  Look for the name and ID for the drupal user you just created, refer back to Step 9c.  change the $adminid to the drupal userid and save.


Add Scheduled cron tasks:
========================
```
sudo crontab -e
```
```
copy and paste the following:
*/15 * * * * echo "Running cron" 2>&1 >> /var/log/daemons/cron.log
15,45 * * * * cd /var/www/api/ && php -f ./control/cron/rates/getrate.php 2>&1 >> /var/log/daemons/cron.log
*/11 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php pending_withdraw 2>&1 >> /var/log/daemons/cron.log
*/13 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php pending_balance 2>&1 >> /var/log/daemons/cron.log

#*/14 * * * * cd /var/www/api && php -f ./control/cron/api_cron.php autopayment 2>&1 >> /var/log/daemons/cron.log
#*/14 * * * * cd /var/www/api && php -f ./control/cron/api_cron.php service_charge 2>&1 >> /var/log/daemons/cron.log

*/15 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php checkbalance 2>&1 >> /var/log/daemons/cron.log

*/15 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php cleanwork 2>&1 >> /var/log/daemons/cron.log
*/15 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php cleancron 2>&1 >> /var/log/daemons/cron.log
*/15 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php cleantrade 2>&1 >> /var/log/daemons/cron.log
*/15 * * * * cd /var/www/api/ && php -f ./control/cron/api_cron.php cleanwallets 2>&1 >> /var/log/daemons/cron.log

# @daily bash /home/cccron/cron/backup/walletbackup.sh  /// missing script
```

See API_CALLS for specific API calls and parameters
