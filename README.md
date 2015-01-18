api
===


mkdir /var/www/api 
copy contents of repo to new directory
cd /var/www/api && git submodule init && git submodule update

Fill in Worker API key, port, and site hot wallet(drupal account), in ./config/ccapiconfig.php (create API key, within worker menu).
Fill in site coins, validation codes, txfees, min/max, in ./config/CoinsAndActions.php, add those coins RPC connections to worker.
Fill in api database login in ./config/dbconfig.php
Fill in rates database config in ./control/cron/rates/ratesconfig.php

Add your SMTP info, for all email validations, to ./control/actions/ccEmail.php ln 59 and 62


Hot Wallet Instructions
========================

1) you need to ensure account signups by anon users is enabled (site-> configuration), login as admin

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

sudo crontab -e

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
