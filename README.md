api
===

```
mkdir /var/www/api 
copy contents of repo to new directory
cd /var/www/api && git submodule init && git submodule update
```

in ./config/ccapiconfig.php
Fill in Worker:
<ol><li> API key(create API key, within worker menu). </li>
<li>port </li>
<li>site hot wallet(drupal account), </li></ol>

in ./config/CoinsAndActions.php 
Fill in site:
<ul><li>coins, </li>
<li>validation codes, </li>
<li>txfees, </li>
<li>min/max, </li></ul>

<ol><li>Add each coin's RPC login within the worker menu.</li>
<li>Fill in api database login in ./config/dbconfig.php</li>
<li>Fill in rates database config in ./control/cron/rates/ratesconfig.php</li>
<li>Add your SMTP info for all email validations to ./control/actions/ccEmail.php ln 59 and 62</li></ol>


<h3>Hot Wallet Instructions</h3>
<ol><li>You need to ensure account signups by anon users is enabled (site-> configuration), login as admin </li>

<li>Register a new account, (use a lengthy user, pass), write it down, http://10.0.1.10/site/user/register </li>

<li>Verify account, by email </li>

<li>Create a password, set basic user settings.  Leave email as 2fa option </li>

<li>Enable all coins, http://10.0.1.10/site/Coin , go coin by coin and hit "manage" a popup comes up, hit "enabled". </li>

<li>Visit Account Dashboard http://10.0.1.10/site/Account copy your new user's API key </li>

<li>Place your apikey in here, instead of the current API key http://10.0.1.11/api/api.php?apikey=404df6fe955060d799b0782d879c783c5909e3f1&action=getnewaddress&coin=BTC  Change the coin for any coin you wish to deposit e.g. BTC, LTC, POT, etc </li>

<li>
```
./bitcoind sendfrom youraccount depositwalletaddress 
```
</li>
<li>
```
a) ssh/browse into your API server
b) cd /var/www/api/config
c) nano ccapiconfig.php

change:  $adminid = '1';
```
The number should be the drupal user id you just created. See below for how to find that id.
</li>

<li>go to phpymyadmin http://10.0.1.12/phpmyadmin, login as root, and browse site database.</li>

<li>look for and go to, 'users' table.  Look for the name and ID for the drupal user you just created, refer back to Step 9c.  change the $adminid to the drupal userid and save.</li>


Add Scheduled cron tasks:
========================
```
sudo crontab -e
```

copy and paste the following:
```
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
