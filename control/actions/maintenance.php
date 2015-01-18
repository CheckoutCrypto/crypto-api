<?php

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
