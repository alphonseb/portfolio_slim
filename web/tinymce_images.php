<?php
/***************************************************
 * Only these origins are allowed to upload images *
 ***************************************************/
$url = 'http://localhost/slim_folio/web/';
 
$accepted_origins = array("http://localhost", "http://192.168.1.1", "http://example.com");

reset($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])) {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        } else {
            header("HTTP/1.1 403 Origin Denied");
            return;
        }
    }

    /*
    If your script needs to receive cookies, set images_upload_credentials : true in
    the configuration and enable the following two headers.
     */
    // header('Access-Control-Allow-Credentials: true');
    // header('P3P: CP="There is no P3P policy."');

    // Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.1 400 Invalid file name.");
        return;
    }

    // Verify extension

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($temp['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        header("HTTP/1.1 400 Invalid extension.");
        return;
    }

    $filetowrite = sprintf('uploads/%s.%s',
        $name = sha1_file($temp['tmp_name']),
        $ext
    );

    if (!move_uploaded_file(
        $temp['tmp_name'],
        $filetowrite
    )) {
        header("HTTP/1.1 500 Couldn't upload file.");
        return;

    }

    // Accept upload if there was no origin, or if it is an accepted origin

    // move_uploaded_file($temp['tmp_name'], $filetowrite);

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    echo json_encode(array('location' => $url.$filetowrite));
} else {
    // Notify editor that the upload failed
    header("HTTP/1.1 500 Server Error");
}
