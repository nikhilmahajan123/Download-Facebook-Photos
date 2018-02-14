<?php
require_once 'fbConfig.php';

if (isset($_SESSION['facebook_access_token'])) {
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
} else {
    echo "login_fb";
    exit();
}

$action = $_POST['action'];
if ($action == 'get_photo') {
    $albumId = $_POST['album_id'];
    //$albumName = $_POST['album_name'];
    // Get Album Data
    try {
        $photoRequest = $fb->get('/'.$albumId.'/photos?fields=images.width(1000).height(500),source,name');
        $photoJsonData = $photoRequest->getGraphEdge();
        //$a=0;
        foreach ($photoJsonData as $node) {
            $fbUserPhotos[] = $node->asArray();
        // $fbUserPhotos[$a][album_name] = $albumName;
        // $a++;
        }
    } catch (FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
        // Redirect user back to app login page
        header("Location: ./");
        exit;
    } catch (FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    if (!$fbUserPhotos) {
        echo 'Failed';
    } else {
        echo json_encode($fbUserPhotos);
    }
}
if ($action == 'dwnld_album') {
    $albumName = str_replace(str_split('/:*?"<>|. '), '_', $_POST['album_name']);
    $albumId = $_POST['album_id'];
    $photoSource = $_POST['photo_source'];
    $photoId = $_POST['photo_id'];
    $photoName = str_replace(str_split('/:*?"<>|. '), '_', $_POST['photo_name']);

    if (!$albumName) {
        $albumName = $albumId;
    }
    if (!$photoName) {
        $photoName = 'fb'.$photoId;
    }

    // Initilized temporary directory tmp directory in project root
    $tmp_dir = __DIR__.'/facebook_'.$_SESSION['fbUserName'].'_albums/';

    // If tmp directory is not created then create it.
    if (!is_dir($tmp_dir)) {
        mkdir($tmp_dir, 0777);
    }

    // So multiple album with same name will not collide
    // Create directory with album name
    $path = $tmp_dir.$albumName.'/';
    if (!is_dir($path)) {
        mkdir($path, 0777);
    }
      $file = $photoName.'.jpg';

      // Copy to the server
    if (!copy($photoSource, $path.$file)) {
        echo "Filed";
    } else {
        echo "Success";
    }
}
  // Function to create zip file
function zipData($source, $destination)
{

    if (file_exists($source)) {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
            $source = realpath($source);
            if (is_dir($source)) {
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) ;
                foreach ($files as $file) {
                    $file = realpath($file);
                    if (is_dir($file)) {
                        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    } elseif (is_file($file)) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    }
                }
            } elseif (is_file($source)) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }
        return $zip->close();
    }

    return false;
}
  //function to delete folder and files
