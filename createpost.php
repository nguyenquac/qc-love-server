<?php

header('Content-Type: text/plain; charset=utf-8');

try {
    
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['upfile']['error']) ||
        is_array($_FILES['upfile']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here. 
    if ($_FILES['upfile']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.

    if (false === $ext = array_search(pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION),
        array(
            'jpg' => 'jpeg',
            'jpg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    $file_path = sprintf('./media/%s.%s', sha1_file($_FILES['upfile']['tmp_name']), $ext);
    if (!move_uploaded_file(
        $_FILES['upfile']['tmp_name'],
        $file_path
        )
    ) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    $_POST['media_path'] = str_replace('./', '/', $file_path);

} catch (RuntimeException $e) {
    $obj = (object) [
        "result" => 0,
        "error" => $e->getMessage()
    ];
    echo json_encode($obj);
    return;
}

$url = 'http://localhost:8080/api.php/post';
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

?>