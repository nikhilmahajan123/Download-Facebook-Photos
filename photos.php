<?php
require_once 'fbConfig.php';
if (isset($_SESSION['facebook_access_token'])) {
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
} else {
    echo "login_fb";
    exit();
}
  $album_id = $_POST['album_id'];
  // Getting user facebook photos from each albums
  try {
    $photoRequest = $fb->get('/'.$album_id.'/photos?fields=images.width(500).height(500),source,name');
    $photoJsonData = $photoRequest->getGraphEdge();
    foreach ($photoJsonData as $node) {
       $fbUserPhotos[] = $node->asArray();
     }

  } catch(FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    session_destroy();
    // Redirect user back to app login page
    header("Location: ./");
    exit;
  } catch(FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }
  if($fbUserPhotos){
    // Render all photos
    $count = 1;

    foreach($fbUserPhotos as $data){
        $imageData = $data['images'][1];
        $imgSource = isset($imageData['source'])?$imageData['source']:'';
        $name = isset($data['name'])?$data['name']:'';

        if($count == 1){
          $class_mode = "active";
        }else {
          $class_mode = "";
        }

        $photo_slide_output .= "<div class='carousel-item {$class_mode}'>";
        $photo_slide_output .= "<img class='img-thumbnail img-carousel' src='{$imgSource}' alt='' >";
        $photo_slide_output .= "</div>";
        $count++;
    }
    $response =array($photo_slide_output);

    echo json_encode($response);
  }else{
    echo "Failed";
  }

?>
