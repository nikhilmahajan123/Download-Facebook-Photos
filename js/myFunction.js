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
