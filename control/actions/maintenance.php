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
Class cgMaintenance {

    function connectDb() {
        include_once('./config/dbconfig.php');
        $c = new ccDbConfig();
        $ccDbConfig = $c->config();
        $ccDb = new PDO($ccDbConfig['driver'].":host=".$ccDbConfig['host'].";dbname=".$ccDbConfig['database'], $ccDbConfig['username'], $ccDbConfig['password']);
        return $ccDb;
    }
	/* Remove old/unused work orders */
   function cleanOrders(){
       $ccDb = $this->connectDb();
       $stmt = $ccDb->prepare("SELECT basic_id from ccdev_work_orders WHERE timestamp <= DATE_ADD(NOW(), INTERVAL -30 MINUTE)");
       $stmt->execute();
       $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       if(is_array($rows)) {
           foreach($rows as $row){
               $stmt2 = $ccDb->prepare("DELETE FROM ccdev_work_orders WHERE basic_id = :id");
               $stmt2->bindValue(':id', $row['basic_id'], PDO::PARAM_INT);
               $stmt2->execute();
           }
       }
   }

	/* Remove old/unused wallets */
	function cleanTrades(){
	$ccDb = $this->connectDb();
       $stmt = $ccDb->prepare("SELECT basic_id from ccdev_trades WHERE timestamp <= DATE_ADD(NOW(), INTERVAL -24 HOUR) AND status = :status");
            $stmt->bindValue(':status', 0, PDO::PARAM_INT);
       $stmt->execute();
       $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       if(is_array($rows)) {
           foreach($rows as $row){
               $stmt2 = $ccDb->prepare("DELETE FROM ccdev_trades WHERE basic_id = :id");
               $stmt2->bindValue(':id', $row['basic_id'], PDO::PARAM_INT);
               $stmt2->execute();
           }
       }
	}

	/* Remove old/unused wallets */
	function cleanWallets(){
	$ccDb = $this->connectDb();
       $stmt = $ccDb->prepare("SELECT basic_id from ccdev_wallets WHERE timestamp <= DATE_ADD(NOW(), INTERVAL -60 MINUTE) AND balance_total = :balance AND pending_total = :pending");
           $stmt->bindValue(':pending', 0, PDO::PARAM_INT);
            $stmt->bindValue(':balance', 0, PDO::PARAM_INT);
       $stmt->execute();
       $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

       if(is_array($rows)) {
           foreach($rows as $row){
               $stmt2 = $ccDb->prepare("DELETE FROM ccdev_wallets WHERE basic_id = :id");
               $stmt2->bindValue(':id', $row['basic_id'], PDO::PARAM_INT);
               $stmt2->execute();
           }
       }
	}
}

?>
