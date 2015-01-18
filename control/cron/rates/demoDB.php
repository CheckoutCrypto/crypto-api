<?php

Class demoDb {

    
   function connectDemoDrupDb() {
        include_once('ratesconfig.php');
        $r = new rDbConfig();
        $rDbConfig = $r->config();
        $rDb = new PDO($rDbConfig['driver'].":host=".$rDbConfig['host'].";dbname=".'oc_cc', $rDbConfig['username'], $rDbConfig['password']);
        return $rDb;
    }

    function connectDemoOpenCDb() {
        include_once('ratesconfig.php');
        $r = new rDbConfig();
        $rDbConfig = $r->config();
        $rDb = new PDO($rDbConfig['driver'].":host=".$rDbConfig['host'].";dbname=".'uc_cc', $rDbConfig['username'], $rDbConfig['password']);
        return $rDb;
    }

    function connectDemoWP_Db() {
        include_once('ratesconfig.php');
        $r = new rDbConfig();
        $rDbConfig = $r->config();
        $rDb = new PDO($rDbConfig['driver'].":host=".$rDbConfig['host'].";dbname=".'wp_cc', $rDbConfig['username'], $rDbConfig['password']);
        return $rDb;
    } 

    function setDemoRates($coin_code,$coin_rate) {
        try {
            $rDb = $this->connectDrupDb();
            $stmt = $rDb->prepare("UPDATE cc_coin SET coin_rate = :coin_rate  WHERE coin_code = :coin_code LIMIT 1" );

            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->bindValue(':coin_rate', $coin_rate, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            echo $e;
        }

        try {
            $rDb = $this->connectDemoOpenCDb();
            $stmt = $rDb->prepare("UPDATE cc_coin SET coin_rate = :coin_rate  WHERE coin_code = :coin_code LIMIT 1" );

            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->bindValue(':coin_rate', $coin_rate, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            echo $e;
        }

        try {
            $rDb = $this->connectWP_Db();
            $stmt = $rDb->prepare("UPDATE cc_coin SET coin_rate = :coin_rate  WHERE coin_code = :coin_code LIMIT 1" );

            $stmt->bindValue(':coin_code', $coin_code, PDO::PARAM_STR);
            $stmt->bindValue(':coin_rate', $coin_rate, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (exception $e) {
            echo $e;
        }

        return false;
    }
}

?>
