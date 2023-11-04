<?php

include '../config/config.php';

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addCategory')) {

    if (($_FILES['cover_image'] != '') && ($_POST['title'] != '')) {

        $check = $conn->prepare("SELECT * FROM `gallery_category` WHERE `title` = ?");
        $check->execute([$_POST['title']]);
        $check = $check->fetch(PDO::FETCH_ASSOC);
        if(!$check){
            $image = basename($_FILES['cover_image']['name']);
            $uploadPath = '../../upload/gallery/' . $image;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath);
    
            $check = $conn->prepare('INSERT INTO `gallery_category`( `title`, `cover`) VALUES (? , ?)');
            $result = $check->execute([$_POST['title'], $image]);
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'successfully Added', "status" => 200));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
        }else{
            http_response_code(400);
            echo json_encode(array("message" => 'This Title is already Exist.', "status" => 500));
        }


    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'updateCategory')) {

    if (($_POST['id'] != '') && ($_POST['title'] != '')) {

        $check = $conn->prepare("SELECT * FROM `gallery_category` WHERE `id` = ?");
        $check->execute([$_POST['id']]);
        $check = $check->fetch(PDO::FETCH_ASSOC);
        if($check){
            if($_FILES['cover_image']['name'] != ''){
                $image = basename($_FILES['cover_image']['name']);
                $uploadPath = '../../upload/gallery/' . $image;
                move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath);
                $check = $conn->prepare('UPDATE `gallery_category` SET `cover` = ? WHERE `id` = ?');
                $result = $check->execute([$image , $_POST['id']]);
            }
    
            $check = $conn->prepare('UPDATE `gallery_category` SET `title` = ? WHERE `id` = ?');
            $result = $check->execute([$_POST['title'], $_POST['id']]);
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'successfully Updated', "status" => 200));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
        }else{
            http_response_code(400);
            echo json_encode(array("message" => 'This Category is not found.', "status" => 500));
        }


    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}


if(($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'deleteCategory')){
    if($_POST['id']){
        $gallery = $conn->prepare("DELETE FROM `gallery_category` WHERE `id` = ?");
        $result = $gallery->execute([$_POST['id']]);
        if($result){
            http_response_code(200);
            echo json_encode(array("message" => "Category is removed.", "status" => 200));
        }else{
            http_response_code(500);
            echo json_encode(array("message" => "Something Went Wrong ", "status" => 500));
        }
    }else{
        http_response_code(500);
        echo json_encode(array("message" => "Id is required ", "status" => 500));
    }
}

if(($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'deleteImage')){
    if($_POST['id']){
        $gallery = $conn->prepare("DELETE FROM `gallery` WHERE `id` = ?");
        $result = $gallery->execute([$_POST['id']]);
        if($result){
            http_response_code(200);
            echo json_encode(array("message" => "Image is removed.", "status" => 200));
        }else{
            http_response_code(500);
            echo json_encode(array("message" => "Something Went Wrong ", "status" => 500));
        }
    }else{
        http_response_code(500);
        echo json_encode(array("message" => "Id is required ", "status" => 500));
    }
}

if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getCategory')){
    if($_GET['id']){
        $gallery = $conn->prepare("SELECT *  FROM `gallery_category` WHERE `id` = ?");
        $gallery->execute([$_GET['id']]);
        $result = $gallery->fetch(PDO::FETCH_ASSOC);
        if($result){
            http_response_code(200);
            echo json_encode($result);
        }else{
            http_response_code(500);
            echo json_encode(array("message" => "Something Went Wrong ", "status" => 500));
        }
    }else{
        http_response_code(500);
        echo json_encode(array("message" => "Id is required ", "status" => 500));
    }
}

?>