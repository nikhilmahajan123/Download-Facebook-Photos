<?php
require_once __DIR__ . '/gconfig.php';


if (isset($_SESSION['access_google_token'])) {

}else if(isset($_GET['code'])){
    $gClient = getClient();
    $gClient->authenticate($_GET['code']);
    $_SESSION['access_google_token'] = $gClient->getAccessToken();

}else{
  $gClient = getClient();
  $authUrl = $gClient->createAuthUrl();
  header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
  exit();
}

//get user Data
//$oAuth = new Google_Service_Oauth2($gClient);
//$gUserData      = $oAuth->userinfo_v2_me->get();


header('Location: index.php');
exit();



?>