function deleteDir($dirPath)
{
    if (! is_dir($dirPath)) {
          return false;
    } else {
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
        return true;
    }
}
if ($action == 'create_zip') {
    $source = __DIR__.'/facebook_'.$_SESSION['fbUserName'].'_albums/';
    $destination = __DIR__.'/facebook_'.$_SESSION['fbUserName'].'_albums.zip';
    if (file_exists($destination)) {
        unlink($destination);
    }
    $result = zipData($source, $destination);

    if ($result) {
        deleteDir($source);
        $url = $_SERVER['REQUEST_URI']; //returns the current URL
        $parts = explode('/', $url);
        echo (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/'.$parts[1].'/facebook_'.$_SESSION['fbUserName'].'_albums.zip';
    } else {
        echo "Failed";
    }
}
  //download selected
if ($action == 'dwnld_selected') {
    $albumsData = $_POST['albums_data'];
    $temp_fname = 'album'.time();
    if (gettype($albumsData)!="array") {
        $albumsData = array($albumsData); // If single album id. Make single Array
    }
    // Initilized temporary directory tmp directory in project root
    $tmp_dir = __DIR__.'/';

    // If tmp directory is not created then create it.
    if (!is_dir($tmp_dir)) {
        mkdir($tmp_dir, 0777);
    }
    // Created temporary subdirectory as profile id
    // So multiple album with same name will not collide
    if (!is_dir($tmp_dir.$temp_fname)) {
        mkdir($tmp_dir.$temp_fname, 0777);
    }

    // Created Zip with the filename <user_profile_id>.zip
    $zip = new ZipArchive();
    $zipFile = $tmp_dir.$temp_fname.'.zip';
    if ($zip->open($zipFile, ZipArchive::CREATE)!=="TRUE") {
        exit("cannot open <$zipFile>\n");
    }

    $tmp_dir .= $temp_fname.'/';

    $albumCount = 0;
    // Loop to start download each albums of array
    foreach ($albumsData as $albumData) {
        $tempArr = array();
        $fbUserPhotos = array();
        $tempArr = explode('_(&)_', $albumData);//seprate album id and album name
        $albumId = $tempArr[0];
        $albumName = $tempArr[1];
        if (!$albumName) {
            $albumName = $albumCount;
        }
        // Get Album Data
        try {
            $photoRequest = $fb->get('/'.$albumId.'/photos?fields=images.width(1000).height(500),source,name');
            $photoJsonData = $photoRequest->getGraphEdge();
            foreach ($photoJsonData as $node) {
                $fbUserPhotos[] = $node->asArray();
            }
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            session_destroy();
            // Redirect user back to app login page
            header("Location: ./");
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        // Create directory with album name
        $path = $tmp_dir.$albumName.'-'.$albumId.'/';
        mkdir($path, 0777);

        // Fore Each photo of album
        $fbPhotoCount = 0;
        foreach ($fbUserPhotos as $data) {
          // Initilized blank photo name if there is no caption for the photo
            $photoName = "";
            $imageData = end($data['images']);
            $imgSource = isset($imageData['source'])?$imageData['source']:'';
            $photoName = isset($data['name'])?$data['name']:'fb_'.$fbPhotoCount;

          //replace slash,dot,space with underscore from name
            $photoName = str_replace(str_split('/:*?"<>|. '), '_', $photoName);
          // Set filename as <photo_caption>-<photo_id>.jpg
            $file = $photoName.'.jpg';

          // Copy to the server
            if (!copy($imgSource, $path.$file)) {
                echo $file."file not copied";
            }
            $fbPhotoCount++;
        }

        // Set parameters for zip i.e. to save each album with their saperate folder
        $options = array('add_path' => $albumName.'/', 'remove_all_path' => "TRUE");
        $zip->addGlob($path.'*.jpg', GLOB_BRACE, $options);
        unset($fbUserPhotos); // unset array
        unset($tempArr); // unset array
    }

    // Close Zip after all the album is archived
    $zip->close();

    // Delete whole temprory directory with downloaded photos
    //$this->removeRecursive($tmp_dir);
    if (file_exists($zipFile)) {
        echo $temp_fname.'.zip';
    } else {
        echo 'fail';
    }
}


if ($action == 'dwmld_zip') {
    $fileName = $_POST['file_name'];
    // Set headers to send a file to the client
    header('Content-type:  application/zip');   // Set file type as zip
    header('Content-Length: ' . filesize($fileName));   // Set file size
    header('Content-Disposition: attachment; filename="'.$fileName.'"');    // Set filename for the client

    // Transfer the filedata as body
    readfile($fileName);

    // Delete file after whole file is transfered
    //unlink($fileName);
}


function removeRecursive($dir)
{
    // Remove . and .. firectories from the directory list
    $files = array_diff(scandir($dir), array('.','..'));

    // Delete all files one by one
    foreach ($files as $file) {
        // If current file is directory then recurse it
        (is_dir("$dir/$file")) ? $this->removeRecursive("$dir/$file") : unlink("$dir/$file");
    }

    // Remove blank directory after deleting all files
    return rmdir($dir);
}
