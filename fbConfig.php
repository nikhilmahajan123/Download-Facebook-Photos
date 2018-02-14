<?php
if (!session_id()) {
    session_start();
}

// Include the autoloader provided in the SDK
require_once __DIR__ . '/facebook-php-sdk/autoload.php';

// Include required libraries
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

/*
 * Configuration and setup Facebook SDK
 */
$appId        = '<Facebook App ID here>'; //Facebook App ID
$appSecret    = '<Facebook App Secret here>'; //Facebook App Secret
$redirectURL  = '<Callback URL here>'; //Callback URL
$fbPermissions= ['user_photos'];  //Optional permissions

$fb = new Facebook(array(
      'app_id'     => $appId,
      'app_secret' => $appSecret,
      'default_graph_version' => 'v2.2',
));
// Get redirect login helper
$helper = $fb->getRedirectLoginHelper();
