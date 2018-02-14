<?php
session_start();

//Include Google client library
include_once 'google/vendor/autoload.php';

/*
 * Configuration and setup Google API
 */
 function getClient(){
   $clientId = '<google client id here>';
   $clientSecret = '<google secret key here>';
   $redirectURL = '<google redirect key here>';


   //Call Google API
   $gClient = new Google_Client();
   $gClient->setApplicationName('Login to Upload Facebook Albums on Drive');
   $gClient->setClientId($clientId);
   $gClient->setClientSecret($clientSecret);
   $gClient->setRedirectUri($redirectURL);
   $gClient->addScope("https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/drive");
   return $gClient;
 }




?>
