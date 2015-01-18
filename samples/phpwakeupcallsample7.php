<?php
$host="127.0.0.1";
$port="12311";
// $msg="AwJTYL/AbBxCFt+EWSyV9auSLhav-cache";
$msg="AwLHxHCO44rkgx2VnnQXbg==-mobile";
$len = strlen($msg);

//var-_dump($bytes);
  // Open a socket
$fp = fsockopen($host, $port, $len);
if (!$fp) {
    echo "ERROR: $errno - $errstr<br />\n";
} else {
    fwrite($fp, $msg);
    echo fread($fp, 26);
    fclose($fp);
}
  echo "I've finished!";
?>
