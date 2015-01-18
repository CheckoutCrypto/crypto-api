<?php

Class ccApiConfig {

    function ccApiWorkerKey() {
        $apikey = "";
        return $apikey;
    }

    function ccApiWorkerServer() {
        $workerserver = '127.0.0.1';
        return $workerserver;
    }

	function ccApiWorkerPort(){
        $port='12311';
		return $port;
	}

	function getAdminID(){
		$adminid = '1';
		return $adminid;
	}

}
?>
