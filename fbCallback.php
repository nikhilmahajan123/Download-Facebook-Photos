<?php
require_once 'fbConfig.php';

// Try to get access token
try {
    $accessToken = $helper->getAccessToken();
} catch (FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (!$accessToken) {
    header('Location:index.php');
}

// OAuth 2.0 client handler helps to manage access tokens
$oAuth2Client = $fb->getOAuth2Client();
if (!$accessToken->isLongLived()) {
    // Exchanges a short-lived access token for a long-lived one
    $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
    $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
} else {
    $_SESSION['facebook_access_token'] = (string) $accessToken;
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
}

// Getting user facebook detail
try {
  // Returns a `Facebook\FacebookResponse` object
    $response = $fb->get('/me?fields=id,name');
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
$user = $response->getGraphUser();
$_SESSION['fbUserName'] = $user['name'];
$_SESSION['fbUserId'] = $user['id'];
header('Location:index.php');
