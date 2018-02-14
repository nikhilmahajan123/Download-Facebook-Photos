<?php
require_once 'gconfig.php';

if (!isset($_SESSION['access_google_token'])) {
  echo 'login_to_google';
  exit();
}

require_once 'fbConfig.php';
if(isset($accessToken)){
	if(isset($_SESSION['facebook_access_token'])){
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}else{
		// Put short-lived access token in session
		$_SESSION['facebook_access_token'] = (string) $accessToken;

	  	// OAuth 2.0 client handler helps to manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

		// Set default access token to be used in script
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
}
$action = $_POST['action'];
if($action == 'move_album'){
  $albumName = $_POST['album_name'];
  $photoId = $_POST['photo_id'];
  $photoSource = $_POST['photo_source'];
  $photoName = isset($_POST['photo_name'])?$_POST['photo_name']:'fb_'.$photoId;

  if(!$albumName){
    $albumName = 'No_Name';
  }

  $driveFolderName = 'facebook_'.$_SESSION['fbUserName'].'_albums';

  $drive = new GoogleDrive();
  $file = $drive->upload($albumName, $photoName, $photoSource, $driveFolderName);


}
/**
*   Google Drive Class
*/
class GoogleDrive
{
  public function upload($albumName, $photoName, $photoSource, $driveFolderName){

    //check parent folder present or not
    $parentFolderId = $this->check_folder($driveFolderName,'parent');

    if($parentFolderId == false){
      $parentFolderId = $this->create_folder($driveFolderName,'');//if parent folder is not present
    }
    //check album folder present or not
    $childFolderId = $this->check_folder($albumName,'child');
    if($childFolderId == false){
    //create child folder and insert all photos
    $childFolderId = $this->create_folder($albumName,$parentFolderId);//create child folder
  }

      $gClient = getClient();
      $gClient->setAccessToken($_SESSION['access_google_token']);
      $driveService = new Google_Service_Drive($gClient);
      $folderId = $childFolderId;
      $fbPhotoCount = 0;


        //replace slash,dot,space with underscore from name
        $photoName = str_replace(str_split('/:*?"<>|. '), '_', $photoName);
        // Set filename as <photo_name>.jpg
        $photoFile = $photoName.'.jpg';

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $photoFile,
            'parents' => array($folderId)
        ));
        $content = file_get_contents($photoSource);
        $file = $driveService->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => 'image/jpg',
            'uploadType' => 'multipart',
            'fields' => 'id'));

      if(isset($file->id)){
        echo "Success";
      }else{
        echo "Failed";
      }



  }
  public function check_folder($folderName,$type){
    if($type == 'parent'){
      $queryData = "mimeType='application/vnd.google-apps.folder' and name='".$folderName."' and 'root' in parents and trashed=false ";
    }else if($type == 'child'){
      $queryData = "mimeType='application/vnd.google-apps.folder' and name='".$folderName."' and trashed=false ";
    }
    $gClient = getClient();
    $gClient->setAccessToken($_SESSION['access_google_token']);
     $driveService = new Google_Service_Drive($gClient);
    $response = $driveService->files->listFiles(array(
        'q' => $queryData,
        'spaces' => 'drive',
        'fields' => ' files(id, name, parents)',
    ));
    if($response->files){
      return $response->files[0]->id;
    }else{
      return false;
    }

  }
  public function create_folder($folderName,$parentFolderId){

    $gClient = getClient();
    $gClient->setAccessToken($_SESSION['access_google_token']);
     $driveService = new Google_Service_Drive($gClient);
     if($parentFolderId){
       $fileMetadata = new Google_Service_Drive_DriveFile(array(
       'name' => $folderName,
       'parents' => array($parentFolderId),
       'mimeType' => 'application/vnd.google-apps.folder'));
     }else{
       $fileMetadata = new Google_Service_Drive_DriveFile(array(
       'name' => $folderName,
       'mimeType' => 'application/vnd.google-apps.folder'));
     }


    $file = $driveService->files->create($fileMetadata, array('fields' => 'id'));

    $folderId = $file->id;
    if($folderId){
      return $folderId;
    }else {
      return false;
    }

  }



}
?>
