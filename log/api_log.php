<?php

Class ccApiLog {

    function __construct($type = FALSE) {
        $this->type = $type;
    }

    function ccLog($args) {
        if($this->type == 'cron') {
            $path = "/var/log/daemons/api.log";
	    $ip = '127.0.0.1';
        } else {
            $path = "/var/log/daemons/api.log";
	    $ip = $_SERVER['REMOTE_ADDR'];
        }

        $date = date("Y-m-d H:i:s", time());
        $msg = "$date $args FROM $ip \r\n";

        try {
            file_put_contents($path, $msg, FILE_APPEND);
        } catch (exception $e) {
            //var_dump($e);
        }
    }

}
