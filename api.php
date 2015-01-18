<?php

include('./control/validate/ccapi_validate.php');
include('./control/actions/ccapi.php');
include('./log/api_log.php');
#error_reporting(E_ALL | E_STRICT);  
#ini_set('display_startup_errors',1);  
ini_set('display_errors',0);
error_reporting(~0); ini_set('display_errors', 0);

$tmpl = array();
if(!empty($_GET)) {
		/// INIT Global
		$masterCntrl = array();
		$l = new ccApiLog('api');
		$v = new ccValidate();
		$a = new ccApi();

		$apikey = $v->getAndValidateApi();
        $valid = $a->validateApiRequest($apikey);
		$masterCntrl = $a->getMasterCntrl();

        if($valid === true && !$masterCntrl['disable_worker'] && !$masterCntrl['disable_all_coins']) {
			$action = $v->getAndValidateAction();
			if(isset($action)){
				//// Get Balance by account
				switch($action){
				case "getbalance":
					include('./control/actions/ccBalance.php');
					$c = new ccBalance();
					$c->getBalance($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Transaction By TransID
				case "gettransaction":
					include('./control/actions/ccTransactions.php');
					$t = new ccTransaction();
					$t->getTransaction($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Received By Address
				case "getreceivedbyaddress":
					include('./control/actions/ccReceived.php');
					$r = new ccReceive();
					$r->getReceived($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// get info about any single generated address and its value
				case "getaddressinfo":
					include('./control/actions/ccAddress.php');
					$cc = new ccAddress();
					$cc->getAddressInfo($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Rate
				case "getrate":
					include('./control/actions/ccRate.php');
					$cc  = new ccRate();
					$cc->getRate($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Order Status
				case "getstatus":
					include('./control/actions/ccStatus.php');
					$cc = new ccStatus();
					$cc->getStatus($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Send To (withdrawal)
				case "send":
					include('./control/actions/ccSend.php');
					$cc = new ccSend();
					$cc->getSend($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				/// Email crypto to (withdrawal, limited)
				case "sendfunds":
					include('./control/actions/ccSendFunds.php');
					$cc = new ccSendFunds();
					$cc->getSendFunds($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get New Address
				case "getnewaddress":
					include('./control/actions/ccGenAddress.php');
					$cc = new ccGenAddress();
					$cc->getGenAddress($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				/// refresh worker cache, a key cache variable changed on front
				case "refreshworker":
					include('./control/actions/ccControl.php');
					$cc = new ccControl();
					$cc->getRefreshWorker($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				/// perform service charge for a package based on billing cycle
				case "servicecharge":
					include('./control/actions/ccControl.php');
					$cc = new ccControl();
					$cc->getServiceCharge($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get All enabled coin names, rates, images
				case "refreshcoins":
					include('./control/actions/ccRefreshCoins.php');
					$cc = new ccRefreshCoins();
					$cc->getRefreshCoins($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Trade Order Status
				case "gettradestatus":
					include('./control/actions/ccStatus.php');
					$cc = new ccStatus();
					$cc->getTradeStatus($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Trade Address
				case "gettradeaddress":
					include('./control/actions/ccTradeAddress.php');
					$cc = new ccTradeAddress();
					$cc->getTradeAddress($masterCntrl, $action, $apikey, $l, $v, $a);
				break;
				//// Get Trade Received - confirm deposit
				case "gettradereceived":
					include('./control/actions/ccTradeReceived.php');
					$cc = new ccTradeReceive();
					$cc->getReceived($masterCntrl, $action, $apikey, $l, $v, $a);
				break;  
				}
            } else {
                $l->ccLog('core: action could not be validated');
            }  
        } else {
            $tmpl['response']['status'] = 'failure';
            $tmpl['response']['message'] = 'Invalid API key';
           $l->ccLog('core: Invalid api key');
   		 echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
        } 
    } else {
        $tmpl['response']['status'] = 'failure';
        $tmpl['response']['message'] = 'No API key in arguments';
     ///   $l->ccLog('core: no API key supplied');
   		 echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
    } 

?>
