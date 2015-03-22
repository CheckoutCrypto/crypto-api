#/bin/bash

sed -i "/$itm['host']/ s/127.0.0.1/$MYSQL_PORT_3306_TCP_ADDR/" /var/www/html/config/dbconfig.php
sed -i "s/test/root/g" /var/www/html/config/dbconfig.php 
sed -i "s/default/$MYSQL_ENV_MYSQL_ROOT_PASSWORD/g" /var/www/html/config/dbconfig.php


/usr/sbin/apache2ctl -D FOREGROUND
