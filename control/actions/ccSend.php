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
class ccSend {
	
	function getSend($masterCntrl, $action, $apikey, $l, $v, $a){
		$thread = "work";
		$MAXCOUNT = 3;
		$tmpl = array();
		if($masterCntrl['disable_withdraw'] == 0){
						$coin = $v->getAndValidateCoin();
						$recip = $v->getAndValidateAddress($coin);
						$amount = $v->getAndValidateAmount();

							if(isset($recip) && isset($amount) && isset($coin)) {
								$args['recip'] = $recip;
								$args['coin'] = $coin;
								$uid = $a->getApiUserID($apikey);
                                $apidetails = $a->getOTPpref($uid);
							
								if(isset($apidetails['uid'])){
									// double check amount is within range
                                    $rangeCoin = $a->checkMinMaxCoin($coin);
									$validRange = $v->getAndValidateMinMaxAmount($rangeCoin['min_amount'], $rangeCoin['max_amount'], $amount);
                                    $args['uid'] = $apidetails['uid'];
									if($validRange == true){   
        							// $a->checkMaxTrans($apidetails['uid']);                                 
									/// determine OTP Preference
									if($apidetails['pref_otp'] == "twofa"){	
										$twofa = $v->getAndValidateTwoFactor();							
										$validTwoFact = $a->validateTwoFact($uid, $twofa);
										if($validTwoFact) {
											$balance = $a->getBalance($uid, $coin);
											$account = $a->getUserWalletByID($apikey);
											/// get account name by api key $address 
											if(isset($account)) {
												$args['account'] = $account;
												$args['gid'] = $a->getGrpID($uid);
												$validBalance = $a->validateBalance($amount, $balance, $coin);
                                                if(isset($validBalance)){
									   				$args['amount'] = $amount;
													$result = $a->addWorkOrderQuery($apikey, 'sendto', $args, true, $thread);
                                                    if($result) {

														$tmpl['response']['status'] = 'success';
														$tmpl['response']['queue_id'] = $result;
														$tmpl['response']['sent_total'] = $validBalance['sent_total'];
														$tmpl['response']['subtotal'] = $validBalance['subtotal'];
														$tmpl['response']['txfee'] = $validBalance['txfee'];
														$tmpl['response']['ccfee'] = $validBalance['ccfee'];
														$tmpl['response']['balance_remaining'] = $validBalance['balance_remaining'];
                                                    } else {
                                                        $l->ccLog('send: Invalid result from addWorkOrderQuery');
                                                    }
                                                } else {
                                                    $l->ccLog('send: Invalid balance. Insufficient balance to complete request.');
                                                    $tmpl['response']['status'] = 'failure';
                                                    $tmpl['response']['message'] = 'Insufficient balance to complete request.';
                                                }
                                            } else {
                                                $l->ccLog('send: Account could not be validated');
                                            }
										} else {
                                            $tmpl['response']['status'] = 'failure';
                                            $tmpl['response']['message'] = 'Invalid two-factor code';
                                            $l->ccLog('send: Invalid two-factor code');
										}
                                    } else {
                                        //email auth
                                        $otpConfirm = $a->getOTPConfirm($args['uid'], $args['coin']);	/// check if pending email confirm for userid if
											if($otpConfirm['valid'] == 1){  /// do regular send work order then remove OTP if its validated
                                               $dbArgs = array();
                                               $dbArgs['uid'] = $otpConfirm['uid'];
                                               $dbArgs['coin_name'] = $otpConfirm['coin_name'];
                                               $dbArgs['coin_code'] = $otpConfirm['coin_code'];
                                               $dbArgs['coin_amount'] = $otpConfirm['coin_amount'];
                                               $dbArgs['coin_address'] = $otpConfirm['coin_address'];

                                               $otpDeleted = $a->ccOTP_otp_remove($dbArgs);

                                               if($otpDeleted === TRUE) { //make sure otp is deleted first
                                                   $balance = $a->getBalance($dbArgs['uid'], $dbArgs['coin_code']);
                                                   $account = $a->getUserWalletByID($apikey);
                                                   // get account name by api key $address 
                                                   if(isset($account)) {
                                                       $args['account'] = $account;
													   $args['gid'] = $a->getGrpID($dbArgs['uid']);
                                                       $validBalance = $a->validateBalance($amount, $balance, $coin);
                                                       if(isset($validBalance)){
                                                           $args['amount'] = $amount;
                                                           $result = $a->addWorkOrderQuery($apikey, 'sendto', $args, true, $thread);
                                                           if($result) {
                                                               $tmpl['response']['status'] = 'success pending email approval';
                                                               $tmpl['response']['queue_id'] = $result;
                                                               $tmpl['response']['sent_total'] = $validBalance['sent_total'];
                                                               $tmpl['response']['subtotal'] = $validBalance['subtotal'];
                                                               $tmpl['response']['txfee'] = $validBalance['txfee'];
                                                               $tmpl['response']['ccfee'] = $validBalance['ccfee'];
                                                               $tmpl['response']['balance_remaining'] = $validBalance['balance_remaining'];
                                                            }
                                                       }
                                                   }
                                               }
											} else { // send new OTP email
                                                if($otpConfirm['sent'] < $MAXCOUNT) {
                                                    $balance = $a->getBalance($args['uid'], $args['coin']);
                                                    $account = $a->getUserWalletByID($apikey);

                                                    /// get account name by api key $address 
                                                    if(isset($account)) {
                                                        $args['account'] = $account;
                                                        $validBalance = $a->validateBalance($amount, $balance, $args['coin']);
                                                        if(isset($validBalance)) {
                                                                $otpArgs = $a->ccOTP_otp_generate();
                                                               //Generate the address to send to user via email
                                                                $url = $a->ccOTP_otp_get_auth_url($otpArgs['signature']);
                                                                $dbArgs = array();
                                                                $dbArgs['uid'] =$args['uid'];
                                                                $dbArgs['coin_name'] = $a->getCoinName($coin);
                                                                $dbArgs['coin_code'] = $coin;
                                                                $dbArgs['coin_amount'] = $amount;
                                                                $dbArgs['coin_address'] = $recip;
                                                                $dbArgs['action'] = 'withdraw';
                                                                $dbArgs['secret'] = $otpArgs['secret'];
                                                                $dbArgs['data'] = $otpArgs['data'];
    
                                                                if($url) {
                                                                    if($a->ccOTP_otp_insert($dbArgs)) {
                                                                        $userinfo = $a->getUserInfo($apidetails['uid']);
                                                                        $mailArgs = array();
                                                                        $mailArgs['username'] = $userinfo['username'];
                                                                        $mailArgs['email'] = $userinfo['email'];
                                                                        $mailArgs['coin_amount'] = $amount;
                                                                        $mailArgs['coin_code'] = $coin;
                                                                        $mailArgs['action'] = 'withdraw';
                                                                        $mailArgs['link'] = 'https://'.$url;
                                                                        $mailArgs['address'] = $recip;
    
                                                                        include('ccEmail.php');
                                                                        $ccEmail = new ccEmail();
                                                                        $ccEmail->sendEmail($mailArgs);
                                                                    }
                                                                }
    
                                                                $tmpl['response']['status'] = 'success pending email approval';
                                                                $tmpl['response']['sent_total'] = $validBalance['sent_total'];
                                                                $tmpl['response']['subtotal'] = $validBalance['subtotal'];
                                                                $tmpl['response']['txfee'] = $validBalance['txfee'];
                                                                $tmpl['response']['ccfee'] = $validBalance['ccfee'];
                                                                $tmpl['response']['balance_remaining'] = $validBalance['balance_remaining'];
												        } else {
    														$tmpl['response']['status'] = 'failure';
	    													$tmpl['response']['message'] = 'Insufficient balance to perform request';
                                                            $tmpl['response']['balance'] = $balance;
                                                            $l->ccLog('send: Invalid balance. Insufficient balance to perform request');
			    									    }
                                                    } else {
                                                        $l->ccLog('send: account could not be validated');
                                                        $tmpl['response']['status'] = 'failure';
                                                        $tmpl['response']['message'] = 'Internal server error';
                                                    }
                                                } else {
                                                    $tmpl['response']['status'] = 'failure';
                                                    $tmpl['response']['message'] = 'Max '.$MAXCOUNT.' withdraw requests per hour. Please wait and try again later.';
                                                }
                                            } //end send new OTP email
                                        }
                                    }else{ /// end valid amount range
									  $l->ccLog('send: Argument range could not be validated.');
                                		$tmpl['response']['status'] = 'failure';
                                		$tmpl['response']['message'] = 'Argument range could not be validated';
								}
                            } else {
                                $l->ccLog('send: Arguments coin, address or amount could not be validated.');
                                $tmpl['response']['status'] = 'failure';
                                $tmpl['response']['message'] = 'Arguments coin, address or amount could not be validated.';
                            }
						}
                    } else {
                        $l->ccLog('send: send is disabled');
                    }
			echo json_encode($tmpl, JSON_UNESCAPED_SLASHES);
		}

}

?>
