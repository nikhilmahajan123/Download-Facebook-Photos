<?php

// Include FB config file && User class
require_once 'fbConfig.php';

if (isset($_SESSION['facebook_access_token'])) {
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    // Redirect the user back to the same page if url has "code" parameter in query string
    if (isset($_GET['code'])) {
          header('Location: index.php');
    }
    // Get logout url
    $logoutCallback = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/fb_album/logout.php';
    $logoutURL = $helper->getLogoutUrl($_SESSION['facebook_access_token'], $logoutCallback);
    // Getting user facebook albums and info
    try {
        $profileRequest = $fb->get('/me/albums?fields=id,name,description,link,cover_photo,count');
        $jsonData = $profileRequest->getGraphEdge();
        foreach ($jsonData as $node) {
            $fbUserAlbums[] = $node->asArray();
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
} else {
    // Get login url
    $loginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.ico">

    <title>Album example for Bootstrap</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/album.css" rel="stylesheet">
</head>
<style>
    #modalCarousel {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, .075);
    }

    /* Modal Content */

    #modalCarousel .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 0;

    }

    #modalCarousel .img-carousel {}
</style>

<body>
    <header>
        <div class="collapse bg-dark" id="navbarHeader">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 col-md-7 py-4">
                        <h4 class="text-white">About</h4>
                        <p class="text-muted">
                            Add some information about the album below, the author, or any other background context. Make it a few sentences long so folks can pick up some informative tidbits. Then, link them off to some social networking sites or contact information.
                        </p>
                    </div>
                    <?php if (isset($logoutURL)) { ?>
                    <div class="col-sm-4 offset-md-1 py-4">
                        <ul class="list-unstyled">
                            <li><a href="<?php echo isset($logoutURL)?$logoutURL:''; ?>" class="text-white">Logout</a></li>
                        </ul>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="navbar navbar-dark bg-dark box-shadow">
            <div class="container d-flex justify-content-between">
                <a href="#" class="navbar-brand d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
            <strong><?php echo isset($_SESSION['fbUserName'])?$_SESSION['fbUserName']:''; ?> Albums</strong>
          </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
            </div>
        </div>
    </header>
    <?php if (!empty($fbUserAlbums)) { ?>
    <main role="main">
        <section class="jumbotron text-center">
            <div class="container">
                <h1 class="jumbotron-heading">Albums</h1>
                <p class="lead text-muted">
                    Download : - To download specific album to your computer <br/> Download Selected: - To download specific albums to your computer <br/> Download All : - Download all albums in your computer. <br/> Move : - To move specific album to Google Drive <br/> Move Selected: - To move selected albums to Google Drive <br/> Move All : - To move all albums to Google Drive <br/>
                </p>
                <p>
                    <button type="button" class="btn btn-primary downloadAlbum" data-name="all">Download All</button>
                    <button type="button" class="btn btn-primary moveAlbum" data-name="all">Move All</button>
                </p>
                <p>
                    <button type="button" class="btn btn-primary downloadSelected downloadAlbum" data-name="multi">Download Selected</button>
                    <button type="button" class="btn btn-primary moveAlbum" data-name="multi">Move Selected</button>
                </p>
            </div>
        </section>

        <div class="album py-5 bg-light">
            <div class="container">

                <div class="row">
                    <?php
                    foreach ($fbUserAlbums as $data) {
                          $id = isset($data['id'])?$data['id']:'';
                          $name = isset($data['name'])?$data['name']:'';
                          $description = isset($data['description'])?$data['description']:'';
                          $link = isset($data['link'])?$data['link']:'';
                          $cover_photo_id = isset($data['cover_photo']['id'])?$data['cover_photo']['id']:'';
                          $count = isset($data['count'])?$data['count']:'';
                        if ($cover_photo_id) {
                        ?>

                        <div class="col-md-4">
                            <div class="card mb-4 box-shadow">
                                <img class="img-thumbnail" style="height:300px;" src="https://graph.facebook.com/v2.9/<?php echo $cover_photo_id; ?>/picture?access_token=<?php echo $_SESSION['facebook_access_token']; ?>" onclick="openModal(<?php echo $id; ?>);" class="hover-shadow">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="<?php echo $id.'_(&)_'.$name; ?>">
                                        <p class="card-text">Album Name :
                                            <?php echo $name;?>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="btn-group" id="<?php echo $id.'_(&)_'.$name; ?>">
                                            <button type="button" class="btn btn-sm btn-outline-secondary viewCarousel">View</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary downloadAlbum" data-name="single">Download</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary moveAlbum" data-name="single">Move</button>
                                        </div>
                                        <small class="text-muted"><?php echo $count;?> Photos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                    }
                    ?>
                </div>
                <!-- Modal -->
                <div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><i class="fa fa-clock-o"></i> Please Wait</h4>
                            </div>
                            <div class="modal-body center-block">
                                <p>Downloading</p>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success myprogress" role="progressbar" style="width:0%">0%</div>
                                </div>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
                <!-- /.modal -->

                <!-- The Modal -->
                <div class="modal fade" id="myModal1">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">Download Completed</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <!-- Modal body -->
                            <div class="modal-body">
                                Click on button to download your album.
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadZip" data-name="">Download Zip</button>
                            </div>

                            <!-- Modal footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- The Message Modal -->
                <div class="modal fade" id="myMessage">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title" id="myMessageTitle"></h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <!-- Modal body -->
                            <div class="modal-body" id="myMessageBody">

                            </div>

                            <!-- Modal footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <!-- The Modal -->
            <div class="container text-center">
                <div class="modal fade" id="modalCarousel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <div id="demoCarousel" class="carousel slide" data-ride="carousel">

                                <!-- The slideshow -->
                                <div class="carousel-inner">

                                </div>

                                <!-- Left and right controls -->
                                <a class="carousel-control-prev" href="#demoCarousel" data-slide="prev">
                                  <span class="carousel-control-prev-icon"></span>
                                </a>
                                <a class="carousel-control-next" href="#demoCarousel" data-slide="next">
                                  <span class="carousel-control-next-icon"></span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <footer class="text-muted">
        <div class="container">
            <p class="float-right">
                <a href="#">Back to top</a>
            </p>
            <p>Album example is &copy; Bootstrap, but please download and customize it for yourself!</p>
        </div>
    </footer>
    <?php } else { ?>
    <section class="jumbotron text-center">
        <div class="container">
            <div class="row">
                <a href="<?php echo $loginURL; ?>" style="margin: 0 auto;"><img src="images/facebook-login-img.png" /></a>
            </div>
        </div>
    </section>
    <?php } ?>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script>
        window.jQuery || document.write('<script src="js/vendor/jquery-slim.min.js"><\/script>')
    </script>
    <script src="js/vendor/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/vendor/holder.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.fileDownload/1.4.2/jquery.fileDownload.min.js"></script>
    <script>
      function message($type) {
          if ($type == 'Error') {
              $('#myMessageTitle, #myMessageBody').empty();
              $('#myMessageTitle').text('Error');
              $('#myMessageBody').text('Error! Something wrong please try after some time or logout then login');
              $('#myMessage').modal('show');
          } else if ($type == 'Success') {
              $('#myMessageTitle, #myMessageBody').empty();
              $('#myMessageTitle').text('Success');
              $('#myMessageBody').text('Success! Album photos move to your drive');
              $('#myMessage').modal('show');
          }
      }

      function closeModal() {
          document.getElementById('myModal').style.display = "none";
      }
      $(document).ready(function() {
          // Open the Modal
          $('.viewCarousel').click(function() {
              $data = $(this).closest("div").attr("id").split('_(&)_');
              $id = $data[0];
              $.ajax({
                  type: "post",
                  url: "photos.php",
                  data: 'album_id=' + $id,
                  success: function(data) {
                      if (data != 'Failed') {
                          $res = $.parseJSON(data);
                          $('.carousel-inner').empty();
                          $('.carousel-inner').append($res[0]);
                          $('#modalCarousel').modal('show');
                      } else if (data) {
                          message('Error');
                      }

                  }
              });

          });

          function createZip() {
              $.ajax({
                  type: "post",
                  url: "downloader.php",
                  data: {
                      action: 'create_zip'
                  },

                  success: function(data) {
                      if (data == 'login_fb') {
                          window.location.href = "index.php";
                      } else if (data == 'Failed') {
                          $('#pleaseWaitDialog').modal('hide');
                          $('.myprogress').css('width', '0%').text('0%');
                      } else if (data.indexOf(".zip") > 0) {
                          $('#downloadZip').attr('data-name', data);
                          $('#pleaseWaitDialog').modal('hide');
                          $('.myprogress').css('width', '0%').text('0%');
                          $('#myModal1').modal('show');
                      } else {
                          $('#pleaseWaitDialog').modal('hide');
                          $('.myprogress').css('width', '0%').text('0%');
                      }
                  },
                  error: function(jqXHR, textStatus, errorThrown) {
                      console.log(errorThrown);
                  }
              });
          }

          function callbackDownload($album_id, $album_name, i) {
              return function(data, textStatus, jqXHR) {
                  if (data == 'login_fb') {
                      window.location.href = "index.php";
                  } else if (data == 'Failed') {
                      $('#pleaseWaitDialog').modal('hide');
                      message('Error');
                      $('.myprogress').css('width', '0%').text('0%');
                  } else if (data) {

                      $photos = JSON.parse(data);

                      if (i == 0) {
                          $total_photos = 0;
                      }
                      $total_photos += $photos.length;
                      for (var j = 0; j < $photos.length; j++) {
                          $photo_id = $photos[j]['id'];
                          $photo_source = $photos[j]['source'];
                          $photo_name = $photos[j]['name'];
                          //$album_name = $photos[j]['album_name'];

                          $.ajax({
                              type: "post",
                              url: "downloader.php",
                              data: {
                                  album_id: $album_id,
                                  album_name: $album_name,
                                  photo_source: $photo_source,
                                  photo_id: $photo_id,
                                  photo_name: $photo_name,
                                  action: 'dwnld_album'
                              },
                              success: function(data) {
                                  if (data == 'login_fb') {
                                      window.location.href = "index.php";
                                  } else if (data = 'Success') {
                                      $current_progress = (($inner_count / $total_photos) * 100);
                                      $current_progress = Math.ceil($current_progress);
                                      $current_progress = $current_progress < 100 ? $current_progress : 100;
                                      $('.myprogress').css('width', $current_progress + '%').text($current_progress + '%');
                                      if ($inner_count == $total_photos) {
                                          $('.myprogress').css('width', '100%').text('100%(Creating Zip...)');
                                          createZip();
                                      }
                                      $inner_count++;
                                  } else {
                                      message('Error');
                                      $('#pleaseWaitDialog').modal('hide');
                                      $('.myprogress').css('width', '0%').text('0%');
                                  }

                              }
                          })

                      }
                  }
              }
          }

          //download album
          $('.downloadAlbum').click(function() {
              $album_id = [];
              $album_name = [];
              if ($(this).attr('data-name') == 'single') {
                  $data = $(this).closest("div").attr("id").split('_(&)_');
                  $album_id[0] = $data[0];
                  $album_name[0] = $data[1];
              } else if ($(this).attr('data-name') == 'multi') {
                  // any one is checked or not
                  if ($(":checkbox:checked").length > 0) {
                      $(':checkbox:checked').each(function(i) {
                          $data = $(this).attr('id').split('_(&)_');
                          $album_id[i] = $data[0];
                          $album_name[i] = $data[1];
                      });
                  } else {
                      alert('You need to select at least one album.');
                      e.preventPropagation();
                      return false;
                  }

              } else if ($(this).attr('data-name') == 'all') {
                  $(':checkbox').each(function(i) {
                      $data = $(this).attr('id').split('_(&)_');
                      $album_id[i] = $data[0];
                      $album_name[i] = $data[1];
                  });
              }
              if (($album_id.length > 0) && ($album_name.length > 0)) {
                  $('.myprogress').css('width', '0%').text('0%');
                  $('#pleaseWaitDialog').modal();
                  $current_progress = 0;
                  $inner_count = 1;
                  for (var i = 0; i < $album_id.length; i++) {
                      $.ajax({
                          type: "post",
                          url: "downloader.php",
                          data: {
                              album_id: $album_id[i],
                              action: 'get_photo'
                          },

                          success: callbackDownload($album_id[i], $album_name[i], i),
                          error: function(jqXHR, textStatus, errorThrown) {
                              console.log(errorThrown);
                          }
                      });
                  }
              } else {
                  message('Error');
              }

          });
          //download Zip
          $('#downloadZip').click(function() {
              $file_url = $(this).attr('data-name');
              $.fileDownload($file_url);
              $('#myModal1').modal('hide');
          });

          function callbackMove($album_name, i) {
              return function(data, textStatus, jqXHR) {
                  if (data == 'login_fb') {
                      window.location.href = "index.php";
                  } else if (data == 'Failed') {
                      $('#pleaseWaitDialog').modal('hide');
                      $('.myprogress').css('width', '0%').text('0%');
                      message('Error');
                  } else if (data) {

                      $photos = JSON.parse(data);

                      if (i == 0) {
                          $total_photos = 0;
                      }
                      $total_photos += $photos.length;
                      for (var j = 0; j < $photos.length; j++) {
                          $photo_id = $photos[j]['id'];
                          $photo_source = $photos[j]['source'];
                          $photo_name = $photos[j]['name'];
                          //$album_name = $photos[j]['album_name'];

                          $.ajax({
                              type: "post",
                              url: "google_uploader.php",
                              data: {
                                  album_name: $album_name,
                                  photo_source: $photo_source,
                                  photo_id: $photo_id,
                                  photo_name: $photo_name,
                                  action: 'move_album'
                              },
                              success: function(data) {
                                  if (data = 'Success') {
                                      $current_progress = (($inner_count / $total_photos) * 100);
                                      $current_progress = Math.ceil($current_progress);
                                      $current_progress = $current_progress < 100 ? $current_progress : 100;
                                      $('.myprogress').css('width', $current_progress + '%').text($current_progress + '%');
                                      if ($inner_count == $total_photos) {
                                          $('#pleaseWaitDialog').modal('hide');
                                          message('Success');
                                      }
                                      $inner_count++;
                                  } else {
                                      message('Error');
                                      $('#pleaseWaitDialog').modal('hide');
                                      $('.myprogress').css('width', '0%').text('0%');
                                  }

                              }
                          })

                      }
                  }
              }
          }

          //move album to GoogleDrive
          $('.moveAlbum').click(function(e) {
              $check_login = "<?php echo $_SESSION['access_google_token']; ?>";

              if ($check_login) {
                  $album_id = [];
                  $album_name = [];
                  if ($(this).attr('data-name') == 'single') {
                      $data = $(this).closest("div").attr("id").split('_(&)_');
                      $album_id[0] = $data[0];
                      $album_name[0] = $data[1];
                  } else if ($(this).attr('data-name') == 'multi') {
                      // any one is checked or not
                      if ($(":checkbox:checked").length > 0) {
                          $(':checkbox:checked').each(function(i) {
                              $data = $(this).attr('id').split('_(&)_');
                              $album_id[i] = $data[0];
                              $album_name[i] = $data[1];
                          });
                      } else {
                          alert('You need to select at least one album.');
                          e.preventPropagation();
                          return false;
                      }

                  } else if ($(this).attr('data-name') == 'all') {
                      $(':checkbox').each(function(i) {
                          $data = $(this).attr('id').split('_(&)_');
                          $album_id[i] = $data[0];
                          $album_name[i] = $data[1];
                      });
                  }
                  if (($album_id.length > 0) && ($album_name.length > 0)) {
                      $('.myprogress').css('width', '0%').text('0%');
                      $('#pleaseWaitDialog').modal();
                      $current_progress = 0;
                      $inner_count = 1;
                      for (var i = 0; i < $album_id.length; i++) {
                          $.ajax({
                              type: "post",
                              url: "downloader.php",
                              data: {
                                  album_id: $album_id[i],
                                  action: 'get_photo'
                              },

                              success: callbackMove($album_name[i], i),
                              error: function(jqXHR, textStatus, errorThrown) {
                                  console.log(errorThrown);
                              }
                          });
                      }
                  } else {
                      message('Error');
                  }

              } else {
                  if (confirm("You need to first login to google") == true) {
                      window.location.href = "google_login.php";
                  }

              }




          });
      });

    </script>
</body>

</html>
